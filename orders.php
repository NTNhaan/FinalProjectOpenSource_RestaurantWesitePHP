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
$page = 'orders';

// Initialize select_orders as null
$select_orders = null;

// Only query orders when search is performed
if (isset($_POST['search_btn']) && !empty($_POST['search_box'])) {
   $search_box = $_POST['search_box'];
   $search_box = filter_var($search_box, FILTER_SANITIZE_STRING);

   $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE (number LIKE ? OR id LIKE ?) AND user_id = ?");
   $select_orders->execute(['%' . $search_box . '%', '%' . $search_box . '%', $user_id]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Orders</title>
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
        <h3>orders</h3>
        <p><a href="html.php">home</a> <span> / orders</span></p>
    </div>

    <!-- search form section starts -->
    <section class="search-form">
        <form method="post" action="">
            <h3>Chỉ áp dụng cho đơn hàng giao hàng</h3>
            <div class="search-box">
                <input type="text" name="search_box" placeholder="Nhập số điện thoại hoặc mã đơn hàng" class="box"
                    pattern="[0-9]+" title="Vui lòng chỉ nhập số">
                <button type="submit" name="search_btn" class="btn">Tìm kiếm</button>
            </div>
        </form>
    </section>
    <!-- search form section ends -->

    <section class="orders">

        <h1 class="title">your orders</h1>

        <div class="box-container">

            <?php
         if ($user_id == '') {
            echo '<p class="empty">please login to see your orders</p>';
         } else {
            if (!isset($_POST['search_btn'])) {
               echo '<p class="empty">Vui lòng nhập số điện thoại hoặc mã đơn hàng để tìm kiếm</p>';
            } else if (empty($_POST['search_box'])) {
               echo '<p class="empty">Vui lòng nhập thông tin tìm kiếm</p>';
            } else if ($select_orders && $select_orders->rowCount() > 0) {
               while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  ?>
            <div class="box">
                <p>placed on : <span><?= $fetch_orders['placed_on']; ?></span></p>
                <p>name : <span><?= $fetch_orders['name']; ?></span></p>
                <p>email : <span><?= $fetch_orders['email']; ?></span></p>
                <p>number : <span><?= $fetch_orders['number']; ?></span></p>
                <p>address : <span><?= $fetch_orders['address']; ?></span></p>
                <p>payment method : <span><?= $fetch_orders['method']; ?></span></p>
                <p>your orders : <span><?= $fetch_orders['total_products']; ?></span></p>
                <p>total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span></p>
                <p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
                              echo 'red';
                           } else {
                              echo 'green';
                           }
                           ; ?>"><?= $fetch_orders['payment_status']; ?></span>
                </p>
            </div>
            <?php
               }
            } else {
               echo '<p class="empty">no orders placed yet!</p>';
            }
         }
         ?>

        </div>

    </section>










    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->






    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>