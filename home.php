<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
;

$page = 'home';

include 'components/add_cart.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Home</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

    <!-- Preload slider images -->
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/Desktop_ZTBNQ.jpeg">
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/Hometop.jpeg">
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/Lifestyle.jpeg">
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/VIE_BZGZF_050320250622.jpeg">
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/Des_Hometop.jpeg">
    <link rel="preload" as="image" href="images/pizzahutnew/Slider/Desktop_Hometop.jpeg">


    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'components/user_header.php'; ?>



    <section class="hero">
        <div class="swiper hero-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/Desktop_ZTBNQ.jpeg" alt="Slide 1">
                </div>
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/Hometop.jpeg" alt="Slide 2">
                </div>
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/Lifestyle.jpeg" alt="Slide 3">
                </div>
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/VIE_BZGZF_050320250622.jpeg" alt="Slide 4">
                </div>
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/Des_Hometop.jpeg" alt="Slide 5">
                </div>
                <div class="swiper-slide slide">
                    <img src="images/pizzahutnew/Slider/Desktop_Hometop.jpeg" alt="Slide 6">
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <section class="category">

        <h1 class="title">food category</h1>

        <div class="box-container">

            <a href="category.php?category=starter dish" class="box">
                <img src="images/cat-1.png" alt="">
                <h3>Starter Dish</h3>
            </a>

            <a href="category.php?category=main dish" class="box">
                <img src="images/cat-2.png" alt="">
                <h3>main dishes</h3>
            </a>

            <a href="category.php?category=drinks" class="box">
                <img src="images/cat-3.png" alt="">
                <h3>drinks</h3>
            </a>

            <a href="category.php?category=chicken dish" class="box">
                <img src="images/step-3.png" alt="">
                <h3>Chicken Dish</h3>
            </a>

        </div>

    </section>




    <section class="products">
        <?php
        // Định nghĩa các category ưu tiên
        $priority_categories = ['Combo', 'B1G1', 'B1G3', 'Mybox'];

        // Hiển thị sản phẩm cho mỗi category ưu tiên
        foreach ($priority_categories as $category) {
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
            $select_products->execute([$category]);

            if ($select_products->rowCount() > 0) {
                ?>
                <div class="category-title">
                    <h2><?= $category ?></h2>
                </div>

                <div class="box-container priority-category">
                    <?php
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
                    ?>
                </div>
                <?php
            }
        }
        ?>

        <!-- Latest Dishes Section -->
        <h1 class="title" style="margin-top: 20px;">latest dishes</h1>

        <div class="box-container">
            <?php
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE category NOT IN (?, ?, ?, ?) LIMIT 6");
            $select_products->execute($priority_categories);
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

    <script>
        var swiper = new Swiper(".hero-slider", {
            loop: true,
            grabCursor: true,
            effect: "fade",
            fadeEffect: {
                crossFade: true
            },
            speed: 800,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
        });
    </script>

</body>

</html>