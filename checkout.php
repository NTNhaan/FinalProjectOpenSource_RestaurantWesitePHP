<?php

include 'components/connect.php';
include 'components/sendMail.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:home.php');
}
;

if (isset($_POST['submit'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = $_POST['address'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {

      if ($address == '') {
         $message[] = 'please add your address!';
      } else {

         // Check if payment method is PayPal
         if ($method == 'paypal') {
            include 'components/paypal_sandbox.php';

            // Create PayPal payment
            $payment = createPayPalPayment($total_price);

            if ($payment->state == 'created') {
               // Store payment ID in session
               $_SESSION['payment_id'] = $payment->id;

               // Redirect to PayPal approval URL
               foreach ($payment->links as $link) {
                  if ($link->rel == 'approval_url') {
                     header('Location: ' . $link->href);
                     exit();
                  }
               }
            } else {
               $message[] = 'Could not create PayPal payment';
            }
         } else {
            // For other payment methods
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);
            $order_id = $conn->lastInsertId();

            $cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $cart_items->execute([$user_id]);

            $order_details = "<table style='width:100%; border-collapse: collapse; margin-bottom: 20px;'>
               <tr style='background-color: #f8f9fa;'>
                   <th style='padding: 10px; border: 1px solid #ddd;'>Sản phẩm</th>
                   <th style='padding: 10px; border: 1px solid #ddd;'>Số lượng</th>
                   <th style='padding: 10px; border: 1px solid #ddd;'>Giá</th>
               </tr>";

            while ($item = $cart_items->fetch(PDO::FETCH_ASSOC)) {
               $order_details .= "<tr>
                   <td style='padding: 10px; border: 1px solid #ddd;'>{$item['name']}</td>
                   <td style='padding: 10px; border: 1px solid #ddd;'>{$item['quantity']}</td>
                   <td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($item['price']) . "đ</td>
               </tr>";
            }

            $order_details .= "<tr style='background-color: #f8f9fa;'>
               <td colspan='2' style='padding: 10px; border: 1px solid #ddd; text-align: right;'><strong>Tổng cộng:</strong></td>
               <td style='padding: 10px; border: 1px solid #ddd;'><strong>" . number_format($total_price) . "đ</strong></td>
           </tr></table>";

            $mail_result = sendOrderConfirmation($email, $order_id, $order_details);

            if ($mail_result === true) {
               $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
               $delete_cart->execute([$user_id]);

               $message[] = 'Đặt hàng thành công! Email xác nhận đã được gửi đến hòm thư của bạn.';
               header('refresh:2;url=orders.php');
            } else {
               $message[] = 'Đặt hàng thành công! Nhưng không thể gửi email xác nhận.';
               error_log("Failed to send order confirmation email: " . $mail_result);
               header('refresh:2;url=orders.php');
            }
         }

      }

   } else {
      $message[] = 'your cart is empty';
   }

}

// Handle PayPal return
if (isset($_GET['success']) && $_GET['success'] == 'true') {
   include 'components/paypal_sandbox.php';

   $paymentId = $_SESSION['payment_id'];
   $payerId = $_GET['PayerID'];

   // Execute payment
   $payment = executePayPalPayment($paymentId, $payerId);

   if ($payment->state == 'approved') {
      // Payment successful, create order
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, 'paypal', $address, $total_products, $total_price]);
      $order_id = $conn->lastInsertId();

      $message[] = 'Payment successful! Your order has been placed.';
      header('refresh:2;url=orders.php');
   } else {
      $message[] = 'Payment failed. Please try again.';
   }
} elseif (isset($_GET['cancel']) && $_GET['cancel'] == 'true') {
   $message[] = 'Payment cancelled. Please try again.';
}

// Xử lý sau khi thanh toán PayPal thành công
if (isset($_POST['paypal_payment_id']) && isset($_POST['paypal_payer_id'])) {
   try {
      $payment_id = $_POST['paypal_payment_id'];
      $payer_id = $_POST['paypal_payer_id'];

      // Kiểm tra giỏ hàng
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $check_cart->execute([$user_id]);

      // Kiểm tra và xử lý đơn hàng
      if ($check_cart->rowCount() > 0) {
         // Bắt đầu transaction
         $conn->beginTransaction();

         try {
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, payment_status) VALUES(?,?,?,?,?,?,?,?,?)");
            $insert_order->execute([
               $user_id,
               $_POST['name'],
               $_POST['number'],
               $_POST['email'],
               'paypal',
               $_POST['address'],
               $_POST['total_products'],
               $_POST['total_price'],
               'pending'
            ]);

            $order_id = $conn->lastInsertId();

            // Gửi email xác nhận
            $cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $cart_items->execute([$user_id]);

            $order_details = "<table style='width:100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr style='background-color: #f8f9fa;'>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Sản phẩm</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Số lượng</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>Giá</th>
                    </tr>";

            while ($item = $cart_items->fetch(PDO::FETCH_ASSOC)) {
               $order_details .= "<tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$item['name']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$item['quantity']}</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($item['price']) . "đ</td>
                    </tr>";
            }

            $order_details .= "<tr style='background-color: #f8f9fa;'>
                    <td colspan='2' style='padding: 10px; border: 1px solid #ddd; text-align: right;'><strong>Tổng cộng:</strong></td>
                    <td style='padding: 10px; border: 1px solid #ddd;'><strong>" . number_format($_POST['total_price']) . "đ</strong></td>
                </tr></table>";

            $mail_result = sendOrderConfirmation($_POST['email'], $order_id, $order_details);

            // Xóa giỏ hàng
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            // Commit transaction
            $conn->commit();

            $message[] = 'Đặt hàng thành công! Email xác nhận đã được gửi đến hòm thư của bạn.';
            header('refresh:2;url=orders.php');
         } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $conn->rollBack();
            throw $e;
         }
      } else {
         $message[] = 'Giỏ hàng của bạn đang trống!';
      }
   } catch (Exception $e) {
      error_log("PayPal Payment Error: " . $e->getMessage());
      $message[] = 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PizzaHut | Checkout</title>
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
        <h3>checkout</h3>
        <p><a href="home.php">home</a> <span> / checkout</span></p>
    </div>

    <section class="checkout">

        <h1 class="title">order summary</h1>

        <form action="" method="post">

            <div class="cart-items">
                <h3>cart items</h3>
                <?php
            $grand_total = 0;
            $cart_items[] = '';
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
                  ?>
                <p><span class="name"><?= $fetch_cart['name']; ?></span><span
                        class="price">$<?= $fetch_cart['price']; ?> x
                        <?= $fetch_cart['quantity']; ?></span></p>
                <?php
               }
            } else {
               echo '<p class="empty">your cart is empty!</p>';
            }
            ?>
                <p class="grand-total"><span class="name">grand total :
                    </span><span class="price">$
                        <?= $grand_total; ?>
                    </span></p>
                <a href="cart.php" class="btn">veiw cart</a>
            </div>

            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
            <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
            <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
            <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
            <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">

            <div class="user-info">
                <h3>your info</h3>
                <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
                <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
                <p><i class="fas fa-envelope"></i><span>
                        <?= $fetch_profile['email'] ?>
                    </span></p>
                <a href="update_profile.php" class="btn">update info</a>
                <h3>delivery address</h3>
                <p><i class="fas fa-map-marker-alt"></i><span><?php if ($fetch_profile['address'] == '') {
               echo 'please enter your address';
            } else {
               echo $fetch_profile['address'];
            } ?></span>
                </p>
                <a href="update_address.php" class="btn">update address</a>
                <select name="method" class="box" required>
                    <option value="" disabled selected>select payment method --</option>
                    <option value="cash on delivery">cash on delivery</option>
                    <option value="credit card">credit card</option>
                    <option value="paytm">paytm</option>
                    <option value="paypal">paypal</option>
                </select>
                <div class="payment-buttons">
                    <input type="submit" value="place order" class="btn <?php if ($fetch_profile['address'] == '') {
                  echo 'disabled';
               } ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
                    <!-- PayPal Button -->
                    <div id="paypal-button-container" style="width: 100%; margin-top: 1rem;"></div>
                </div>
            </div>
        </form>

    </section>

    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?= $_ENV['PAYPAL_CLIENT_ID'] ?>&currency=USD"></script>
    <script>
    const paypalButtons = window.paypal.Buttons({
        style: {
            shape: "rect",
            layout: "horizontal",
            color: "white",
            label: "paypal",
            height: 45
        },
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?= $grand_total ?>'
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Tạo form mới
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                // Thêm các trường ẩn vào form
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = 'method';
                methodInput.value = 'paypal';
                form.appendChild(methodInput);

                const paymentIdInput = document.createElement('input');
                paymentIdInput.type = 'hidden';
                paymentIdInput.name = 'paypal_payment_id';
                paymentIdInput.value = data.orderID;
                form.appendChild(paymentIdInput);

                const payerIdInput = document.createElement('input');
                payerIdInput.type = 'hidden';
                payerIdInput.name = 'paypal_payer_id';
                payerIdInput.value = details.payer.payer_id;
                form.appendChild(payerIdInput);

                // Thêm các trường cần thiết khác
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'name';
                nameInput.value = '<?= $fetch_profile['name'] ?>';
                form.appendChild(nameInput);

                const numberInput = document.createElement('input');
                numberInput.type = 'hidden';
                numberInput.name = 'number';
                numberInput.value = '<?= $fetch_profile['number'] ?>';
                form.appendChild(numberInput);

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'email';
                emailInput.value = '<?= $fetch_profile['email'] ?>';
                form.appendChild(emailInput);

                const addressInput = document.createElement('input');
                addressInput.type = 'hidden';
                addressInput.name = 'address';
                addressInput.value = '<?= $fetch_profile['address'] ?>';
                form.appendChild(addressInput);

                const totalProductsInput = document.createElement('input');
                totalProductsInput.type = 'hidden';
                totalProductsInput.name = 'total_products';
                totalProductsInput.value = '<?= $total_products ?>';
                form.appendChild(totalProductsInput);

                const totalPriceInput = document.createElement('input');
                totalPriceInput.type = 'hidden';
                totalPriceInput.name = 'total_price';
                totalPriceInput.value = '<?= $grand_total ?>';
                form.appendChild(totalPriceInput);

                // Thêm form vào body và submit
                document.body.appendChild(form);
                form.submit();
            }).catch(function(err) {
                console.error('PayPal Capture Error:', err);
                alert('Có lỗi xảy ra khi xử lý thanh toán PayPal. Vui lòng thử lại.');
            });
        },
        onError: function(err) {
            console.error('PayPal Error:', err);
            alert('Có lỗi xảy ra khi kết nối với PayPal. Vui lòng thử lại.');
        },
        onCancel: function(data) {
            console.log('Payment cancelled by user');
            alert('Bạn đã hủy thanh toán PayPal.');
        }
    });

    if (paypalButtons.isEligible()) {
        paypalButtons.render('#paypal-button-container');
    }
    </script>

    <!-- footer section starts  -->
    <?php include 'components/footer.php'; ?>
    <!-- footer section ends -->






    <!-- custom js file link  -->
    <script src="js/script.js"></script>

</body>

</html>