<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
requireAdmin(); // Only admins can manage users via API

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, CONCAT(firstname, ' ', lastname) as name FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['users' => $users]);
}
?>