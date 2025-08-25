<?php
// Centralized PDO configuration for the application

$dbHost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$dbName = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'fertilizer';
$dbUser = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $pdoOptions);
} catch (PDOException $e) {
    die('Database Connection Failed: ' . $e->getMessage());
}

