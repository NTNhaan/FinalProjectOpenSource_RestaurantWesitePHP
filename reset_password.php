<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Initialize message array
$message = array();
$token_valid = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token = filter_var($token, FILTER_SANITIZE_STRING);

    // Check if token exists and is valid
    $check_token = $conn->prepare("SELECT * FROM `users` WHERE reset_token = ? AND reset_token_expires > ?");
    $current_time = date('Y-m-d H:i:s');
    $check_token->execute([$token, $current_time]);

    // Debug information
    error_log("Token from URL: " . $token);
    error_log("Current time: " . $current_time);
    error_log("Number of rows found: " . $check_token->rowCount());

    if ($check_token->rowCount() > 0) {
        $token_valid = true;
        error_log("Token is valid");
    } else {
        // Get the actual token and expiry from database for debugging
        $debug_query = $conn->prepare("SELECT reset_token, reset_token_expires FROM users WHERE reset_token = ?");
        $debug_query->execute([$token]);
        $debug_result = $debug_query->fetch(PDO::FETCH_ASSOC);
        error_log("Debug - Database token: " . ($debug_result ? $debug_result['reset_token'] : 'not found'));
        error_log("Debug - Token expires: " . ($debug_result ? $debug_result['reset_token_expires'] : 'not found'));

        $message[] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
        error_log("Token is invalid or expired");
    }
} else {
    header('location:login.php');
    exit();
}

if (isset($_POST['submit'])) {
    $token = $_POST['token'];
    $token = filter_var($token, FILTER_SANITIZE_STRING);
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Validate password strength
    if (strlen($new_pass) < 8) {
        $message[] = 'Mật khẩu phải có ít nhất 8 ký tự!';
    } elseif (!preg_match("#[0-9]+#", $new_pass)) {
        $message[] = 'Mật khẩu phải chứa ít nhất 1 số!';
    } elseif (!preg_match("#[A-Z]+#", $new_pass)) {
        $message[] = 'Mật khẩu phải chứa ít nhất 1 chữ hoa!';
    } elseif ($new_pass !== $confirm_pass) {
        $message[] = 'Xác nhận mật khẩu không khớp!';
    } else {
        // Verify token is still valid before updating password
        $check_token = $conn->prepare("SELECT * FROM `users` WHERE reset_token = ? AND reset_token_expires > ?");
        $current_time = date('Y-m-d H:i:s');
        $check_token->execute([$token, $current_time]);

        if ($check_token->rowCount() > 0) {
            $new_pass = sha1($new_pass);

            // Update password and clear reset token
            $update_pass = $conn->prepare("UPDATE `users` SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
            $update_pass->execute([$new_pass, $token]);

            $message[] = 'Cập nhật mật khẩu thành công! Đang chuyển hướng đến trang đăng nhập...';
            header('refresh:2;url=login.php');
            exit();
        } else {
            $message[] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
            header('refresh:2;url=login.php');
            exit();
        }
    }
}

// Only redirect if token is invalid and we're not showing an error message
if (!$token_valid && empty($message)) {
    error_log("Redirecting to login - Token invalid and no messages to show");
    header('location:login.php');
    exit();
}

// Debug information before showing form
error_log("Showing reset password form. Token valid: " . ($token_valid ? 'true' : 'false'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Đặt lại mật khẩu</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <!-- messages section starts -->
    <?php
    if (isset($message) && is_array($message)) {
        foreach ($message as $msg) {
            echo '
      <div class="message">
         <span>' . $msg . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
        }
    }
    ?>
    <!-- messages section ends -->

    <section class="form-container">
        <form action="" method="POST">
            <h3>Đặt lại mật khẩu</h3>
            <input type="hidden" name="token" value="<?= $token; ?>">
            <input type="password" name="new_pass" required placeholder="Nhập mật khẩu mới" class="box" maxlength="50"
                oninput="this.value = this.value.replace(/\s/g, '')" pattern="(?=.*\d)(?=.*[A-Z]).{8,}"
                title="Mật khẩu phải có ít nhất 8 ký tự, 1 chữ hoa và 1 số">
            <input type="password" name="confirm_pass" required placeholder="Xác nhận mật khẩu mới" class="box"
                maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Cập nhật mật khẩu" name="submit" class="btn">
        </form>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="js/script.js"></script>

</body>

</html>