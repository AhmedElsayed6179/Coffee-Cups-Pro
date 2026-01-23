<?php
session_start();
header("Content-Type: application/json");
require_once "config.php"; // اتصال قاعدة بيانات Coffee Cups

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$new_name = $_POST['new_name'] ?? '';
$password_input = $_POST['password'] ?? '';

try {
    // جلب المستخدم الحالي
    $stmt = $connection->prepare("SELECT id, full_name, password FROM guests WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit;
    }

    // تحقق كلمة السر
    if (!password_verify($password_input, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        exit;
    }

    // تحقق الاسم الجديد
    $new_name = trim($new_name);
    if (!preg_match("/^[a-zA-Z\s]{8,50}$/", $new_name)) {
        echo json_encode(["status" => "error", "message" => "Name must be 8-50 letters without numbers or symbols."]);
        exit;
    }

    // تحديث الاسم
    $stmt = $connection->prepare("UPDATE guests SET full_name = ? WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
    $stmt->bind_param("si", $new_name, $user_id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Full name updated successfully.", "old_name" => $user['full_name'], "new_name" => $new_name]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}
