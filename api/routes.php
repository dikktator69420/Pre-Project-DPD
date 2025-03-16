<?php
// routes.php

function route($method, $route) {
    // Add this at the start of the function
    error_log("Received request: Method=$method, Route=$route");
    
    require_once 'C:\Users\Finian\Desktop\School\4EHIF\PRE\Pre-Project-DPD\api\AddressValidator.php';
    
    if ($method === 'OPTIONS') {
        return ['status' => 1, 'message' => 'OK'];
    }
    
    try {
        if ($route === 'test') {
            return ['status' => 1, 'message' => 'Test endpoint working'];
        }
        
        $validator = new AddressValidator();
        
        if ($route === 'validate' && $method === 'POST') {
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
        
        // Street-specific recommend route
        if ($route === 'recommend-strasse' && $method === 'POST') {
            $rawInput = file_get_contents('php://input');
            error_log("Received recommend-streets request: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg()
                ];
            }
            
            // Simply pass the query string to the recommendStreets function
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
            error_log("Received recommend-cities request: " . $rawInput);
            
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 0,
                    'message' => 'Invalid JSON input: ' . json_last_error_msg()
                ];
            }
            
            // Simply pass the query string to the recommendCities function
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