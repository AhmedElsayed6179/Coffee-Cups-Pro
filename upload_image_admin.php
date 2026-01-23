<?php
session_start();

if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['error' => 'You must be logged in.']);
    exit;
}

$username = $_SESSION['admin_username'];

// الاتصال بقاعدة البيانات
require_once 'config.php';

$uploadDir = __DIR__ . "/uploads_admin/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// رفع الصورة
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
        echo json_encode(['error' => 'Only JPG, PNG, GIF, or WEBP images are allowed.']);
        exit;
    }

    // حذف الصورة القديمة
    $stmt = $connection->prepare("SELECT profile_image FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($oldImage);
    $stmt->fetch();
    $stmt->close();

    if ($oldImage && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }

    // حفظ الصورة الجديدة
    $newName = uniqid("admin_", true) . "." . $ext;
    $target = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $connection->prepare("UPDATE admin SET profile_image = ? WHERE username = ?");
        $stmt->bind_param("ss", $newName, $username);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => 'Profile picture updated successfully.']);
    } else {
        echo json_encode(['error' => 'Failed to save uploaded image.']);
    }
    exit;
}

// حذف الصورة
if (isset($_POST['delete'])) {
    $stmt = $connection->prepare("SELECT profile_image FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($currentImage);
    $stmt->fetch();
    $stmt->close();

    if ($currentImage && file_exists($uploadDir . $currentImage)) {
        unlink($uploadDir . $currentImage);
    }

    $stmt = $connection->prepare("UPDATE admin SET profile_image = NULL WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => 'Profile picture deleted successfully.']);
    exit;
}

// لو مفيش عملية
echo json_encode(['error' => 'No action detected.']);