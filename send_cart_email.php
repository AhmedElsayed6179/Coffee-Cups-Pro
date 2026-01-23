<?php
header("Content-Type: application/json; charset=UTF-8");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// ================== 🔹 استقبال البيانات القادمة من JavaScript ==================
$input = json_decode(file_get_contents("php://input"), true);
$cart = $input["cart"] ?? [];
$customerName = trim($input["name"] ?? "");
$customerPhone = trim($input["phone"] ?? "");

// ================== 🔹 التحقق من صحة البيانات ==================
if (empty($cart)) {
    echo json_encode(["success" => false, "message" => "🛒 Cart is empty"]);
    exit;
}

if (empty($customerName) || empty($customerPhone)) {
    echo json_encode(["success" => false, "message" => "⚠️ Name or phone number is missing"]);
    exit;
}

// التحقق من رقم الموبايل (أرقام فقط وطوله من 10 إلى 11 رقم)
if (!preg_match('/^\d{10,11}$/', $customerPhone)) {
    echo json_encode(["success" => false, "message" => "📱 Invalid phone number format"]);
    exit;
}

// ================== 🔹 إعداد تفاصيل الرسالة ==================
date_default_timezone_set('Africa/Cairo');
$date = date("l, d F Y - h:i:s A");
$total = 0;
$orderList = "";

foreach ($cart as $item) {
    $name = htmlspecialchars($item["name"]);
    $qty = (int)$item["qty"];
    $price = (float)$item["price"];
    $subtotal = $price * $qty;
    $orderList .= "<li><b>$name</b> × $qty — $" . number_format($subtotal, 2) . "</li>";
    $total += $subtotal;
}

$message = "
<h2>🧾 New Order (Pay at Store)</h2>
<p>
<b>👤 Customer:</b> " . htmlspecialchars($customerName) . "<br>
<b>📞 Phone:</b> " . htmlspecialchars($customerPhone) . "<br>
<b>📅 Date:</b> $date
</p>
<hr>
<ul>$orderList</ul>
<p><b>Total:</b> $" . number_format($total, 2) . "</p>
";

// ================== 🔹 إرسال البريد عبر PHPMailer ==================
$mail = new PHPMailer(true);

try {
    // إعدادات SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'coffeecups111@gmail.com';
    $mail->Password = 'kphaghdvkmpjsajr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // إعدادات البريد
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('coffeecups111@gmail.com', '☕ Coffee Cups Orders');
    $mail->addAddress('ahmedelsayed6179@gmail.com', 'Store Manager');

    $mail->isHTML(true);
    $mail->Subject = "🛍️ New Pay-at-Store Order from $customerName";
    $mail->Body = $message;

    // إرسال البريد
    if ($mail->send()) {
        echo json_encode(["success" => true, "message" => "✅ Order email sent successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "❌ Failed to send email."]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Mailer Error: " . $mail->ErrorInfo
    ]);
}
