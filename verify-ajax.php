<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['token'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$token = $_POST['token'];

// استدعاء ملف الاتصال بقاعدة البيانات
require_once 'config.php';

$query = $connection->prepare("SELECT id, verified FROM guests WHERE token=?");
$query->bind_param("s", $token);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['verified'] == 1) {
        echo json_encode(["status" => "success", "message" => "Your account is already verified."]);
        exit;
    }

    $update = $connection->prepare("UPDATE guests SET verified=1 WHERE id=?");
    $update->bind_param("i", $row['id']);
    if ($update->execute()) {
        echo json_encode(["status" => "success", "message" => "Your account has been verified successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to verify your account."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
}

$connection->close();

