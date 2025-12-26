<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'dolphin_crm');
define('DB_USER', 'root'); // Change as needed
define('DB_PASS', ''); // Change as needed

function getDBConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>