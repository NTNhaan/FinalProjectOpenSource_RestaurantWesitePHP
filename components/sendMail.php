<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Include loadEnv function
include 'loadEnv.php';

// Function to send email
function sendMail($recipient_email, $recipient_name, $subject, $body)
{
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($recipient_email, $recipient_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to generate password reset email
function sendPasswordResetEmail($email, $reset_token)
{
    $subject = "Đặt lại mật khẩu - PizzaHut";
    $reset_link = "http://localhost/FinalProject_PHP/reset_password.php?token=" . $reset_token;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e31837;'>Đặt lại mật khẩu PizzaHut</h2>
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
        <p>Vui lòng click vào link bên dưới để đặt lại mật khẩu:</p>
        <p><a href='{$reset_link}' style='background-color: #e31837; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đặt lại mật khẩu</a></p>
        <p>Link này sẽ hết hạn sau 1 giờ.</p>
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        <hr>
        <p style='font-size: 12px; color: #666;'>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>";

    return sendMail($email, "", $subject, $body);
}

// Function to send order confirmation email
function sendOrderConfirmation($email, $order_id, $order_details)
{
    $subject = "Xác nhận đơn hàng #" . $order_id . " - PizzaHut";

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e31837;'>Cảm ơn bạn đã đặt hàng tại PizzaHut!</h2>
        <p>Đơn hàng #" . $order_id . " của bạn đã được xác nhận.</p>
        <h3>Chi tiết đơn hàng:</h3>
        " . $order_details . "
        <p>Chúng tôi sẽ sớm liên hệ với bạn để xác nhận thời gian giao hàng.</p>
        <hr>
        <p style='font-size: 12px; color: #666;'>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua hotline: 1900 1822</p>
    </div>";

    return sendMail($email, "", $subject, $body);
}

// Function to send welcome email to new users
function sendWelcomeEmail($email, $name)
{
    $subject = "Chào mừng bạn đến với PizzaHut!";

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e31837;'>Chào mừng " . $name . " đến với PizzaHut!</h2>
        <p>Cảm ơn bạn đã đăng ký tài khoản tại PizzaHut.</p>
        <p>Với tài khoản này, bạn có thể:</p>
        <ul>
            <li>Đặt hàng nhanh chóng và tiện lợi</li>
            <li>Theo dõi lịch sử đơn hàng</li>
            <li>Nhận thông tin về các chương trình khuyến mãi</li>
            <li>Tích điểm và nhận ưu đãi hấp dẫn</li>
        </ul>
        <p><a href='http://localhost/FinalProject_PHP/menu.php' style='background-color: #e31837; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Khám phá menu ngay!</a></p>
        <hr>
        <p style='font-size: 12px; color: #666;'>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>";

    return sendMail($email, $name, $subject, $body);
}

// Function to send order completion notification
function sendOrderCompletionEmail($email, $order_id, $order_details)
{
    $subject = "Đơn hàng #" . $order_id . " đã được hoàn thành - PizzaHut";

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e31837;'>Đơn hàng của bạn đã được hoàn thành!</h2>
        <p>Đơn hàng #" . $order_id . " của bạn đã được hoàn thành và đang trong quá trình vận chuyển.</p>
        <h3>Chi tiết đơn hàng:</h3>
        " . $order_details . "
        <p>Đơn hàng của bạn sẽ được giao trong thời gian sớm nhất.</p>
        <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của PizzaHut!</p>
        <hr>
        <p style='font-size: 12px; color: #666;'>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua hotline: 1900 1822</p>
    </div>";

    return sendMail($email, "", $subject, $body);
}