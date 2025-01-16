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

function route($method, $route) {
    // Add this at the start of the function
    error_log("Received request: Method=$method, Route=$route");
    
    if ($method === 'OPTIONS') {
        return ['status' => 1, 'message' => 'OK'];
    }

    try {
        if ($route === 'test') {
            return ['status' => 1, 'message' => 'Test endpoint working'];
        }
        
        if ($route === 'validate' && $method === 'POST') {
            $validator = new AddressValidator();
            $rawInput = file_get_contents('php://input');
            error_log("Received input: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg()
                ];
            }
            
            return $validator->validate($input);
        }
        
        return [
            'status' => 0,
            'message' => "Invalid route or method: $method $route"
        ];
    } catch (Exception $e) {
        error_log("Error in route function: " . $e->getMessage());
        return [
            'status' => 0,
            'message' => 'Server error: ' . $e->getMessage()
        ];
    }
}