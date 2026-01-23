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
        $fullname = htmlspecialchars($user['full_name'], ENT_QUOTES);
        $email = htmlspecialchars($user['email'], ENT_QUOTES);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/contact.css">
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
    <div class="location">
        <!-- Navbar -->
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
                        <li><a href="contact.php" class="link">Contact</a></li>
                        <li><a href="promo.php" class="link">Promo</a></li>
                        <li id="navProfileBtn"><a href="account.php" class="link">My Profile</a></li>
                        <li id="navReservationBtn"><a href="reservation.php" class="link">Reservation</a></li>
                        <li><a href="login.html" class="link_login">login</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <div class="contact-container">
            <!-- Contact form -->
            <form class="contact-form" id="contactForm" action="send_message.php" method="post">
                <h2 style="font-weight: 600; line-height: 1.3;">
                    Leave a Message<br>
                    We'll get back to you shortly
                </h2>
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" placeholder="Your Name"
                    <?php if (isset($_SESSION['user_id'])): ?>
                    value="<?php echo $fullname; ?>"
                    style="background-color: #f0f0f0; cursor: not-allowed;"
                    readonly
                    <?php else: ?>
                    required
                    <?php endif; ?>>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Your Email"
                    <?php if (isset($_SESSION['user_id'])): ?>
                    value="<?php echo $email; ?>"
                    style="background-color: #f0f0f0; cursor: not-allowed;"
                    readonly
                    <?php else: ?>
                    required
                    <?php endif; ?>>

                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" placeholder="Your Subject" required>

                <label for="message">Message:</label>
                <textarea id="message" rows="5" name="message" placeholder="Your Message" required></textarea>

                <button type="submit">Send</button>
            </form>

            <!-- Google Map -->
            <div class="map-container">
                <div class="map-title">Our Location</div>
                <iframe class="map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d109186.33095686174!2d29.84417679729945!3d31.21871123560148!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14f5c56cb69278a1%3A0xa9f55cc3bef24c35!2sLuna%20Park%20Caf%C3%A9!5e0!3m2!1sen!2seg!4v1761171622107!5m2!1sen!2seg" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
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
                    <li><a href="index2.html">Home</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="about.html">about us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="promo.php">Promo</a></li>
                    <li id="footerProfileBtn"><a href="account.php">My Profile</a></li>
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
            <p>Website By : <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank"><span><b>Ahmed
                            Mohamed.</b></span></a></p>
        </div>
    </footer>
    <!-- Linked external JavaScript file named contact.js -->
    <script src="java script/contact.js"></script>
</body>

</html>