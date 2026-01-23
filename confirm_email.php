<?php
session_start();
require 'config.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    $error_message = "Invalid token.";
} else {
    // جلب الرمز من قاعدة البيانات
    $stmt = $connection->prepare("
        SELECT username, new_email, expiry 
        FROM email_verification 
        WHERE token=? LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $error_message = "Token invalid.";
    } elseif (strtotime($row['expiry']) < time()) {
        $error_message = "Token expired.";
    } else {
        // تحديث البريد الجديد في جدول المستخدمين
        $stmt = $connection->prepare("UPDATE guests SET email=? WHERE username=?");
        $stmt->bind_param("ss", $row['new_email'], $row['username']);
        $stmt->execute();
        $stmt->close();

        // حذف الرمز بعد التأكيد
        $stmt = $connection->prepare("DELETE FROM email_verification WHERE token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        $success_message = "Your email has been successfully updated!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Confirmation | Coffee Cups</title>
    <link rel="shortcut icon" href="icons/coffee-cup.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        /* Preloader */
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
            background: #795548;
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

        /* Particles & Background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
        }

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
            background: linear-gradient(135deg, rgba(44, 27, 19, 0.95) 0%, rgba(26, 17, 11, 0.97) 50%, rgba(60, 35, 23, 0.9) 100%);
        }

        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.12;
            background-image: radial-gradient(circle at 20% 30%, #C89F74 0%, transparent 20%), radial-gradient(circle at 70% 70%, #8B5E3C 0%, transparent 20%);
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

        @keyframes bgMove {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 280px 280px;
            }
        }

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
    </style>
</head>

<body>
    <div id="preloader">
        <div class="dots"><span></span><span></span><span></span></div>
    </div>
    <div id="particles-js" class="particles"></div>
    <div class="bg-wrapper">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
        <div class="bg-shapes">
            <div class="bg-shape bg-shape-1"></div>
            <div class="bg-shape bg-shape-2"></div>
            <div class="bg-shape bg-shape-3"></div>
        </div>
    </div>

    <script>
        window.addEventListener("load", () => {
            setTimeout(() => {
                const e = document.getElementById("preloader");
                e.style.opacity = "0";
                e.style.transition = "opacity 0.5s ease";
                setTimeout(() => {
                    e.style.display = "none"
                }, 500)
            }, 500)
        });

        // Particles.js Init
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
                        }
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
                        width: 1.8
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: "none",
                        out_mode: "out"
                    }
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
                        resize: true
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
                        }
                    }
                },
                retina_detect: true
            });
        }

        // SweetAlert2 Messages
        <?php if (!empty($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= $error_message ?>',
                background: '#4e342e',
                color: '#fbe9e7',
                confirmButtonColor: '#6d4c41'
            });
        <?php elseif (!empty($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Verified! ☕',
                html: '<div style="color:#fbe9e7;"><?= $success_message ?></div><br><button id="loginBtn" class="swal2-confirm swal2-styled" style="background-color:#795548;color:#fff;border-radius:8px;padding:8px 20px;">Login</button>',
                background: '#3e2723',
                showConfirmButton: false
            }).then(() => {
                const btn = document.getElementById('loginBtn');
                if (btn) {
                    btn.addEventListener('click', () => {
                        window.location.href = 'login.html'
                    })
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>