<?php
// login.php - secure admin + guest login (returns JSON)
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// DB config
require_once __DIR__ . '/config.php';

// Get and sanitize input
$login_value = isset($_POST['username']) ? trim($_POST['username']) : '';
$password_value = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($login_value === '' || $password_value === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
    $connection->close();
    exit;
}

// Helper to respond and close connection
function respond_and_exit($payload, $conn = null, $close = true) {
    if ($close && $conn) $conn->close();
    echo json_encode($payload);
    exit;
}

/* ============================
   1) Check admin table first
   ============================ */
try {
    $admin_stmt = $connection->prepare("SELECT id, username, email, password FROM admin WHERE username = ? OR email = ? LIMIT 1");
    if (!$admin_stmt) {
        respond_and_exit(['status' => 'error', 'message' => 'Server error (prepare admin)'], $connection);
    }

    $admin_stmt->bind_param('ss', $login_value, $login_value);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin = $admin_result->fetch_assoc()) {
        // Verify password
        if (password_verify($password_value, $admin['password'])) {
            // Regenerate session id on privilege change
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();

            // Update last login and last active
            $adm_update = $connection->prepare("UPDATE admin SET last_login = NOW(), last_active = NOW() WHERE id = ?");
            $adm_update->bind_param("i", $admin['id']);
            $adm_update->execute();
            $adm_update->close();

            respond_and_exit([
                'status' => 'admin',
                "username" => $admin['username'],
                'redirect' => 'dashboard.php'
            ], $connection);

        } else {
            // Incorrect password
            respond_and_exit(['status' => 'invalid', 'message' => 'Incorrect username/email or password.'], $connection);
        }
    }

    $admin_stmt->close();

} catch (Exception $e) {
    respond_and_exit(['status' => 'error', 'message' => 'Server error (admin lookup)'], $connection);
}

/* ============================
   2) Check guests table
   ============================ */
try {
    $user_stmt = $connection->prepare("SELECT id, username, email, password, verified FROM guests WHERE email = ? OR username = ? LIMIT 1");
    if (!$user_stmt) {
        respond_and_exit(['status' => 'error', 'message' => 'Server error (prepare user)'], $connection);
    }
    $user_stmt->bind_param('ss', $login_value, $login_value);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user = $user_result->fetch_assoc()) {
        // If account not verified -> generate token, update, resend activation email
        if ((int)$user['verified'] !== 1) {
            // generate token
            $token = bin2hex(random_bytes(16));
            $upd = $connection->prepare("UPDATE guests SET token = ? WHERE id = ?");
            if ($upd) {
                $upd->bind_param('si', $token, $user['id']);
                $upd->execute();
                $upd->close();
            }

            // send activation email (mailer.php must return configured PHPMailer instance)
            try {
                $mail = require __DIR__ . '/mailer.php';
                $mail->isHTML(true);
                $mail->setFrom('coffeecups@gmail.com', 'Coffee Cups');
                $mail->addAddress($user['email'], $user['username']);
                $mail->Subject = 'Confirm Your Account';
                // adjust verification URL to your domain
                $verifyUrl = "https://coffee-cups.page.gd/verify.php?token=" . urlencode($token);
                $mail->Body = <<<HTML
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #e5e7eb;">
  <div style="text-align: center;">
    <img src="cid:coffee-cup_cid" alt="Coffee Cups" style="width:120px;margin-bottom:20px;">
    <h2 style="color:#1e293b">Confirm Your Email</h2>
    <p>Welcome <strong>{$user['username']}</strong>! Please verify your email to activate your Coffee Cups account.</p>
    <a href="{$verifyUrl}" style="display:inline-block;padding:12px 28px;background:#22c55e;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Confirm Email</a>
    <p style="color:#64748b;font-size:14px;margin-top:20px;">If you didn't register, ignore this email. This link will expire soon.</p>
    <p style="color:#94a3b8;font-size:12px;">© 2025 Coffee Cups</p>
  </div>
</div>
HTML;
                // attempt to embed image if mailer configured for it
                if (file_exists(__DIR__ . '/icons/coffee-cup.png')) {
                    $mail->AddEmbeddedImage(__DIR__ . '/icons/coffee-cup.png', 'coffee-cup_cid');
                }
                $mail->send();
            } catch (Exception $e) {
                // ignore mail send errors but inform client
                respond_and_exit(['status' => 'unverified', 'message' => 'Account not verified. Failed to send confirmation email.'], $connection);
            }

            respond_and_exit(['status' => 'unverified', 'message' => 'Your account is not verified. A confirmation email has been resent.'], $connection);
        }

        // Verify password for regular user
        if (password_verify($password_value, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();

            // update last_login & last_active
            $upd2 = $connection->prepare("UPDATE guests SET last_login = NOW(), last_active = NOW() WHERE id = ?");
            if ($upd2) {
                $upd2->bind_param('i', $user['id']);
                $upd2->execute();
                $upd2->close();
            }

            respond_and_exit(['status' => 'success', 'username' => $user['username']], $connection);
        } else {
            respond_and_exit(['status' => 'invalid', 'message' => 'Incorrect username/email or password.'], $connection);
        }
    } else {
        // no user/admin found
        respond_and_exit(['status' => 'notfound', 'message' => 'No account found with this username or email.'], $connection);
    }

    $user_stmt->close();
} catch (Exception $e) {
    respond_and_exit(['status' => 'error', 'message' => 'Server error (user lookup)'], $connection);
}

// fallback close
$connection->close();
