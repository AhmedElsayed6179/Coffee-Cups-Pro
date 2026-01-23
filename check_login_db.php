<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}


$userId = $_SESSION['user_id'];

$stmt = $connection->prepare(
    "SELECT username, last_active FROM guests WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    echo json_encode(['status' => 'notfound']);
    exit;
}

$lastActive = $res['last_active'];
$isOnline = false;
if ($lastActive !== null) {
    $diff = time() - strtotime($lastActive);
    if ($diff <= 300) $isOnline = true;
}

echo json_encode([
    'status' => 'loggedin',
    'username' => $res['username'],
    'online' => $isOnline
]);

