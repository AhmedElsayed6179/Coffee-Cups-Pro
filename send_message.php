<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
header('Content-Type: application/json');
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

// تأكد من تسجيل الدخول
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

// جلب الاسم والإيميل من قاعدة البيانات
$stmt = $connection->prepare("SELECT full_name, email FROM guests WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}
$user = $res->fetch_assoc();
$name  = htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');

// باقي الحقول التي يمكن للمستخدم تعديلها
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

if (!$subject || !$message) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// إرسال البريد
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'coffeecups111@gmail.com';
    $mail->Password   = 'xyrkwwwsdelkrqth';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('coffeecup@gmail.com', 'Coffee Cups');
    $mail->addReplyTo($email, $name); // الرد على المستخدم
    $mail->addAddress('ahmedelsayed6179@gmail.com', 'Ahmed Elsayed');

    $mail->isHTML(true);
    $mail->Subject = 'New Message from Coffee Cups Contact Form';
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background: #f9f9f9;'>
      <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>New Contact Form Message</h2>
      <p><strong>Name:</strong> $name</p>
      <p><strong>Email:</strong> $email</p>
      <p><strong>Subject:</strong> $subject</p>
      <p><strong>Message:</strong><br>$message</p>
      <hr style='border: none; border-top: 1px solid #ccc; margin: 20px 0;'/>
      <p style='font-size: 12px; color: #7f8c8d;'>This message was sent from the Coffee Cups contact form.</p>
    </div>
    ";

    $mail->send();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $mail->ErrorInfo]);
}
