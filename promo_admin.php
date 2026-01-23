<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/promo.css">
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

    <div class="promo">
        <!-- nav bar -->
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

        <?php
        require_once 'config.php';

        // جلب أحدث محتوى فيديو
        $query = "SELECT * FROM video_content ORDER BY created_at DESC LIMIT 1";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            $content = $result->fetch_assoc();
            $videoPath = $content['file_path'];
            $videoTitle = $content['title'];
            $videoDesc  = $content['description'];
        } else {
            $videoPath = '';
            $videoTitle = 'No content available';
            $videoDesc  = '';
        }
        ?>

        <!-- Video Section -->
        <section class="video-section">
            <div class="video-text-box">
                <h1><?php echo htmlspecialchars($videoTitle); ?></h1>
                <p><?php echo htmlspecialchars($videoDesc); ?></p>
            </div>

            <div class="video-container">
                <?php if ($videoPath): ?>
                    <video id="coffeeVideo" src="<?php echo htmlspecialchars($videoPath); ?>" controlsList="nodownload" controls></video>
                <?php else: ?>
                    <p>No content available</p>
                <?php endif; ?>
            </div>

            <!-- 🎛️ عناصر التحكم -->
            <div class="video-controls">
                <button id="playToggle" class="control-btn">
                    <i class="fa-solid fa-play"></i>
                </button>

                <div class="volume-group">
                    <button id="volumeToggle" class="control-btn">
                        <i class="fa-solid fa-volume-high"></i>
                    </button>
                    <input type="range" id="volumeControl" min="0" max="1" step="0.05" value="0.5">
                </div>

                <button id="fullscreenToggle" class="control-btn">
                    <i class="fa-solid fa-expand"></i>
                </button>
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
</body>

</html>