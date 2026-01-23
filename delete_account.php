<?php
session_start();
header("Content-Type: application/json");
require_once "config.php"; // اتصال قاعدة بيانات Coffee Cups

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in."
    ]);
    exit;
}

// التأكد من إرسال كلمة السر
$password_input = $_POST['password'] ?? '';
if (empty($password_input)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password is required."
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // جلب المستخدم
    $stmt = $connection->prepare("SELECT id, password FROM guests WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);

    $stmt->bind_param("i", $user_id);
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

    // التحقق من كلمة السر
    if (!password_verify($password_input, $user['password'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Incorrect password."
        ]);
        exit;
    }

    // حذف المستخدم
    $stmt = $connection->prepare("DELETE FROM guests WHERE id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete account."
        ]);
        exit;
    }

    // تدمير الجلسة بعد الحذف
    session_destroy();

    echo json_encode([
        "status" => "success",
        "message" => "Your account has been deleted successfully."
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred: " . $e->getMessage()
    ]);
    exit;
}
