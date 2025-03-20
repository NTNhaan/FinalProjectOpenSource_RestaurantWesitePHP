<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:home.php');
}
;

if (isset($_POST['delete'])) {
    $cart_id = $_POST['cart_id'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$cart_id]);
    $message[] = 'cart item deleted!';
}

if (isset($_POST['delete_all'])) {
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    // header('location:cart.php');
    $message[] = 'deleted all from cart!';
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
    $message[] = 'cart quantity updated';
}

$grand_total = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Cart</title>
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

    <div class="heading">
        <h3>Giỏ hàng của bạn</h3>
        <p><a href="home.php">home</a> <span> / cart</span></p>
    </div>

    <!-- shopping cart section starts  -->
    <section class="products">
        <div class="cart-container">
            <!-- Left side - Cart items -->
            <div class="cart-items">
                <div class="box-container">
                    <?php
                    $grand_total = 0;
                    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                    $select_cart->execute([$user_id]);
                    if ($select_cart->rowCount() > 0) {
                        while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                    <form action="" method="post" class="box">
                        <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">

                        <div class="product-image">
                            <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                        </div>

                        <div class="product-content">
                            <div>
                                <h3 class="product-name"><?= $fetch_cart['name']; ?></h3>
                                <span class="unit-price">Đơn giá: <?= number_format($fetch_cart['price']); ?>đ</span>
                            </div>

                            <div class="price">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div class="quantity-control">
                                        <input type="number" name="qty" class="qty" min="1" max="99"
                                            value="<?= $fetch_cart['quantity']; ?>" maxlength="2">
                                        <button type="submit" class="update-btn" name="update_qty">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button type="submit" name="delete"
                                            onclick="return confirm('Xóa sản phẩm này?');">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="sub-total">
                                    Thành tiền:
                                    <span><?= number_format($sub_total = ($fetch_cart['price'] * $fetch_cart['quantity'])); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                            $grand_total += $sub_total;
                        }
                    } else {
                        echo '<div class="empty-cart">
                                <p>Giỏ hàng của bạn trống</p>
                                <p>Tại sao không thử một vài món trong <a href="menu.php">thực đơn của chúng tôi</a>?</p>
                            </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Right side - Cart summary -->
            <div class="cart-summary">
                <div class="summary-box">
                    <h3>Tổng quan đơn hàng</h3>

                    <!-- Total calculation -->
                    <div class="total-section">
                        <div class="total-row">
                            <span>Tạm tính</span>
                            <span><?= number_format($grand_total); ?>đ</span>
                        </div>
                        <div class="total-row">
                            <span>Giảm giá thành viên</span>
                            <span>0đ</span>
                        </div>
                        <div class="total-row">
                            <span>Phí giao hàng</span>
                            <span>0đ</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Tổng cộng</span>
                            <span><?= number_format($grand_total); ?>đ</span>
                        </div>
                    </div>

                    <!-- Checkout button -->
                    <button class="checkout-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">
                        <a href="checkout.php">Thanh Toán</a>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <!-- shopping cart section ends -->

    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>