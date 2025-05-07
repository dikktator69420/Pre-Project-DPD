<?php
// setup_database.php - Run this script to create and populate the database

// Directly include the database config to get access to variables
$dbConfig = require 'config/database.php';

try {
    // First connect without specifying a database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']}",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']}");
    
    // Use the database
    $pdo->exec("USE {$dbConfig['dbname']}");
    
    // Create addresses table
    $pdo->exec("
        DROP TABLE IF EXISTS addresses;
        
        CREATE TABLE addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            straße VARCHAR(255) NOT NULL,
            haus_nummer VARCHAR(50),
            plz VARCHAR(20) NOT NULL,
            stadt VARCHAR(100) NOT NULL
        );
    ");
    
    // Insert test data
    $testData = [
        ['Mariahilfer Straße', '50', '1060', 'Wien'],
        ['Mariahilferstraße', '50', '1060', 'Wien'],
        ['Mariahilfer Straße', '50/Stiege 7/8. Stock', '1060', 'Wien'],
        ['Stephansplatz', '1', '1010', 'Wien'],
        ['Kärntner Straße', '45', '1010', 'Wien'],
        ['Ring14', '10', '2700', 'Wiener Neustadt'],
        ['10er Straße', '22', '3100', 'St. Pölten'],
        ['Karl-Renner-Ring', '3', '1010', 'Wien'],
        ['Mozart-Gasse', '4-6', '8010', 'Graz'],
        ['Hauptstraße', '7A', '4020', 'Linz'],
        ['Bahnhofplatz', '3/5/7', '6020', 'Innsbruck'],
        ['Donaustraße', '12-14', '1220', 'Wien'],
        ['Neubaugasse', '15/3/8', '1070', 'Wien'],
        ['Landstraße', '34/Tür 12', '4020', 'Linz'],
        ['Universitätsstr.', '1', '5020', 'Salzburg'],
        ['Dr. Karl Renner Pl.', '2', '1010', 'Wien'],
        ['Schönbrunner Str.', '213', '1120', 'Wien']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO addresses (straße, haus_nummer, plz, stadt) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($testData as $row) {
        $stmt->execute($row);
    }
    
    echo "Database setup complete! Created and populated address_validator database with test data.";
    
} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}