<?php
/*
class AddressValidator {
    public function validate($address) {
        // Beispielprüfung für PLZ und Ort
        if (empty($address['PLZ']) || empty($address['Ort'])) {
            return ['status' => 0, 'message' => 'Missing required fields'];
        }
        // Füge hier die eigentliche Validierungslogik hinzu
        return [
            'status' => 1,
            'corrected_address' => [
                'Strasse' => 'Beispielstraße',
                'Hausnummer' => '50',
                'PLZ' => $address['PLZ'],
                'Ort' => $address['Ort']
            ]
        ];
    }
}*/
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