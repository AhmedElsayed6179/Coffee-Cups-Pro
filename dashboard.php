<?php
session_start();
// الاتصال بقاعدة البيانات
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>
            alert('🚫 Access denied! Please log in first.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

// إضافة منتج جديد
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // تحميل الصورة
    $image = $_FILES['image']['name'];
    $target = "products/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $connection->query("INSERT INTO products (name, description, price, image) 
                        VALUES ('$name', '$description', '$price', '$image')");
    echo "<script>alert('Product added successfully ✅');</script>";
}

// حذف منتج
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $connection->query("DELETE FROM products WHERE id=$id");
    echo "<script>alert('Product deleted 🗑️'); window.location='dashboard.php';</script>";
}

// تعديل منتج
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "products/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $connection->query("UPDATE products SET name='$name', description='$description', price='$price', image='$image' WHERE id=$id");
    } else {
        $connection->query("UPDATE products SET name='$name', description='$description', price='$price' WHERE id=$id");
    }

    echo "<script>alert('Product updated successfully ✏️'); window.location='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Coffee Cups</title>
    <!-- Main Template CSS File -->
    <link rel="stylesheet" href="css/dashboard.css">
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
        <div class="dashboard_bg">
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
            <!-- dashboard -->
            <section class="dashboard">
                <div class="dashboard-container">
                    <h1 class="dashboard-title">Admin Dashboard ☕</h1>
                    
                    <!-- Add Product Box -->
                    <div class="add-product-box">
                        <h3 class="section-title">➕ Add New Product</h3>
                        <form method="POST" enctype="multipart/form-data" class="product-form">
                            <input type="text" name="name" class="input-field" placeholder="Product name" required>
                            <textarea name="description" class="input-field textarea-field" placeholder="Product description" required></textarea>
                            <input type="number" step="0.01" name="price" class="input-field" placeholder="Price ($)" required>
                            <input type="file" name="image" class="file-input" required>
                            <button type="submit" name="add_product" class="btn add-btn">Add Product</button>
                        </form>
                    </div>

                    <!-- Products Table -->
                    <div class="table-box">
                        <h3 class="section-title">Products List 📦</h3>
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price ($)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $connection->query("SELECT * FROM products ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()) {
                                    echo "
                        <tr>
                            <td>{$row['id']}</td>
                            <td><img src='products/{$row['image']}' alt='{$row['name']}' class='product-img'></td>
                            <td>{$row['name']}</td>
                            <td>{$row['description']}</td>
                            <td>\${$row['price']}</td>
                            <td>
                                <a href='?edit={$row['id']}' class='btn-table edit-btn'>✏️ Edit</a>
                                <a href='?delete={$row['id']}' class='btn-table delete-btn' onclick='return confirm(\"Are you sure?\")'>🗑️ Delete</a>
                            </td>
                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Edit Product Box -->
                    <?php
                    if (isset($_GET['edit'])) {
                        $id = $_GET['edit'];
                        $product = $connection->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
                    ?>
                        <div class="edit-box">
                            <h3 class="section-title">Edit Product (ID: <?php echo $id; ?>) ✏️</h3>
                            <form method="POST" enctype="multipart/form-data" class="product-form">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="text" name="name" value="<?php echo $product['name']; ?>" class="input-field" required>
                                <textarea name="description" class="input-field textarea-field" required><?php echo $product['description']; ?></textarea>
                                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" class="input-field" required>
                                <p>Current Image:</p>
                                <img src="products/<?php echo $product['image']; ?>" class="edit-img"><br><br>
                                <input type="file" name="image" class="file-input">
                                <div style="display: flex; gap: 10px; margin-top: 10px;">
                                    <button type="submit" name="update_product" class="btn update-btn">Update Product</button>
                                    <a href="dashboard.php" class="btn" style="background-color: #888;text-decoration: none;">Cancel</a>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
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
                <p>© 2025 <a href="home_admin.html"><span><b>CoffeeCups.</b></span></a> All Rights Reserved.</p>
                <p>Website By : <a href="https://ahmedelsayed6179.github.io/Ahmed-Websites/" target="_blank"><span><b>Ahmed
                                Mohamed.</b></span></a></p>
            </div>
        </footer>
        <!-- Linked external JavaScript file named dashboard.js -->
   <script src="java script/dashboard.js"></script>
    </body>

</html>