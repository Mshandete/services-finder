<?php

// ===============================
// DATABASE CONFIGURATION
// ===============================

$host = "localhost";
$dbname = "services_finder";
$username = "root";
$password = "";

// ===============================
// PDO CONNECTION
// ===============================

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Error mode (IMPORTANT for development)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch mode default (associative arrays)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    // Stop system if DB fails
    die("Database connection failed: " . $e->getMessage());

}