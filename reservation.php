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
        $phone = htmlspecialchars($user['phone'], ENT_QUOTES);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/reservation.css">
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
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
    <!-- nav bar -->
    <div class="reservation">
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
                            <li><a href="contact.php" class="link">Contact</a></li>
                            <li><a href="promo.php" class="link">Promo</a></li>
                            <li id="navProfileBtn"><a href="account.php" class="link">My Profile</a></li>
                            <li id="navReservationBtn"><a href="reservation.php" class="link">Reservation</a></li>
                            <li><a href="login.html" class="link_login">login</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <section id="reservation-section" class="reservation">
            <form id="reservationForm" class="reservation-form">
                <h2>Reserve Your Table ☕</h2>
                <div class="form-group">
                    <label for="resName">Full Name</label>
                    <input style="background-color: #f0f0f0;cursor: not-allowed;" type="text" id="resName" value="<?php echo $fullname; ?>" name="resName" readonly />
                </div>

                <div class="form-group">
                    <label for="resEmail">Email</label>
                    <input style="background-color: #f0f0f0;cursor: not-allowed;" type="email" id="resEmail" value="<?php echo $email; ?>" name="resEmail" readonly />
                </div>

                <div class="form-group">
                    <label for="resPhone">Phone</label>
                    <input style="background-color: #f0f0f0;cursor: not-allowed;" value="<?php echo $phone; ?>" type="tel" id="resPhone" name="resPhone" maxlength="11" readonly />
                </div>

                <div class="form-group">
                    <label for="resDate">Date</label>
                    <input type="date" id="resDate" name="resDate" placeholder="Pick a date" required />
                </div>

                <div class="form-group">
                    <label for="resTime">Time</label>
                    <input type="time" id="resTime" name="resTime" placeholder="Select reservation time" required />
                </div>

                <div class="form-group">
                    <label for="resGuests">Guests</label>
                    <input type="number" id="resGuests" name="resGuests" placeholder="Choose number of guests (1-20)" min="1" max="20" required />
                </div>

                <button type="submit" class="reserve-btn">
                    <i class="fa-solid fa-calendar-check"></i> Confirm Reservation
                </button>
            </form>
        </section>

        <!-- ===== Intro Before Login ===== -->
        <section id="intro" class="intro">
            <div class="intro-content">
                <h1>Welcome to <span>CoffeeCups ☕</span></h1>
                <p class="intro-text">
                    Please log in to <b>reserve your table</b>.
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
    <!-- Linked external JavaScript file named reservation.js -->
    <script src="java script/reservation.js"></script>
</body>

</html>