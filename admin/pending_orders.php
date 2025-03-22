<?php

include '../components/connect.php';
include '../components/sendMail.php';

session_start();

$admin_id = $_SESSION['admin_id'];
$page = 'orders';
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];

    // Get order details before updating
    $get_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
    $get_order->execute([$order_id]);
    $order = $get_order->fetch(PDO::FETCH_ASSOC);

    // Update order status
    $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
    $update_status->execute([$payment_status, $order_id]);

    // If status is changed to completed, send email
    if ($payment_status == 'completed') {
        // Prepare order details for email
        $order_details = "<table style='width:100%; border-collapse: collapse; margin-bottom: 20px;'>
            <tr style='background-color: #f8f9fa;'>
                <th style='padding: 10px; border: 1px solid #ddd;'>Sản phẩm</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>Tổng tiền</th>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$order['total_products']}</td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($order['total_price']) . "đ</td>
            </tr>
        </table>";

        // Send completion email
        $mail_result = sendOrderCompletionEmail($order['email'], $order_id, $order_details);

        if ($mail_result === true) {
            $message[] = 'Trạng thái đơn hàng đã được cập nhật và email thông báo đã được gửi!';
        } else {
            $message[] = 'Trạng thái đơn hàng đã được cập nhật nhưng không thể gửi email!';
            error_log("Failed to send order completion email: " . $mail_result);
        }
    } else {
        $message[] = 'Trạng thái đơn hàng đã được cập nhật!';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_order->execute([$delete_id]);
    header('location:pending_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng đang xử lý</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- pending orders section starts  -->

    <section class="placed-orders">

        <h1 class="heading">Đơn hàng đang xử lý</h1>

        <div class="box-container">

            <?php
            // Chỉ lấy các đơn hàng có trạng thái pending
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ? ORDER BY placed_on DESC");
            $select_orders->execute(['pending']);
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="box">
                        <p>Mã đơn hàng : <span>#<?= $fetch_orders['id']; ?></span></p>
                        <p>Ngày đặt : <span><?= $fetch_orders['placed_on']; ?></span></p>
                        <p>Tên khách hàng : <span><?= $fetch_orders['name']; ?></span></p>
                        <p>Số điện thoại : <span><?= $fetch_orders['number']; ?></span></p>
                        <p>Email : <span><?= $fetch_orders['email']; ?></span></p>
                        <p>Địa chỉ : <span><?= $fetch_orders['address']; ?></span></p>
                        <p>Sản phẩm : <span><?= $fetch_orders['total_products']; ?></span></p>
                        <p>Tổng tiền : <span><?= number_format($fetch_orders['total_price']); ?>đ</span></p>
                        <p>Phương thức thanh toán : <span><?= $fetch_orders['method']; ?></span></p>
                        <form action="" method="POST">
                            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                            <select name="payment_status" class="drop-down">
                                <option value="pending" selected>Đang xử lý</option>
                                <option value="completed">Hoàn thành</option>
                            </select>
                            <div class="flex-btn">
                                <input type="submit" value="Cập nhật" class="btn" name="update_payment">
                                <a href="pending_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn"
                                    onclick="return confirm('Xóa đơn hàng này?');">Xóa</a>
                            </div>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="empty">Không có đơn hàng nào đang xử lý!</p>';
            }
            ?>

        </div>

    </section>

    <!-- pending orders section ends -->

    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>