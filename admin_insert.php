<?php
require_once 'config.php';

$username = "ahmedelsayed";
$email = "ahmedelsayed6179@gmail.com";
$password_plain = "Ahmed1234#";

// تشفير كلمة المرور
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

$stmt = $connection->prepare("INSERT INTO admin (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hashed);
$stmt->execute();
$stmt->close();
$connection->close();

echo "Admin inserted successfully!";
