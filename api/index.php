<?php
require_once '../config/database.php';
require_once 'routes.php';

// Set UTF-8 encoding with complete configuration
ini_set('default_charset', 'UTF-8');

// Add back multibyte string functions if available
if (extension_loaded('mbstring')) {
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    mb_http_input('UTF-8');
}

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request first
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

$response = route($method, $route);

// Ensure JSON encoding preserves UTF-8 characters
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);