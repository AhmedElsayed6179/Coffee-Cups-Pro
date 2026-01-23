<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config.php";

// ✅ التحقق من تسجيل الدخول
if (!isset($_SESSION["username"])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit;
}

// ✅ استقبال البيانات
$new_username = trim($_POST["new_username"] ?? "");
$password = $_POST["password"] ?? "";

if ($new_username === "" || $password === "") {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

$current_user = $_SESSION["username"];

// ✅ التحقق من أن اسم المستخدم يحتوي فقط على حروف وأرقام وطوله مناسب
if (!preg_match("/^[a-zA-Z0-9]{5,20}$/", $new_username)) {
    echo json_encode([
        "status" => "error",
        "message" => "Username must be 5–20 characters long and contain only letters and numbers."
    ]);
    exit;
}

// ✅ منع تكرار نفس الاسم القديم
if (strcasecmp($new_username, $current_user) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "The new username is the same as your current username."
    ]);
    exit;
}

// ✅ التحقق من أن الاسم الجديد غير مستخدم مسبقًا
$check = $connection->prepare("SELECT id FROM guests WHERE username = ? LIMIT 1");
$check->bind_param("s", $new_username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This username is already taken."]);
    exit;
}

// ✅ جلب كلمة المرور الحالية من قاعدة البيانات
$stmt = $connection->prepare("SELECT password FROM guests WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found."]);
    exit;
}

// ✅ التحقق من كلمة المرور
if (!password_verify($password, $user["password"])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    exit;
}

// ✅ تنفيذ التحديث
$update = $connection->prepare("UPDATE guests SET username = ? WHERE username = ?");
$update->bind_param("ss", $new_username, $current_user);

if ($update->execute()) {
    $_SESSION["username"] = $new_username;
    echo json_encode([
        "status" => "success",
        "message" => "Your username has been updated successfully!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update username. Please try again."
    ]);
}

$connection->close();

