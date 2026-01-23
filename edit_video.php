<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>
            alert('🚫 Access denied! Please log in first.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

// رفع فيديو جديد وتحديث المحتوى
if (isset($_POST['update_content'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // إذا تم رفع ملف فيديو جديد
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
        $uploadDir = 'video/';
        $fileName = basename($_FILES['video_file']['name']);
        $targetFile = $uploadDir . $fileName;

        // رفع الملف للسيرفر
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFile)) {
            $file_path = $targetFile;
        } else {
            $file_path = $_POST['file_path'];
        }
    } else {
        $file_path = $_POST['file_path'];
    }

    // تحديث البيانات في قاعدة البيانات
    $stmt = $connection->prepare("UPDATE video_content SET title=?, description=?, file_path=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $file_path, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('✏️ Content updated successfully!'); window.location='edit_video.php';</script>";
}

// جلب جميع المحتويات
$result = $connection->query("SELECT * FROM video_content");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Video | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/edit_video.css">
    <!-- Render All Elements Normally -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- Font Awesome Library (CDN) for social media icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="icons/coffee-cup.png" type="image/x-icon">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <body>
        <!-- Preloader -->
        <div id="preloader">
            <div class="dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <!-- nav bar -->
        <div class="video_bg">
            <div class="navigation">
                <nav class="welcome">
                    <div class="left_side">
                        <a href="index.html" class="mylink">
                            <h1>coffee<span>cups</span></h1>
                        </a>
                    </div>
                    <div class="right_side">
                        <a href="login.html" class="link_login_mobile link_login">login</a>
                        <div id="hamburger" class="hamburger">☰</div>
                        <div class="links">
                            <ul>
                                <li><a href="home_admin.html" class="link">home</a></li>
                                <li><a href="gallery_admin.php" class="link">gallery</a></li>
                                <li><a href="dashboard.php" class="link">Dashboard</a></li>
                                <li><a href="edit_video.php" class="link">Edit Video</a></li>
                                <li><a href="account_admin.php" class="link">My Profile</a></li>
                                <li><a href="promo_admin.php" class="link">promo</a></li>
                                <li><a href="login.html" class="link_login">login</a></li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Edit Video Section -->
<section class="video-edit">
    <h2>Edit Video Content</h2>
    <?php while ($row = $result->fetch_assoc()): ?>
        <form class="video-card" method="post" enctype="multipart/form-data">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>">

            <label>Description</label>
            <textarea name="description"><?php echo htmlspecialchars($row['description']); ?></textarea>

            <label>Current Video</label>
            <?php if ($row['file_path']): ?>
                <video src="<?php echo htmlspecialchars($row['file_path']); ?>" controlsList="nodownload" controls></video>
            <?php else: ?>
                <p>N/A</p>
            <?php endif; ?>

            <label>Upload New Video</label>
            <input type="file" name="video_file" accept="video/*">

            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($row['file_path']); ?>">

            <button type="submit" name="update_content">Update</button>
        </form>
    <?php endwhile; ?>
</section>
        </div>
        <!-- Start Footer -->
        <footer class="footer">
            <div class="footer-container">
                <!-- About -->
                <div class="footer-about">
                    <h2>Coffee<span>Cups</span></h2>
                    <p>Your daily dose of fresh coffee & vibes ☕</p>
                    <p><strong>📍 Address:</strong> 123 Coffee Street, Alexandria, Egypt</p>
                    <p><strong>📞 Phone:</strong> +20 101 234 5678</p>
                    <p><strong>📧 Email:</strong> <a style="text-transform: lowercase;" href="mailto:ahmedelsayed6179@gmail.com">coffeecups@gmail.com</a></p>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="home_admin.html">Home</a></li>
                        <li><a href="gallery_admin.php">Gallery</a></li>
                        <li><a href="account_admin.php">My Profile</a></li>
                        <li><a href="edit_video.php">Edit Video</a></li>
                        <li><a href="promo_admin.php">promo</a></li>
                        <li><a class="authLink" href="login.html">Login</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="footer-social">
                    <h3>Follow Us</h3>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 <a href="index.html"><span><b>CoffeeCups.</b></span></a> All Rights Reserved.</p>
                <p>Website By : <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank"><span><b>Ahmed
                                Mohamed.</b></span></a></p>
            </div>
        </footer>
        <!-- Linked external JavaScript file named dashboard.js -->
        <script src="java script/dashboard.js"></script>
        <script>
            document.querySelectorAll('.video-card textarea').forEach(textarea => {
                const adjustHeight = el => {
                    el.style.height = 'auto';
                    el.style.height = el.scrollHeight + 'px';
                };

                adjustHeight(textarea);

                textarea.addEventListener('input', () => adjustHeight(textarea));
            });
        </script>
    </body>
</html>