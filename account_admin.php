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

$loggedIn = false;
$username = "";

$checkLoginUrl = __DIR__ . "/check_login_admin.php";

$response = @file_get_contents($checkLoginUrl);
if ($response) {
    $data = json_decode($response, true);

    if (isset($data['status']) && $data['status'] === 'loggedin') {
        $loggedIn = true;
        $username = $data['username'] ?? "";
    }
}

if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    $stmt = $connection->prepare("SELECT username, email FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $loggedIn = true;
        $username = htmlspecialchars($user['username'], ENT_QUOTES);
        $email = htmlspecialchars($user['email'], ENT_QUOTES);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $username; ?> | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/account.css">
    <!-- Render All Elements Normally -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- Font Awesome Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Favicon -->
    <link rel="shortcut icon" href="icons/coffee-cup.png" type="image/x-icon">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CropperJS -->
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="account">
        <!-- ===== Navigation Bar ===== -->
        <div class="navigation">
            <nav class="welcome">
                <div class="left_side">
                    <a href="home_admin.html" class="mylink">
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

        <!-- ===== Account Section ===== -->
        <?php if ($loggedIn): ?>
            <section class="welcome-user">
                <div class="container">
                    <?php
                    $stmt = $connection->prepare("SELECT id, email, profile_image FROM admin WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $guest = $result->fetch_assoc();
                    $stmt->close();

                    if (!empty($guest['profile_image']) && $guest['profile_image'] !== 'NULL') {
                        $profileImage = "uploads_admin/" . htmlspecialchars($guest['profile_image']);
                    } else {
                        $profileImage = "uploads_admin/default.webp";
                    }
                    ?>

                    <!-- ✅ الصورة وبيانات المستخدم -->
                    <div class="user-info">
                        <img src="<?php echo $profileImage; ?>" alt="User" class="user-img">
                        <h2>Welcome,<br><?php echo htmlspecialchars($username); ?> 👋</h2>
                        <p>Manage your account settings below:</p>
                    </div>

                    <div class="account-settings">
                        <ul>
                            <li><button class="btn" id="changePasswordBtn"><i class="fas fa-lock"></i> Change Password</button></li>
                            <li><button type="button" class="btn" id="changePicBtn"><i class="fas fa-image"></i> Change Profile Picture</button></li>
                            <input type="file" id="profileUpload" name="profile_image" accept="image/*" style="display:none;">
                            <li><button type="button" id="deletePicBtn" class="btn delete"><i class="fas fa-trash-alt"></i> Delete Profile Picture</button></li>
                            <li><button type="button" id="deleteBtn" class="btn delete"><i class="fas fa-user-times"></i> Delete Account</button></li>
                        </ul>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="welcome-user">
                <div class="container">
                    <h2>Welcome, Guest 👋</h2>
                    <p>Please <a href="login.html">login</a> to manage your account.</p>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- ===== Footer ===== -->
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="edit_video.php">Edit Video</a></li>
                    <li><a href="account_admin.php">My Profile</a></li> 
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
            <p>© 2025 <a href="home_admin.html"><span><b>CoffeeCups.</b></span></a> All Rights Reserved.</p>
            <p>Website By:
                <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank">
                    <span><b>Ahmed Mohamed.</b></span>
                </a>
            </p>
        </div>
    </footer>

    <!-- Linked external JavaScript -->
    <script src="java script/account_admin.js"></script>
</body>


</html>