<?php
require_once 'AddressValidator.php';

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
}
