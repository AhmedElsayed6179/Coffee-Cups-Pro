<?php
session_start();
require_once 'config.php';

// تحقق من تسجيل الدخول
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $connection->prepare("SELECT full_name, email, phone FROM guests WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $fullname = htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8');
    } else {
        $fullname = '';
        $email = '';
    }
    $stmt->close();
} else {
    $fullname = '';
    $email = '';
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/payment.css">
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

    <div class="payment">
        <!-- nav bar -->
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
                            <li><a href="promo.html" class="link">Promo</a></li>
                            <li id="navProfileBtn"><a href="account.php" class="link">My Profile</a></li>
                            <li id="navReservationBtn"><a href="reservation.php" class="link">Reservation</a></li>
                            <li><a href="login.html" class="link_login">login</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>

        <!-- ================== PAYMENT BOX ================== -->
        <section id="visa-payment" class="visa-payment container">
            <div class="payment-grid">
                <!-- Left: Order summary -->
                <aside class="order-summary">
                    <h3>Your Order</h3>
                    <div id="summary-items" class="summary-items">
                        <!-- عناصر السلة تُضاف ديناميكيا بواسطة JS -->
                        <p class="empty">No items in cart.</p>
                    </div>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong id="summary-total">$0.00</strong>
                    </div>
                </aside>

                <!-- Right: Visa form -->
                <div class="payment-card">
                    <h3>Pay with Visa</h3>

                    <form id="visaForm" novalidate autocomplete="off">
                        <div class="row">
                            <div class="col">
                                <label for="account_name">Account Name</label>
                                <input style="background-color: #f0f0f0;cursor: not-allowed;" id="account_name" name="account_name" value="<?php echo $fullname; ?>" type="text" readonly>
                            </div>
                            <div class="col">
                                <label for="cvv">Phone</label>
                                <input style="background-color: #f0f0f0;cursor: not-allowed;" value="<?php echo $phone; ?>" id="account_phone" name="account_phone" type="text" readonly>
                            </div>
                        </div>
                        <label for="cardName">Cardholder Name</label>
                        <input id="cardName" name="cardName" type="text" placeholder="Full name as on card"
                            maxlength="50" required>

                        <label for="cardNumber">Card Number</label>
                        <input id="cardNumber" name="cardNumber" inputmode="numeric" type="text"
                            placeholder="4242 4242 4242 4242" maxlength="19" required>

                        <div class="row">
                            <div class="col">
                                <label for="expiry">Expiry (MM/YY)</label>
                                <input id="expiry" name="expiry" type="text" inputmode="numeric" placeholder="MM/YY"
                                    maxlength="5" required>
                            </div>
                            <div class="col">
                                <label for="cvv">CVV</label>
                                <input id="cvv" name="cvv" type="password" inputmode="numeric" placeholder="123"
                                    maxlength="4" required>
                            </div>
                        </div>

                        <label for="billingEmail">Email (receipt)</label>
                        <input style="background-color: #f0f0f0;cursor: not-allowed;" id="billingEmail" name="billingEmail" type="email" value="<?php echo $email; ?>" readonly>

                        <div class="actions">
                            <button type="submit" class="payment-btn visa-submit">
                                <i class="fa-solid fa-credit-card"></i> Pay Now
                            </button>
                            <button type="button" id="cancelPayment" class="payment-btn cancel-btn">Cancel</button>
                        </div>

                        <p class="secure-note">Secure payment — this is a simulated checkout. Integrate a real gateway
                            for production.</p>
                    </form>
                </div>
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
                    <li><a href="promo.html">Promo</a></li>
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
    <!-- Linked external JavaScript file named payment.js -->
    <script src="java script/payment.js"></script>
</body>

</html>