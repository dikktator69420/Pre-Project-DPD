<?php
/*require_once 'AddressValidator.php';

function route($method, $route) {
    $validator = new AddressValidator();

    switch ($route) {
        case 'validate':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                return $validator->validate($input);
            }
            break;
        default:
            return ['error' => 'Route not found'];
    }
}*/
// routes.php
require_once 'AddressValidator.php';

function route($method, $route) {
    try {
        $validator = new AddressValidator();
        
        switch ($route) {
            case 'validate':
                if ($method === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return [
                            'status' => 0,
                            'message' => 'Invalid JSON input'
                        ];
                    }
                    return $validator->validate($input);
                }
                return [
                    'status' => 0,
                    'message' => 'Method not allowed'
                ];
            
            default:
                return [
                    'status' => 0,
                    'message' => 'Route not found'
                ];
        }
    } catch (Exception $e) {
        return [
            'status' => 0,
            'message' => 'Internal server error'
        ];
    }
}
