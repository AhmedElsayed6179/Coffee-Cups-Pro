<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config.php";

if (!isset($_SESSION["admin_username"])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in."
    ]);
    exit;
}

$old_password = $_POST["old_password"] ?? "";
$new_password = $_POST["new_password"] ?? "";

if (empty($old_password) || empty($new_password)) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required."
    ]);
    exit;
}

$username = $_SESSION["admin_username"];

// جلب كلمة السر القديمة من قاعدة البيانات
$stmt = $connection->prepare("SELECT password FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    exit;
}

// التحقق من كلمة السر القديمة
if (!password_verify($old_password, $user["password"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Incorrect old password"
    ]);
    exit;
}

// منع استخدام نفس كلمة السر القديمة
if (password_verify($new_password, $user["password"])) {
    echo json_encode([
        "status" => "error",
        "message" => "New password cannot be the same as the old one."
    ]);
    exit;
}

// تشفير كلمة السر الجديدة
$new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

// ✅ تنفيذ التحديث في قاعدة البيانات
$update = $connection->prepare("UPDATE admin SET password = ? WHERE username = ?");
$update->bind_param("ss", $new_hashed, $username);

if ($update->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update password. Please try again."
    ]);
}

$connection->close();
