<?php
// AddressValidator.php
class AddressValidator {
    private $pdo;
    
    public function __construct() {
        $dbConfig = require_once '../config/database.php';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['user'],
                $dbConfig['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function validate($input) {
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
                UPPER(straße) = UPPER(:strasse)
                OR UPPER(CONCAT(straße, ' ', haus_nummer)) = UPPER(:full_street)
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
                    'Strasse' => $result['strasse'],
                    'Hausnummer' => $result['hausnummer'],
                    'PLZ' => $result['plz'],
                    'Ort' => $result['ort']
                ]
            ];
        }

        // If no exact match, try to find closest match
        return $this->findClosestMatch($normalizedAddress);
    }
    
    /**
     * Recommend streets based on a partial street name (minimum 4 characters)
     * 
     * @param string $query Partial street name
     * @return array Results with status and recommendations
     */
    public function recommendStrasse($query) {
        // Input validation
        if (empty($query) || strlen($query) < 4) {
            return [
                'status' => 0,
                'message' => 'Query must be at least 4 characters long'
            ];
        }
        
        // Get streets that start with the query
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT straße as Strasse
            FROM addresses 
            WHERE UPPER(straße) LIKE UPPER(:query_start)
            ORDER BY straße ASC
        ");
        
        $stmt->bindValue(':query_start', $query . '%');
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($results) > 0) {
            return [
                'status' => 1,
                'recommendations' => $results
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'No matching streets found'
            ];
        }
    }
    
    /**
     * Recommend cities (Ort) based on a partial city name (minimum 4 characters)
     * 
     * @param string $query Partial city name
     * @return array Results with status and recommendations
     */
    public function recommendStadt($query) {
        // Input validation
        if (empty($query) || strlen($query) < 4) {
            return [
                'status' => 0,
                'message' => 'Query must be at least 4 characters long'
            ];
        }
        
        // Get cities that start with the query
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT stadt as Ort
            FROM addresses 
            WHERE UPPER(stadt) LIKE UPPER(:query_start)
            ORDER BY stadt ASC
        ");
        
        $stmt->bindValue(':query_start', $query . '%');
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($results) > 0) {
            return [
                'status' => 1,
                'recommendations' => $results
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
        $austrianCodes = ['AT', 'AUT', 'OE', 'AUSTRIA', 'ÖSTERREICH'];
        return in_array(strtoupper($country), $austrianCodes);
    }

    private function findClosestMatch($address) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM addresses 
            WHERE plz = :plz 
            AND UPPER(stadt) = UPPER(:ort)
            AND UPPER(straße) LIKE CONCAT(UPPER(:street_start), '%')
            LIMIT 1
        ");

        $stmt->execute([
            'plz' => $address['PLZ'],
            'ort' => $address['Ort'],
            'street_start' => substr($address['Strasse'], 0, 5)
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'status' => 1,
                'corrected_address' => [
                    'Strasse' => $result['strasse'],
                    'Hausnummer' => $result['hausnummer'],
                    'PLZ' => $result['plz'],
                    'Ort' => $result['ort']
                ]
            ];
        }

        return [
            'status' => 0,
            'message' => 'Address not found'
        ];
    }
}