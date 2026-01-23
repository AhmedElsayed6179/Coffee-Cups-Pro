<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'notloggedin']);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $connection->prepare("UPDATE guests SET last_active = NOW() WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

echo json_encode(['status' => 'ok']); 
echo json_encode(['status' => 'updated']); 
