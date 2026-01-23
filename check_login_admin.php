<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// تحقق من وجود جلسة للمشرف
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'notloggedin']);
    exit;
}

$adminId = $_SESSION['admin_id'];

// تحديث آخر نشاط للمشرف
$stmt = $connection->prepare("UPDATE admin SET last_active = NOW() WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$stmt->close();

// جلب بيانات المشرف
$stmt = $connection->prepare("SELECT username, last_active FROM admin WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$res = $result->fetch_assoc();
$stmt->close();

if (!$res) {
    echo json_encode(['status' => 'notfound']);
    exit;
}

// التحقق من حالة النشاط (أونلاين / أوفلاين)
$lastActive = $res['last_active'];
$isOnline = false;

if ($lastActive !== null) {
    $diff = time() - strtotime($lastActive);
    if ($diff <= 300) { // خلال آخر 5 دقائق
        $isOnline = true;
    }
}

echo json_encode([
    'status' => 'loggedin',
    'username' => $res['username'],
    'online' => $isOnline
]);

