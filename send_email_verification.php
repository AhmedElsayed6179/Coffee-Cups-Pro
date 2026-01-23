<?php
session_start();
require 'config.php';
require 'mailer.php';

header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit;
}

$username = $_SESSION['username'];
$new_email = filter_var($_POST['new_email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$new_email) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit;
}

// توليد رمز تحقق عشوائي
$token = bin2hex(random_bytes(16));
$expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

// حفظ الرمز في جدول مؤقت email_verification
$stmt = $connection->prepare("
    INSERT INTO email_verification (username, new_email, token, expiry)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE token=?, expiry=?
");
$stmt->bind_param("ssssss", $username, $new_email, $token, $expiry, $token, $expiry);
$stmt->execute();
$stmt->close();

// إعداد البريد للتحقق
$mail->isHTML(true);
$mail->setFrom("coffeecups@gmail.com", "Coffee Cups");
$mail->addAddress($new_email, $username);
$mail->Subject = "Email Change Verification For " . htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE);
$mail->AddEmbeddedImage(__DIR__ . "/icons/coffee-cup.png", "coffee-cup_cid");
$mail->Subject = "Confirm Your New Email";
$mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #e5e7eb;">
    <div style="text-align: center;">
        <img src="cid:coffee-cup_cid" alt="Coffee Cups" style="width: 120px; height: auto; margin-bottom: 20px;">
        <h2 style="color: #1e293b; margin-bottom: 10px;">Email Change Request</h2>
        <p style="color: #475569; font-size: 15px;">
            Hello <strong>{$username}</strong>,<br>
            You recently requested to change your email address for your <strong>Coffee Cups</strong> account.
            Please confirm your new email by clicking the button below.
        </p>
        <a href="https://Coffee-Cups.page.gd/confirm_email.php?token={$token}"
           style="display: inline-block; margin-top: 20px; padding: 12px 28px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">
           Confirm New Email
        </a>
        <p style="color: #64748b; font-size: 14px; margin-top: 25px;">
            If you didn't request this change, please ignore this email.<br>
            This link will expire in 15 minutes for your security.
        </p>
        <hr style="margin: 25px 0; border: none; border-top: 1px solid #e2e8f0;">
        <p style="color: #94a3b8; font-size: 12px;">
            © 2025 Coffee Cups. All rights reserved.
        </p>
    </div>
</div>
END;

try {
    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Verification email sent.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
}
