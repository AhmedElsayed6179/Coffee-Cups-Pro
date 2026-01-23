<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config.php";

if (!isset($_SESSION["username"])) {
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

$username = $_SESSION["username"];

// جلب كلمة السر القديمة من قاعدة البيانات
$stmt = $connection->prepare("SELECT password FROM guests WHERE username = ?");
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

// تحديث كلمة السر وتعيين verified = 0
$update = $connection->prepare("UPDATE guests SET password = ?, verified = 0 WHERE username = ?");
$update->bind_param("ss", $new_hashed, $username);

if ($update->execute()) {
    session_destroy(); // إنهاء الجلسة بعد التغيير
    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully. Please log in again and verify your email."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database update failed. Please try again."
    ]);
}
