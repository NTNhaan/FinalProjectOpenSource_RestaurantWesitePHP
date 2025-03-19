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
    <title>PizzaHut | Category</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <?php
    $category = $_GET['category'];
    $banner_image = 'images/pizzahutnew/banner/' . strtolower($category) . '.jpeg';
    ?>

    <!-- Category Title -->
    <div class="category-title">
        <h2><?= ucwords($category) ?></h2>
    </div>

    <!-- Category Banner -->
    <div class="category-banner">
        <img src="<?= $banner_image ?>" alt="<?= ucwords($category) ?> banner">
    </div>

    <section class="products">
        <div class="box-container">
            <?php
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
            $select_products->execute([$category]);
            if ($select_products->rowCount() > 0) {
                while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <form action="" method="post" class="box">
                        <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                        <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                        <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                        <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">

                        <div class="product-image">
                            <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                        </div>

                        <div class="product-content">
                            <div class="product-info">
                                <h3 class="product-name"><?= $fetch_products['name']; ?></h3>
                                <p class="product-detail"><?= $fetch_products['detail']; ?></p>
                            </div>
                            <div class="product-bottom">
                                <div class="price">Chỉ từ <span><?= number_format($fetch_products['price']); ?>đ</span></div>
                                <div class="actions">
                                    <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                                    <button type="submit" class="add-to-cart" name="add_to_cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="view-detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                }
            } else {
                echo '<p class="empty">no products added yet!</p>';
            }
            ?>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>