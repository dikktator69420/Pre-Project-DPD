<?php
// AddressValidator.php
class AddressValidator {
    private $pdo;
    
    public function __construct() {
        $dbConfig = require '../config/database.php';
        
        try {
            // Enhanced connection to properly handle UTF-8
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']} COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function validate($input) {
        // Apply UTF-8 encoding to all input strings
        $input = $this->encodeAllStrings($input);
        
        // Input validation
        if (!$this->validateInput($input)) {
            return [
                'status' => 0,
                'message' => 'Missing required fields'
            ];
        }

        // Validate country is Austria
        if (!$this->isAustrianCountryCode($input['Land'])) {
            return [
                'status' => 0,
                'message' => 'Only Austrian addresses are supported'
            ];
        }

        // Normalize address components
        $normalizedAddress = $this->normalizeAddress($input);

        // Check if address exists in database
        $stmt = $this->pdo->prepare("
            SELECT * FROM addresses 
            WHERE plz = :plz 
            AND UPPER(stadt) = UPPER(:ort)
            AND (
                UPPER(strasse) = UPPER(:strasse)
                OR UPPER(CONCAT(strasse, ' ', haus_nummer)) = UPPER(:full_street)
            )
        ");

        $stmt->execute([
            'plz' => $normalizedAddress['PLZ'],
            'ort' => $normalizedAddress['Ort'],
            'strasse' => $normalizedAddress['Strasse'],
            'full_street' => $normalizedAddress['Strasse'] . ' ' . $normalizedAddress['Hausnummer']
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'status' => 1,
                'corrected_address' => [
                    'Strasse' => $this->ensureUtf8($result['strasse']),
                    'Hausnummer' => $this->ensureUtf8($result['haus_nummer']),
                    'PLZ' => $result['plz'],
                    'Ort' => $this->ensureUtf8($result['stadt'])
                ]
            ];
        }

        // If no exact match, try to find closest match
        return $this->findClosestMatch($normalizedAddress);
    }
    
    /**
     * Recommend streets based on a partial street name (minimum 3 characters)
     */
    public function recommendStrasse($query) {
        // Ensure UTF-8 encoding for the query
        $query = $this->ensureUtf8($query);
        
        // Input validation
        if (empty($query) || strlen($query) < 3) {
            return [
                'status' => 0,
                'message' => 'Query must be at least 3 characters long'
            ];
        }
        
        // Get streets that start with the query
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT strasse as Strasse
            FROM addresses 
            WHERE UPPER(strasse) LIKE UPPER(:query_start)
            ORDER BY strasse ASC
            LIMIT 20
        ");
        
        $stmt->bindValue(':query_start', $query . '%');
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Ensure proper UTF-8 encoding of results
        $encodedResults = array_map(function($item) {
            return $this->ensureUtf8($item);
        }, $results);
        
        if (count($encodedResults) > 0) {
            return [
                'status' => 1,
                'recommendations' => $encodedResults
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'No matching streets found'
            ];
        }
    }
    
    /**
     * Recommend cities (Ort) based on a partial city name
     */
    public function recommendStadt($query) {
        // Ensure UTF-8 encoding for the query
        $query = $this->ensureUtf8($query);
        
        // Input validation
        if (empty($query) || strlen($query) < 3) {
            return [
                'status' => 0,
                'message' => 'Query must be at least 3 characters long'
            ];
        }
        
        // Get cities that start with the query
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT stadt as Ort
            FROM addresses 
            WHERE UPPER(stadt) LIKE UPPER(:query_start)
            ORDER BY stadt ASC
            LIMIT 20
        ");
        
        $stmt->bindValue(':query_start', $query . '%');
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Ensure proper UTF-8 encoding of results
        $encodedResults = array_map(function($item) {
            return $this->ensureUtf8($item);
        }, $results);
        
        if (count($encodedResults) > 0) {
            return [
                'status' => 1,
                'recommendations' => $encodedResults
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'No matching cities found'
            ];
        }
    }
    
    private function validateInput($input) {
        $requiredFields = ['PLZ', 'Ort', 'Strasse', 'Land'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return false;
            }
        }
        return true;
    }

    private function normalizeAddress($address) {
        // Extract house number if included in street
        if (empty($address['Hausnummer']) && preg_match('/^(.*?)\s+(\d+.*)$/', $address['Strasse'], $matches)) {
            $address['Strasse'] = trim($matches[1]);
            $address['Hausnummer'] = trim($matches[2]);
        }

        // Remove common abbreviations and normalize spacing
        $address['Strasse'] = $this->normalizeStreetName($address['Strasse']);
        
        // Normalize postal code format
        $address['PLZ'] = preg_replace('/[^0-9]/', '', $address['PLZ']);

        return $address;
    }

    private function normalizeStreetName($street) {
        $replacements = [
            '/str\./i' => 'strasse',
            '/g\./i' => 'gasse',
            '/pl\./i' => 'platz',
            '/\s+/' => ' ',
            '/\.$/' => ''
        ];

        return trim(preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $street
        ));
    }

    private function isAustrianCountryCode($country) {
        $austrianCodes = ['AT', 'AUT', 'OE', 'AUSTRIA', 'ÖSTERREICH', 'OSTERREICH'];
        return in_array(strtoupper($country), $austrianCodes);
    }

    private function findClosestMatch($address) {
        // Try to find by postal code and city
        $stmt = $this->pdo->prepare("
            SELECT * FROM addresses 
            WHERE plz = :plz 
            AND UPPER(stadt) = UPPER(:ort)
            LIMIT 1
        ");

        $stmt->execute([
            'plz' => $address['PLZ'],
            'ort' => $address['Ort']
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'status' => 1,
                'message' => 'Partial match found',
                'corrected_address' => [
                    'Strasse' => $this->ensureUtf8($result['strasse']),
                    'Hausnummer' => $this->ensureUtf8($result['haus_nummer']),
                    'PLZ' => $result['plz'],
                    'Ort' => $this->ensureUtf8($result['stadt'])
                ]
            ];
        }

        // Try to find by street name and city
        $stmt = $this->pdo->prepare("
            SELECT * FROM addresses 
            WHERE UPPER(stadt) = UPPER(:ort)
            AND UPPER(strasse) LIKE CONCAT('%', UPPER(:strasse), '%')
            LIMIT 1
        ");

        $stmt->execute([
            'ort' => $address['Ort'],
            'strasse' => $address['Strasse']
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'status' => 1,
                'message' => 'Partial match found',
                'corrected_address' => [
                    'Strasse' => $this->ensureUtf8($result['strasse']),
                    'Hausnummer' => $this->ensureUtf8($result['haus_nummer']),
                    'PLZ' => $result['plz'],
                    'Ort' => $this->ensureUtf8($result['stadt'])
                ]
            ];
        }

        return [
            'status' => 0,
            'message' => 'Address not found'
        ];
    }
    
    /**
     * Helper method to ensure UTF-8 encoding of strings
     */
    private function ensureUtf8($str) {
        if (!is_string($str)) {
            return $str;
        }
        
        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($str, 'UTF-8, ISO-8859-1, ISO-8859-15, Windows-1252', true);
            if ($encoding && $encoding !== 'UTF-8') {
                return mb_convert_encoding($str, 'UTF-8', $encoding);
            }
        }
        
        // If mb_detect_encoding isn't available or couldn't detect the encoding
        if (!mb_check_encoding($str, 'UTF-8')) {
            // Try to convert from ISO-8859-1 (Latin1) as a fallback
            return utf8_encode($str);
        }
        
        return $str;
    }
    
    /**
     * Apply UTF-8 encoding to all string values in an array
     */
    private function encodeAllStrings($data) {
        if (!is_array($data)) {
            return $this->ensureUtf8($data);
        }
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->ensureUtf8($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->encodeAllStrings($value);
            }
        }
        
        return $data;
    }
}