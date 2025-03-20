<?php

include 'components/connect.php';
include 'components/sendMail.php';

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

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Email không hợp lệ!';
    } else {
        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
        $select_user->execute([$email]);

        if ($select_user->rowCount() > 0) {
            $row = $select_user->fetch(PDO::FETCH_ASSOC);

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Update user with reset token
            $update_user = $conn->prepare("UPDATE `users` SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
            $update_user->execute([$reset_token, $reset_token_expires, $email]);

            // Send reset password email
            $mail_result = sendPasswordResetEmail($email, $reset_token);
            if ($mail_result === true) {
                $message[] = 'Link đặt lại mật khẩu đã được gửi đến email của bạn!';
            } else {
                $message[] = 'Không thể gửi email! Vui lòng thử lại sau.';
                $message[] = $mail_result; // Show the error message from PHPMailer
            }
        } else {
            $message[] = 'Email không tồn tại trong hệ thống!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Forgot Password</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- header section starts  -->
    <?php include 'components/user_header.php'; ?>
    <!-- header section ends -->

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
            <h3>Quên mật khẩu</h3>
            <input type="email" name="email" required placeholder="Nhập email của bạn" class="box" maxlength="50"
                oninput="this.value = this.value.replace(/\s/g, '')" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Vui lòng nhập email hợp lệ">
            <input type="submit" value="Submit" name="submit" class="btn">
            <p>Đã nhớ mật khẩu? <a href="login.php">Đăng nhập</a></p>
        </form>

    </section>

    <?php include 'components/footer.php'; ?>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>