<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
;

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Quick View</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="quick-view">

        <h1 class="title">quick view</h1>

        <?php
        $pid = $_GET['pid'];
        $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $select_products->execute([$pid]);

        if ($select_products->rowCount() > 0) {
            $product = $select_products->fetch(PDO::FETCH_ASSOC);

            // Add to viewed products
            $viewed_product = array(
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'detail' => $product['detail'],
                'timestamp' => time()
            );

            // Remove if already exists
            foreach ($_SESSION['viewed_products'] as $key => $item) {
                if ($item['id'] == $pid) {
                    unset($_SESSION['viewed_products'][$key]);
                }
            }

            // Add to start of array
            array_unshift($_SESSION['viewed_products'], $viewed_product);

            // Keep only last 6 products
            $_SESSION['viewed_products'] = array_slice($_SESSION['viewed_products'], 0, 6);
            ?>
            <form action="" method="post" class="box">
                <input type="hidden" name="pid" value="<?= $product['id']; ?>">
                <input type="hidden" name="name" value="<?= $product['name']; ?>">
                <input type="hidden" name="price" value="<?= $product['price']; ?>">
                <input type="hidden" name="image" value="<?= $product['image']; ?>">
                <img src=" uploaded_img/<?= $product['image']; ?>" alt="">
                <a href="category.php?category=<?= $product['category']; ?>" class=" cat">
                    <?= $product['category']; ?></a>
                <div class="name"><?= $product['name']; ?></div>
                <div class="flex">
                    <div class="price">Chỉ từ <span><?= number_format($product['price']); ?>đ</span></div>
                    <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                </div>
                <button type="submit" name="add_to_cart" class="cart-btn">add to cart</button>
            </form>
            <?php
        } else {
            echo '<p class="empty">no products added yet!</p>';
        }
        ?>

    </section>



    <?php include 'components/footer.php'; ?>


    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>


</body>

</html>