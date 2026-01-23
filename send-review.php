<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
header('Content-Type: application/json');
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ تأكد أن المستخدم مسجل
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Not logged in"]);
        exit;
    }

    // 2️⃣ جلب الاسم والإيميل من DB
    $stmt = $connection->prepare("SELECT full_name, email FROM guests WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }
    $user = $res->fetch_assoc();
    $name = htmlspecialchars($user['full_name']);
    $email = htmlspecialchars($user['email']);

    // 3️⃣ باقي الحقول التي يدخلها المستخدم (مسموح التعديل)
    $type = htmlspecialchars($_POST['type']);
    $message = htmlspecialchars($_POST['message']);
    $rating = htmlspecialchars($_POST['rating'] ?? 'No rating');
    $products = isset($_POST['products']) ? implode(', ', $_POST['products']) : 'No product selected';

    // 4️⃣ إعداد الوقت
    date_default_timezone_set('Africa/Cairo');
    $current_time = date('l, F j, Y g:i A');

    // 5️⃣ إعداد البريد
    $adminEmail = "ahmedelsayed6179@gmail.com";
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'coffeecups111@gmail.com';
        $mail->Password = 'xbnrcelmjthqbqap';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('coffeecup@gmail.com', 'Coffee Cups');
        $mail->addAddress($adminEmail);
        $mail->isHTML(true);
        $mail->Subject = "New Feedback from $name";
        $mail->Body = "
          <h3>New Feedback Received</h3>
          <p><b>Name:</b> $name</p>
          <p><b>Email:</b> $email</p>
          <p><b>Type:</b> $type</p>
          <p><b>Rating:</b> ⭐ $rating / 5</p>
          <p><b>Products:</b> $products</p>
          <p><b>Message:</b><br>$message</p>
          <p><b>Sent on:</b> $current_time</p>
        ";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Feedback sent successfully!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $mail->ErrorInfo]);
    }
}
