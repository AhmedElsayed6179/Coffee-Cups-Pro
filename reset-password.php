<?php
session_start();

// الاتصال بقاعدة البيانات
$mysqli = require __DIR__ . "/config.php";

$token = $_GET["token"] ?? null;
$username = "User";

if ($token) {
    $token_hash = hash("sha256", $token);
    $stmt = $mysqli->prepare("SELECT username FROM guests WHERE reset_token_hash = ?");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['username'] = $user['username'];
        $username = $user['username'];
    }
}

// التعامل مع POST عبر Ajax
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");

    $token = $_POST["token"] ?? null;
    $password = $_POST["password"] ?? '';
    $password_confirmation = $_POST["password_confirmation"] ?? '';

    if (!$token) {
        echo json_encode(["status" => "error", "message" => "Invalid token."]);
        exit;
    }

    $token_hash = hash("sha256", $token);
    $stmt = $mysqli->prepare("SELECT * FROM guests WHERE reset_token_hash = ?");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
        exit;
    }

    if (strtotime($user["reset_token_expires_at"]) <= time()) {
        echo json_encode(["status" => "error", "message" => "Token has expired."]);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters."]);
        exit;
    }

    if (!preg_match("/[a-z]/i", $password)) {
        echo json_encode(["status" => "error", "message" => "Password must contain at least one letter."]);
        exit;
    }

    if (!preg_match("/[0-9]/", $password)) {
        echo json_encode(["status" => "error", "message" => "Password must contain at least one number."]);
        exit;
    }

    if (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        echo json_encode(["status" => "error", "message" => "Password must contain at least one special character (e.g., #, @, $)."]);
        exit;
    }

    if (!preg_match("/[A-Z]/", $password)) {
        echo json_encode(["status" => "error", "message" => "Password must contain at least one uppercase letter."]);
        exit;
    }

    if (preg_match("/[\p{Arabic}]/u", $password)) {
        echo json_encode(["status" => "error", "message" => "Password cannot contain Arabic letters."]);
        exit;
    }

    if ($password !== $password_confirmation) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
        exit;
    }

    if (password_verify($password, $user["password"])) {
        echo json_encode(["status" => "error", "message" => "Your new password cannot be the same as the old one."]);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $update = $mysqli->prepare("UPDATE guests
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL 
        WHERE id = ?");
    $update->bind_param("ss", $password_hash, $user["id"]);
    $update->execute();

    echo json_encode(["status" => "success", "message" => "Your password has been successfully reset."]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Coffee Cups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="icons/coffee-cup.png" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at 20% 20%, #667eea 0%, #764ba2 50%, #1e1f3f 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .dots {
            display: flex;
            gap: 10px;
        }

        .dots span {
            width: 15px;
            height: 15px;
            background: var(--mainColor);
            border-radius: 50%;
            animation: bounce 0.6s infinite alternate;
        }

        .dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(-20px);
            }
        }

        .container {
            position: relative;
            z-index: 1;
            background: rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            text-align: center;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            border-bottom: 3px solid #fff;
            display: inline-block;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .welcome-text {
            font-size: 18px;
            font-weight: 500;
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: color 0.3s ease;
        }

        .welcome-text strong {
            color: #ffdd57;
            font-weight: 600;
        }

        .welcome-text:hover {
            color: #f0f0f0;
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group input {
            width: 100%;
            padding: 14px 50px 14px 15px;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            transition: 0.3s ease;
        }

        .input-group input:focus {
            background: #fff;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.6);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #555;
            cursor: pointer;
            font-size: 18px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #5D3A1A, #A0522D);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
            transform: translateY(-3px);
        }

        /* ================== Background Effects ================== */
        .bg-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }

        .bg-gradient {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                    rgba(44, 27, 19, 0.95) 0%,
                    rgba(26, 17, 11, 0.97) 50%,
                    rgba(60, 35, 23, 0.9) 100%);
        }

        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.12;
            background-image:
                radial-gradient(circle at 20% 30%, #C89F74 0%, transparent 20%),
                radial-gradient(circle at 70% 70%, #8B5E3C 0%, transparent 20%);
            background-size: 280px 280px;
            animation: bgMove 40s linear infinite;
        }

        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.15;
        }

        .bg-shape-1 {
            width: 450px;
            height: 450px;
            background: #D4A373;
            top: -100px;
            left: -100px;
            animation: float 16s ease-in-out infinite;
        }

        .bg-shape-2 {
            width: 400px;
            height: 400px;
            background: #A47148;
            bottom: -150px;
            right: -100px;
            animation: float 20s ease-in-out infinite reverse;
        }

        .bg-shape-3 {
            width: 300px;
            height: 300px;
            background: #5C3D2E;
            top: 40%;
            right: 20%;
            animation: float 14s ease-in-out infinite 2s;
        }

        /* حركة النقوش */
        @keyframes bgMove {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 280px 280px;
            }
        }

        /* حركة ناعمة للأشكال */
        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            25% {
                transform: translate(40px, 40px);
            }

            50% {
                transform: translate(0, 80px);
            }

            75% {
                transform: translate(-40px, 40px);
            }
        }

        /* ================== Particle Layer ================== */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
        }

        /* ===== Mobile Responsive ===== */
        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
                overflow: hidden;
            }

            .container {
                padding: 30px 20px;
                width: 100%;
                max-width: 350px;
                border-radius: 15px;
                max-width: 100%;
                box-sizing: border-box;
            }

            h1 {
                font-size: 24px;
            }

            .welcome-text {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .input-group input {
                padding: 12px 45px 12px 12px;
                font-size: 14px;
            }

            .toggle-password {
                font-size: 16px;
                right: 10px;
            }

            button {
                padding: 12px;
                font-size: 15px;
            }
        }

        /* ===== Tablet Responsive ===== */
        @media screen and (max-width: 768px) and (min-width: 481px) {
            .container {
                padding: 40px 30px;
                width: 100%;
                max-width: 400px;
            }

            h1 {
                font-size: 26px;
            }

            .welcome-text {
                font-size: 17px;
                margin-bottom: 18px;
            }

            .input-group input {
                padding: 13px 48px 13px 12px;
                font-size: 15px;
            }

            button {
                padding: 13px;
                font-size: 16px;
            }
        }
    </style>
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
    <!-- Particles background -->
    <div id="particles-js" class="particles"></div>

    <!-- Background Wrapper: contains all background layers -->
    <div class="bg-wrapper">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
        <div class="bg-shapes">
            <div class="bg-shape bg-shape-1"></div>
            <div class="bg-shape bg-shape-2"></div>
            <div class="bg-shape bg-shape-3"></div>
        </div>
    </div>
    <div class="container">
        <h1>Reset Password</h1>
        <p class="welcome-text">Hello, <strong><?= htmlspecialchars($username) ?></strong>👋<br>Welcome back to your account</p>
        <form id="resetForm">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
            <div class="input-group">
                <input type="password" id="newPassword" name="password" placeholder="Enter new password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('newPassword', this)"></i>
            </div>
            <div class="input-group">
                <input type="password" id="confirmPassword" name="password_confirmation" placeholder="Confirm password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
            </div>
            <button type="submit">Update Password</button>
        </form>
    </div>

    <script>
        // ===================== TOGGLE PASSWORD VISIBILITY =====================
        function togglePassword(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                el.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        // ===================== SAFE FETCH (InfinityFree Compatible) =====================
        async function safeFetch(url, options, retries = 3) {
            for (let i = 0; i < retries; i++) {
                const response = await fetch(url, options);
                const text = await response.text();

                // ⚠️ لو InfinityFree رجّع صفحة حماية HTML
                if (text.includes("/aes.js") || text.startsWith("<html")) {
                    console.warn("InfinityFree security page detected. Retrying...");
                    await new Promise((res) => setTimeout(res, 5000)); // انتظر 5 ثواني
                    continue; // حاول تاني
                }

                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON response:", text);
                    throw new Error("Unexpected server response");
                }
            }
            throw new Error("Failed after multiple attempts due to InfinityFree security filter.");
        }

        // ===================== FORM HANDLER =====================
        document.getElementById("resetForm").addEventListener("submit", async (e) => {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            Swal.fire({
                title: "Updating...",
                text: "Please wait a moment",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const data = await safeFetch("", { // نفس الصفحة
                    method: "POST",
                    body: formData,
                });

                Swal.close();

                if (data.status === "error") {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message,
                        confirmButtonColor: "#667eea",
                    });
                } else {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: data.message,
                        confirmButtonText: "Go to Login",
                        confirmButtonColor: "#667eea",
                    }).then(() => {
                        window.location.href = "login.html";
                    });
                }
            } catch (err) {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong. Please try again.",
                    confirmButtonColor: "#A47148",
                });
            }
        });

        // ===================== PRELOADER =====================
        window.addEventListener("load", () => {
            setTimeout(() => {
                const preloader = document.getElementById("preloader");
                preloader.style.opacity = "0";
                preloader.style.transition = "opacity 0.5s ease";

                setTimeout(() => {
                    preloader.style.display = "none";
                }, 500);
            }, 500);
        });

        // ===================== PARTICLES.JS INITIALIZATION =====================
        window.onload = function() {
            if (window.particlesJS) {
                particlesJS("particles-js", {
                    particles: {
                        number: {
                            value: 45,
                            density: {
                                enable: true,
                                value_area: 800
                            }
                        },
                        color: {
                            value: "#F0F4F8"
                        },
                        shape: {
                            type: "circle",
                            stroke: {
                                width: 0,
                                color: "#000"
                            },
                            polygon: {
                                nb_sides: 5
                            },
                        },
                        opacity: {
                            value: 0.8
                        },
                        size: {
                            value: 10,
                            random: true
                        },
                        line_linked: {
                            enable: true,
                            distance: 220,
                            color: "#F0F4F8",
                            opacity: 0.4,
                            width: 1.8,
                        },
                        move: {
                            enable: true,
                            speed: 2,
                            direction: "none",
                            out_mode: "out"
                        },
                    },
                    interactivity: {
                        detect_on: "canvas",
                        events: {
                            onhover: {
                                enable: true,
                                mode: "grab"
                            },
                            onclick: {
                                enable: true,
                                mode: "push"
                            },
                            resize: true,
                        },
                        modes: {
                            grab: {
                                distance: 200,
                                line_linked: {
                                    opacity: 1
                                }
                            },
                            push: {
                                particles_nb: 4
                            },
                        },
                    },
                    retina_detect: true,
                });
            }
        };
    </script>
    <!-- AOS Library for Scroll Animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Particles.js Library for Background Particle Effects -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <!-- Include SweetAlert2 library for modern alert popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>