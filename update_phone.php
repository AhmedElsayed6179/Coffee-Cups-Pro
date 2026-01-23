<?php
session_start();
header("Content-Type: application/json");
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

$username = $_SESSION['username'] ?? '';
$newPhone = trim($_POST['new_phone'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$newPhone || !$password) {
    echo json_encode(["status" => "error", "message" => "Missing required data."]);
    exit;
}

// ✅ تحقق من أن الرقم يبدأ بـ 01 وطوله 11
if (!preg_match('/^01\d{9}$/', $newPhone)) {
    echo json_encode(["status" => "error", "message" => "Invalid phone number format."]);
    exit;
}

// ✅ جلب بيانات المستخدم
$stmt = $connection->prepare("SELECT id, password FROM guests WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit;
}

// ✅ التحقق من كلمة المرور
if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    exit;
}

// ✅ التحقق من أن رقم الهاتف غير مستخدم
$checkPhone = $connection->prepare("SELECT id FROM guests WHERE phone=? AND id != ?");
$checkPhone->bind_param("si", $newPhone, $user['id']);
$checkPhone->execute();
if ($checkPhone->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This phone number is already registered."]);
    exit;
}

// ✅ تحديث الرقم
$update = $connection->prepare("UPDATE guests SET phone=? WHERE id=?");
$update->bind_param("si", $newPhone, $user['id']);

if ($update->execute()) {
    echo json_encode(["status" => "success", "message" => "Phone number updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update phone number."]);
}

$connection->close();
