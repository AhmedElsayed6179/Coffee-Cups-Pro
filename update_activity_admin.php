<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'notloggedin']);
    exit;
}

$adminId = $_SESSION['admin_id'];

$stmt = $connection->prepare("UPDATE admin SET last_active = NOW() WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not update last activity']);
}

$stmt->close();
