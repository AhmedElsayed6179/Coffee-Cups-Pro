<?php
date_default_timezone_set("Africa/Cairo");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
header("Content-Type: application/json");
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit;
}

// تحقق من تسجيل الدخول
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// جلب بيانات المستخدم
$stmt = $connection->prepare("SELECT full_name, email, phone FROM guests WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit;
}
$user = $res->fetch_assoc();
$name  = htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8');

// بيانات الحجز
$date   = $_POST['resDate'] ?? '';
$time   = $_POST['resTime'] ?? '';
$guests = $_POST['resGuests'] ?? '';

if (!$date || !$time || !$guests) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// ✅ تحقق من وجود حجز في نفس اليوم خلال ساعة واحدة
$check = $connection->prepare("
    SELECT id 
    FROM reservations 
    WHERE res_date = ? 
      AND (
          TIME_TO_SEC(TIMEDIFF(res_time, ?)) BETWEEN -3599 AND 3599
      )
    LIMIT 1
");
$check->bind_param("ss", $date, $time);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "error" => "This time slot is already booked. Please choose another hour."
    ]);
    exit;
}

// تحويل الوقت إلى تنسيق 12 ساعة
$time_obj = DateTime::createFromFormat('H:i', $time);
$time_formatted = $time_obj ? $time_obj->format('h:i A') : $time;

// حفظ الحجز في قاعدة البيانات
$insert = $connection->prepare("
    INSERT INTO reservations (user_id, full_name, email, phone, res_date, res_time, guests, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");
$insert->bind_param("issssss", $userId, $name, $email, $phone, $date, $time_formatted, $guests);
if (!$insert->execute()) {
    echo json_encode(["success" => false, "error" => "Database error while saving reservation"]);
    exit;
}

$current_time = date("l, F j, Y g:i A");

// إرسال البريد الإلكتروني للإدارة
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "coffeecups111@gmail.com";
    $mail->Password   = "xbnrcelmjthqbqap";
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;

    $mail->setFrom('coffeecups111@gmail.com', 'Coffee Cups');
    $mail->addReplyTo($email, $name);
    $mail->addAddress("ahmedelsayed6179@gmail.com", "Coffee Cups Admin");

    $mail->isHTML(true);
    $mail->Subject = "New Table Reservation from $name";
    $mail->Body = "
        <h3>New Reservation Request ☕</h3>
        <p><b>Name:</b> $name</p>
        <p><b>Email:</b> $email</p>
        <p><b>Phone:</b> $phone</p>
        <p><b>Date:</b> $date</p>
        <p><b>Time:</b> $time_formatted</p>
        <p><b>Guests:</b> $guests</p>
        <p><b>Submitted at:</b> $current_time</p>
    ";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Reservation confirmed and email sent"]);
} catch (Exception $e) {
    error_log("PHPMailer error: " . $mail->ErrorInfo);
    echo json_encode(["success" => false, "error" => "Reservation saved but email failed to send"]);
}

