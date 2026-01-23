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
                                <button class="buy-btn">Buy Now</button>
                                <div class="quantity-wrapper">
                                    <button type="button" class="qty-btn" data-action="decrease">-</button>
                                    <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input" readonly>
                                    <button type="button" class="qty-btn" data-action="increase">+</button>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p>No products found.</p>";
                    }
                    ?>
                </div>

                <!-- Coming Soon -->
                <div class="coming-soon" style="display: none;">
                    <p>More products coming soon… Stay tuned!</p>
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

        <!-- ================== CART + RESERVATION WRAPPER ================== -->
        <section id="user-sections" class="hidden">
            <div class="cart-reservation-container">
                <!-- ================== CART SECTION ================== -->
                <section id="cart-section" class="cart">
                    <h2>Your Shopping Cart 🛒</h2>
                    <div id="cart-items">
                        <p class="empty-cart">Your cart is empty.</p>
                    </div>
                    <div id="cart-total" class="cart-total">🧾 Total: $0.00</div>

                    <!-- Payment Options -->
                    <div class="payment-options">
                        <h3>Choose Payment Method</h3>
                        <div class="payment-buttons">
                            <button class="payment-btn visa-btn">
                                <i class="fa-solid fa-credit-card"></i> Pay with Visa
                            </button>
                            <button class="payment-btn store-btn">
                                <i class="fa-solid fa-store"></i> Pay at Store
                            </button>
                        </div>
                    </div>
                </section>
                <section id="cart-section" class="cart">
                    <h2>Share Your Feedback ☕</h2>

                    <form action="send-review.php" method="post" id="feedbackForm">
                        <label for="type">Select Feedback Type:</label>
                        <select name="type" id="type" required>
                            <option value="">-- Choose Type --</option>
                            <option value="service">Service</option>
                            <option value="payment">Payment</option>
                            <option value="products">Products</option>
                        </select>

                        <!-- Product list -->
                        <div id="product-options" class="hidden">
                            <p>Select Products You Tried:</p>
                            <div class="product-list">
                                <label><input type="checkbox" name="products[]" value="Espresso"> Espresso</label>
                                <label><input type="checkbox" name="products[]" value="Cappuccino"> Cappuccino</label>
                                <label><input type="checkbox" name="products[]" value="Latte"> Latte</label>
                                <label><input type="checkbox" name="products[]" value="Mocha"> Mocha</label>
                                <label><input type="checkbox" name="products[]" value="Americano"> Americano</label>
                                <label><input type="checkbox" name="products[]" value="Macchiato"> Macchiato</label>
                                <label><input type="checkbox" name="products[]" value="Flat White"> Flat White</label>
                                <label><input type="checkbox" name="products[]" value="Caramel Latte"> Caramel Latte</label>
                                <label><input type="checkbox" name="products[]" value="Iced Coffee"> Iced Coffee</label>
                            </div>
                        </div>

                        <!-- 5-star rating -->
                        <div class="rating">
                            <p>Rate your experience:</p>
                            <div class="stars">
                                <input type="radio" name="rating" id="star5" value="5"><label for="star5">★</label>
                                <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                                <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                                <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                                <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                            </div>
                        </div>

                        <label for="name">Your Name:</label>
                        <input type="text" style="background-color: #f0f0f0;cursor: not-allowed;" value="<?php echo $fullname; ?>" id="name" name="name" readonly>

                        <label for="email">Your Email:</label>
                        <input type="email" style="background-color: #f0f0f0;cursor: not-allowed;" id="email" value="<?php echo $email; ?>" name="email" readonly>

                        <label for="message">Your Feedback:</label>
                        <textarea id="message" name="message" rows="4" required></textarea>

                        <button type="submit" id="sendBtn">Send</button>
                    </form>
                </section>
            </div>
        </section>

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
    </div>
    <!-- 🟢 نرسل الاسم من PHP إلى جافا سكربت -->
    <script>
        const phpFullName = "<?php echo $fullname; ?>";
        const php_phone = "<?php echo $phone; ?>";
    </script>
    <!-- Linked external JavaScript file named gallery.js -->
    <script src="java script/gallery.js"></script>
</body>

</html>