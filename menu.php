<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Initialize viewed products session if not exists
if (!isset($_SESSION['viewed_products'])) {
    $_SESSION['viewed_products'] = array();
}

// Handle product view
if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Get product details
    $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $select_product->execute([$pid]);

    if ($select_product->rowCount() > 0) {
        $product = $select_product->fetch(PDO::FETCH_ASSOC);

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
    }
}

include 'components/add_cart.php';
$page = 'menu';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Menu</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <!-- header section starts  -->
    <?php include 'components/user_header.php'; ?>
    <!-- header section ends -->


    <!-- menu section starts  -->
    <?php
    // Get unique categories with MAIN DISH first
    $categories = $conn->prepare("SELECT DISTINCT category FROM products ORDER BY 
      CASE 
         WHEN category = 'MAIN DISH' THEN 0 
         ELSE 1 
      END, 
      category");
    $categories->execute();

    // For each category
    while ($category = $categories->fetch(PDO::FETCH_ASSOC)) {
        $current_category = $category['category'];
        $banner_image = 'images/pizzahutnew/banner/' . strtolower($current_category) . '.jpeg';
        ?>
    <!-- Category Title -->
    <div class="category-title">
        <h2><?= ucwords($current_category) ?></h2>
    </div>

    <!-- Category Banner -->
    <div class="category-banner">
        <img src="<?= $banner_image ?>" alt="<?= ucwords($current_category) ?> banner">
    </div>

    <!-- Products Section -->
    <section class="products">
        <div class="box-container">
            <?php
                $select_products = $conn->prepare("SELECT * FROM products WHERE category = ?");
                $select_products->execute([$current_category]);

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
    <?php
    }
    ?>
    <!-- menu section ends -->

    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>