<?php
// routes.php

function route($method, $route) {
    // Log request details for debugging
    error_log("Received request: Method=$method, Route=$route");
    
    require_once __DIR__ . '/AddressValidator.php';
    
    if ($method === 'OPTIONS') {
        return ['status' => 1, 'message' => 'OK'];
    }
    
    try {
        if ($route === 'test') {
            return ['status' => 1, 'message' => 'Test endpoint working with UTF-8: äöüß'];
        }
        
        $validator = new AddressValidator();
        
        if ($route === 'validate' && $method === 'POST') {
            $rawInput = file_get_contents('php://input');
            error_log("Received input: " . $rawInput);
            
            // Use JSON_INVALID_UTF8_IGNORE flag to handle potential invalid UTF-8 sequences
            $input = json_decode($rawInput, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg() . ' - ' . json_last_error()
                ];
            }
            
            return $validator->validate($input);
        }
        
        // Street-specific recommend route
        if ($route === 'recommend-strasse' && $method === 'POST') {
            $rawInput = file_get_contents('php://input');
            error_log("Received recommend-strasse request: " . $rawInput);
            
            $input = json_decode($rawInput, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg()
                ];
            }
            
            if (!isset($input['query']) || !is_string($input['query'])) {
                return [
                    'status' => 0,
                    'message' => 'Missing or invalid query string'
                ];
            }
            
            return $validator->recommendStrasse($input['query']);
        }
        
        // City-specific recommend route
        if ($route === 'recommend-stadt' && $method === 'POST') {
            $rawInput = file_get_contents('php://input');
            error_log("Received recommend-stadt request: " . $rawInput);
            
            $input = json_decode($rawInput, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg()
                ];
            }
            
            if (!isset($input['query']) || !is_string($input['query'])) {
                return [
                    'status' => 0,
                    'message' => 'Missing or invalid query string'
                ];
            }
            
            return $validator->recommendStadt($input['query']);
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