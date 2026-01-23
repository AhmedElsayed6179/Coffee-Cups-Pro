<?php
session_start();
require_once 'config.php';

$loggedIn = false;
$username = "";

$checkLoginUrl = __DIR__ . "/check_login_db.php";

$response = @file_get_contents($checkLoginUrl);
if ($response) {
    $data = json_decode($response, true);

    if (isset($data['status']) && $data['status'] === 'loggedin') {
        $loggedIn = true;
        $username = $data['username'] ?? "";
    }
}

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $connection->prepare("SELECT username, email, full_name, phone FROM guests WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $loggedIn = true;
        $username = htmlspecialchars($user['username'], ENT_QUOTES);
        $fullname = htmlspecialchars($user['full_name'], ENT_QUOTES);
        $email = htmlspecialchars($user['email'], ENT_QUOTES);
        $phone = htmlspecialchars($user['phone'], ENT_QUOTES);
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
                    <a href="index2.html" class="mylink">
                        <h1>coffee<span>cups</span></h1>
                    </a>
                </div>
                <div class="right_side">
                    <a href="login.html" class="link_login_mobile link_login">login</a>
                    <div id="hamburger" class="hamburger">☰</div>
                    <div class="links">
                        <ul>
                            <li><a href="index2.html" class="link">home</a></li>
                            <li><a href="gallery.php" class="link">gallery</a></li>
                            <li><a href="about.html" class="link">about us</a></li>
                            <li><a href="contact.php" class="link">contact</a></li>
                            <li><a href="promo.php" class="link">promo</a></li>
                            <li id="navProfileBtn"><a href="#" class="link">My Profile</a></li>
                            <li id="navReservationBtn"><a href="reservation.php" class="link">Reservation</a></li>
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
                    $stmt = $connection->prepare("SELECT id, email, profile_image FROM guests WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $guest = $result->fetch_assoc();
                    $stmt->close();

                    if (!empty($guest['profile_image']) && $guest['profile_image'] !== 'NULL') {
                        $profileImage = "uploads/" . htmlspecialchars($guest['profile_image']);
                    } else {
                        $profileImage = "uploads/default.webp";
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
                            <li><button class="btn" id="changeFullNameBtn" data-old-name="<?php echo $fullname; ?>"><i class="fas fa-user-edit"></i> Change Full Name</button></li>
                            <li><button class="btn" id="changeUserNameBtn" data-old-user="<?php echo $username; ?>"><i class="fas fa-user"></i> Change Username</button></li>
                            <li><button class="btn" id="changeEmailBtn" data-old-email="<?php echo $email; ?>"><i class="fas fa-envelope"></i> Change Email</button></li>
                            <li><button class="btn" id="changePhoneBtn" data-old-phone="<?php echo $phone; ?>"><i class="fas fa-phone"></i> Change Phone</button></li>
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
                    <li><a href="index2.html">Home</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="about.html">About us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="promo.php">Promo</a></li>
                    <li id="footerProfileBtn"><a href="#">My Profile</a></li>
                    <li id="footerReservationBtn"><a href="reservation.php">Reservation</a></li>
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
            <p>© 2025 <a href="index2.html"><span><b>CoffeeCups.</b></span></a> All Rights Reserved.</p>
            <p>Website By:
                <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank">
                    <span><b>Ahmed Mohamed.</b></span>
                </a>
            </p>
        </div>
    </footer>

    <!-- Linked external JavaScript -->
    <script src="java script/account.js"></script>
</body>


</html>