<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
;
$page = 'about';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | About Us</title>
    <link rel="icon" type="image/x-icon" href="../images/PizzaHut/pizza-hut-logo.png">
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

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
        <h3>about us</h3>
        <p><a href="home.php">home</a> <span> / about</span></p>
    </div>

    <!-- about section starts  -->

    <section class="about">
        <div class="row">
            <div class="content">
                <div class="image-gallery">
                    <div class="gallery-grid">
                        <div class="gallery-item main-image">
                            <img src="images/pizzahutnew/AboutUs/1.1.jpg" alt="Pizza Hut Team" class="about-img">
                        </div>
                    </div>
                </div>
                <p>Pizza Hut là chuỗi nhà hàng pizza được yêu thích và lớn nhất thế giới, trực thuộc tập đoàn
                    Yum!
                    (www.yum.com). Pizza Hut có mặt tại 100 quốc gia trên khắp thế giới từ tháng 4 năm 2016.</p>
                <p>Sự kiện này đánh dấu một cột mốc ý nghĩa để chứng cho sự cam kết của nhãn hàng về chất lượng
                    pizza
                    hảo hạng và phong cách phục vụ chuyên nghiệp.</p>
                <p>Pizza Hut có mặt tại Việt Nam từ năm 2006 với 100% vốn nước ngoài, và hiện đã phát triển hơn
                    110 nhà
                    hàng với trên 3.000 nhân viên.</p>
                <div class="about-points">
                    <div class="point">
                        <h4>Pizza Hut Việt Nam - nơi bạn được thỏa sức thể hiện chính mình với cơ hội hấp dẫn để
                            phát
                            triển cá nhân lẫn nghề nghiệp toàn diện!</h4>
                    </div>
                    <div class="point">
                        <h4>Chúng tôi luôn mang đến môi trường làm việc thân thiện và chuyên nghiệp cho từng
                            nhân viên,
                            nỗ lực toàn thiện giá trị "Cùng nhau hướng đến một Pizza Hut TUYỆT VỜI"</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="image-gallery">
            <div class="gallery-grid">
                <div class="gallery-item main-image">
                    <img src="images/pizzahutnew/AboutUs/1.4.jpg" alt="Pizza Hut Team" class="about-img">
                </div>
                <div class="gallery-item">
                    <img src="images/pizzahutnew/AboutUs/1.5.jpg" alt="Pizza Hut Store" class="about-img">
                </div>
                <div class="gallery-item">
                    <img src="images/pizzahutnew/AboutUs/1.6.jpg" alt="Pizza Hut Team" class="about-img">
                </div>
                <div class="gallery-item">
                    <img src="images/pizzahutnew/AboutUs/1.2.jpg" alt="Pizza Hut Team" class="about-img">
                </div>
                <div class="gallery-item">
                    <img src="images/pizzahutnew/AboutUs/Delivery.jpg" alt="Pizza Hut Team" class="about-img">
                </div>
            </div>
            <div class="brand-logo">
                <img src="images/pizzahutnew/AboutUs/1.7.jpg" alt="Pizza Hut Brand" class="logo-img">
            </div>
        </div>
    </section>

    <!-- about section ends -->

    <!-- steps section starts  -->

    <section class="steps">

        <h1 class="title">simple steps</h1>

        <div class="box-container">

            <div class="box">
                <img src="images/step-1.png" alt="">
                <h3>choose order</h3>
                <p>Lựa chọn thực đơn ngày hôm nay của bạn, pizza, salad, đồ uống, và nhiều hơn nữa</p>
            </div>

            <div class="box">
                <img src="images/step-2.png" alt="">
                <h3>fast delivery</h3>
                <p>Chính sách giao hàng nhanh trong vòng 30 phút</p>
            </div>

            <div class="box">
                <img src="images/step-3.png" alt="">
                <h3>enjoy food</h3>
                <p>Thưởng thức những món ăn ngon nhất từ Pizza Hut</p>
            </div>

        </div>

    </section>

    <!-- steps section ends -->

    <!-- reviews section starts  -->

    <section class="reviews">

        <h1 class="title">customer's reivews</h1>

        <div class="swiper reviews-slider">

            <div class="swiper-wrapper">

                <div class="swiper-slide slide">
                    <img src="images/pic-1.png" alt="">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos voluptate eligendi laborum
                        molestias ut earum nulla sint voluptatum labore nemo.</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>john deo</h3>
                </div>

                <div class="swiper-slide slide">
                    <img src="images/pic-2.png" alt="">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos voluptate eligendi laborum
                        molestias ut earum nulla sint voluptatum labore nemo.</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>john deo</h3>
                </div>

                <div class="swiper-slide slide">
                    <img src="images/pic-3.png" alt="">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos voluptate eligendi laborum
                        molestias ut earum nulla sint voluptatum labore nemo.</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>john deo</h3>
                </div>

                <div class="swiper-slide slide">
                    <img src="images/pic-4.png" alt="">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos voluptate eligendi laborum
                        molestias ut earum nulla sint voluptatum labore nemo.</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>john deo</h3>
                </div>

                <div class="swiper-slide slide">
                    <img src="images/pic-6.png" alt="">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos voluptate eligendi laborum
                        molestias ut earum nulla sint voluptatum labore nemo.</p>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>john deo</h3>
                </div>

            </div>

            <div class="swiper-pagination"></div>

        </div>

    </section>

    <!-- reviews section ends -->

    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->

    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

    <script>
        var swiper = new Swiper(".reviews-slider", {
            loop: true,
            grabCursor: true,
            spaceBetween: 20,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                },
                700: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    </script>

</body>

</html>