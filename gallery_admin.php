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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/gallery.css">
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
    <!-- Preloader -->
    <div id="preloader">
        <div class="dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="bg_gallery">
        <div class="gallery">
            <!-- nav bar -->
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
                                <li><a href="gallery_admin.php">Gallery</a></li>
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

            <!-- products -->
            <section class="products">
                <div class="texts">
                    <h1>Our Coffee Collection</h1>
                    <p>Discover our selection of premium coffees, carefully roasted to perfection.<br>
                        From rich espresso blends to smooth, aromatic brews,<br>
                        every cup is crafted to delight your senses.
                    </p>
                </div>

                <div class="items">
                    <?php
                    $sql = "SELECT * FROM products ORDER BY id ASC";
                    $result = $connection->query($sql);

                    if ($result->num_rows > 0) {
                        $count = 0; // عدّاد لتحديد أول 3 منتجات
                        while ($row = $result->fetch_assoc()) {
                            $count++;
                            // إضافة كلاس more-item من المنتج الرابع فما فوق
                            $itemClass = $count > 3 ? 'item more-item' : 'item';
                    ?>
                            <div class="<?php echo $itemClass; ?>">
                                <img src="products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <br>
                                <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                <p class="price" data-unit-price="<?php echo $row['price']; ?>">
                                    $<?php echo number_format($row['price'], 2); ?>
                                </p>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p>No products found.</p>";
                    }
                    ?>
                </div>

                <!-- Show More -->
                <div class="btn-container">
                    <button id="toggleBtn">▼ Show More</button>
                </div>
            </section>

            <!-- ===== Intro Before Login ===== -->
            <section id="intro" class="intro">
                <div class="intro-content">
                    <h1>Welcome to <span>CoffeeCups ☕</span></h1>
                    <p class="intro-text">
                        Please log in to <b>place your order</b> or <b>access our exclusive products</b>.
                    </p>
                    <p class="login-text">
                        If you want to log in, <a href="login.html">click here</a>.
                    </p>
                </div>
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
                <p>Website By : <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank"><span><b>Ahmed
                                Mohamed.</b></span></a></p>
            </div>
        </footer>
    </div>
    <!-- Linked external JavaScript file named gallery_admin.js -->
    <script src="java script/gallery_admin.js"></script>
</body>

</html>