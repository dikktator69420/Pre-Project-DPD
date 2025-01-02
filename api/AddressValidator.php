<?php
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
}
