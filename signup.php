<?php
session_start();
header("Content-Type: application/json");

// ✅ السماح فقط بطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// ✅ استدعاء الاتصال بقاعدة البيانات
require_once 'config.php';

// ✅ استقبال البيانات
$fullname_value = trim($_POST['fullname'] ?? '');
$username_value = trim($_POST['username'] ?? '');
$email_value = trim($_POST['email'] ?? '');
$phone_value = trim($_POST['phone'] ?? '');
$password_value_raw = $_POST['password'] ?? '';
$password_value = password_hash($password_value_raw, PASSWORD_DEFAULT);
$dob_value = $_POST['dob'] ?? '';
$gender_value = $_POST['gender'] ?? '';

// ✅ التحقق من وجود المستخدم مسبقًا
$checkQuery = $connection->prepare("SELECT id, username, email, phone, verified, token FROM guests WHERE username = ? OR email = ? OR phone = ?");
$checkQuery->bind_param("sss", $username_value, $email_value, $phone_value);
$checkQuery->execute();
$result = $checkQuery->get_result();

$usernameExists = false;
$emailExists = false;
$phoneExists = false;
$unverifiedEmailUser = null;
$unverifiedUsernameUser = null;

while ($row = $result->fetch_assoc()) {
    if (strcasecmp($row['username'], $username_value) === 0) {
        $usernameExists = true;
        if ($row['verified'] == 0) $unverifiedUsernameUser = $row;
    }
    if (strcasecmp($row['email'], $email_value) === 0) {
        $emailExists = true;
        if ($row['verified'] == 0) $unverifiedEmailUser = $row;
    }
    if ($row['phone'] === $phone_value) {
        $phoneExists = true;
    }
}

// ✅ الحالة 1: الحساب غير مفعل بناءً على الإيميل → إعادة إرسال التأكيد
if ($unverifiedEmailUser) {
    $token = bin2hex(random_bytes(16));
    $update = $connection->prepare("UPDATE guests SET token=? WHERE id=?");
    $update->bind_param("si", $token, $unverifiedEmailUser['id']);
    $update->execute();

    // 🔄 إعادة إرسال بريد التأكيد
    $mail = require __DIR__ . "/mailer.php";
    $mail->isHTML(true);
    $mail->setFrom("coffeecups@gmail.com", "Coffee Cups");
    $mail->addAddress($unverifiedEmailUser['email'], $unverifiedEmailUser['username']);
    $mail->Subject = "Confirm Your Account (Resent)";
    $mail->AddEmbeddedImage(__DIR__ . "/icons/coffee-cup.png", "coffee-cup_cid");
    $mail->Body = <<<END
<div style="font-family:'Segoe UI',Tahoma,sans-serif;background:#f9fafb;padding:30px;border-radius:12px;max-width:600px;margin:auto;border:1px solid #e5e7eb;">
    <div style="text-align:center;">
        <img src="cid:coffee-cup_cid" alt="coffee-cup" style="width:120px;height:auto;margin-bottom:20px;">
        <h2>Confirm Your Email</h2>
        <p>Hello <strong>{$unverifiedEmailUser['username']}</strong>! You haven't verified your email yet. Click below to activate your account.</p>
        <a href="https://Coffee-Cups.page.gd/verify.php?token=$token" style="display:inline-block;padding:12px 28px;background-color:#22c55e;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Confirm Email</a>
        <p>If you didn't register, ignore this email.</p>
    </div>
</div>
END;

    try {
        $mail->send();
        echo json_encode([
            "status" => "pending_verification",
            "message" => "This email is already registered but not verified. A new confirmation email has been sent to your address."
        ]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to send verification email."]);
    }
    exit;
}

// ✅ الحالة 2: اليوزر موجود وغير مفعل (لكن الإيميل مختلف)
if ($unverifiedUsernameUser && !$emailExists) {
    echo json_encode([
        "status" => "error",
        "type" => "username_pending",
        "message" => "This username is already used in an unverified account. Please verify your previous account or choose another username."
    ]);
    exit;
}

// ✅ الحالة 3: الحساب موجود ومفعل مسبقًا
if ($usernameExists && $emailExists) {
    echo json_encode(["status" => "error", "type" => "both", "message" => "Account already exists and is verified."]);
    exit;
} elseif ($usernameExists) {
    echo json_encode(["status" => "error", "type" => "username", "message" => "Username already exists."]);
    exit;
} elseif ($emailExists) {
    echo json_encode(["status" => "error", "type" => "email", "message" => "Email already exists and is verified."]);
    exit;
}

if ($phoneExists) {
    echo json_encode(["status" => "error", "type" => "phone", "message" => "This phone number is already registered."]);
    exit;
}

if (empty($dob_value)) {
    echo json_encode(["status" => "error", "message" => "Please enter your date of birth."]);
    exit;
}

if (empty($gender_value)) {
    echo json_encode(["status" => "error", "message" => "Please select your gender."]);
    exit;
}

// منع تسجيل المستخدمين المواليد بعد 2015
$birthYear = (int)date("Y", strtotime($dob_value));
if ($birthYear > 2015) {
    echo json_encode(["status" => "error", "message" => "You must be born in 2015 or earlier to register."]);
    exit;
}

// ✅ الحالة 4: إنشاء حساب جديد
$token = bin2hex(random_bytes(16));
$insert = $connection->prepare("INSERT INTO guests(full_name, username, email, phone, password, dob, gender, token, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
$insert->bind_param("ssssssss", $fullname_value, $username_value, $email_value, $phone_value, $password_value, $dob_value, $gender_value, $token);

if (!$insert->execute()) {
    echo json_encode(["status" => "error", "message" => "Registration failed. Try again later."]);
    exit;
}

// ✅ إرسال بريد التفعيل للمستخدم الجديد
$mail = require __DIR__ . "/mailer.php";
$mail->isHTML(true);
$mail->setFrom("coffeecups@gmail.com", "Coffee Cups");
$mail->addAddress($email_value, $username_value);
$mail->Subject = "Confirm Your Account";
$mail->AddEmbeddedImage(__DIR__ . "/icons/coffee-cup.png", "coffee-cup_cid");
$mail->Body = <<<END
<div style="font-family:'Segoe UI',Tahoma,sans-serif;background:#f9fafb;padding:30px;border-radius:12px;max-width:600px;margin:auto;border:1px solid #e5e7eb;">
    <div style="text-align:center;">
        <img src="cid:coffee-cup_cid" alt="Coffee Cups" style="width:120px;height:auto;margin-bottom:20px;">
        <h2>Confirm Your Email</h2>
        <p>Welcome <strong>$fullname_value</strong>! Please verify your email address to activate your <strong>Coffee Cups</strong> account.</p>
        <a href="https://Coffee-Cups.page.gd/verify.php?token=$token" style="display:inline-block;padding:12px 28px;background-color:#22c55e;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Confirm Email</a>
        <p>If you didn't register, ignore this email.</p>
    </div>
</div>
END;

try {
    $mail->send();
    $_SESSION['username'] = $username_value;
    echo json_encode(["status" => "success", "message" => "Account created successfully. Please check your email to verify your account."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to send confirmation email."]);
}

$connection->close();
