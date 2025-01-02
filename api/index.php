<?php
require_once '../config/database.php';
require_once 'routes.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

$response = route($method, $route);

echo json_encode($response);
