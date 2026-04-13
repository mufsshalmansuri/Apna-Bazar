<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
}
;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

require('fpdf/fpdf.php');

if (isset($_POST['order'])) {

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);

   $address = 'Flat No. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);

   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   // generate order id
   $order_id = "ORD" . rand(100000, 999999);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {

      $insert_order = $conn->prepare("INSERT INTO orders
      (order_id,user_id,name,number,email,method,address,total_products,total_price,status)
      VALUES(?,?,?,?,?,?,?,?,?,?)");

      $insert_order->execute([
         $order_id,
         $user_id,
         $name,
         $number,
         $email,
         $method,
         $address,
         $total_products,
         $total_price,
         'Pending'
      ]);
      // ===== ADVANCED PDF INVOICE =====

      $pdf = new FPDF();
      $pdf->AddPage();

      // STORE TITLE
      $pdf->SetFont('Arial', 'B', 20);
      $pdf->Cell(190, 15, 'APNA BAZAR', 0, 1, 'C');

      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(190, 5, 'E-Commerce Invoice', 0, 1, 'C');

      $pdf->Ln(10);

      // INVOICE INFO

      $pdf->SetFont('Arial', 'B', 12);
      $pdf->Cell(95, 8, "Invoice No: $order_id", 1, 0);
      $pdf->Cell(95, 8, "Payment: $method", 1, 1);

      $pdf->Cell(95, 8, "Customer: $name", 1, 0);
      $pdf->Cell(95, 8, "Phone: $number", 1, 1);

      $pdf->Cell(190, 8, "Email: $email", 1, 1);

      $pdf->MultiCell(190, 8, "Address: $address", 1);

      $pdf->Ln(10);

      // PRODUCT TABLE HEADER

      $pdf->SetFont('Arial', 'B', 12);

      $pdf->Cell(60, 10, 'Product', 1);
      $pdf->Cell(20, 10, 'Size', 1);
      $pdf->Cell(30, 10, 'Price', 1);
      $pdf->Cell(30, 10, 'Qty', 1);
      $pdf->Cell(50, 10, 'Total', 1, 1);

      // CART PRODUCTS

      $pdf->SetFont('Arial', '', 9);

      $select_products = $conn->prepare("SELECT * FROM cart WHERE user_id=?");
      $select_products->execute([$user_id]);

      while ($row = $select_products->fetch(PDO::FETCH_ASSOC)) {

         $product = $row['name'];
         $price = $row['price'];
         $qty = $row['quantity'];
         $total = $price * $qty;

         $size = $row['size'];

         $pdf->Cell(60, 10, $product, 1);
         $pdf->Cell(20, 10, $size, 1);
         $pdf->Cell(30, 10, "Rs $price", 1);
         $pdf->Cell(30, 10, $qty, 1);
         $pdf->Cell(50, 10, "Rs $total", 1, 1);
      }

      $pdf->Ln(5);

      // GRAND TOTAL

      $pdf->SetFont('Arial', 'B', 14);

      $pdf->Cell(140, 10, 'Grand Total', 1);
      $pdf->Cell(50, 10, "Rs $total_price", 1, 1);

      $pdf->Ln(10);

      $pdf->SetFont('Arial', '', 10);

      $pdf->Cell(190, 10, 'Thank you for shopping with Apna Bazar!', 0, 1, 'C');

      // SAVE FILE

      $pdf_file = "invoice_$order_id.pdf";

      $pdf->Output('F', $pdf_file);

      // clear cart
      $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id=?");
      $delete_cart->execute([$user_id]);

      // SEND EMAIL

      $mail = new PHPMailer(true);

      try {

         $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com';
         $mail->SMTPAuth = true;

         $mail->Username = 'mufsshalmansuri@gmail.com';
         $mail->Password = '';//enter your email password

         $mail->SMTPSecure = 'ssl';
         $mail->Port = 465;

         $mail->setFrom('mufsshalmansuri@gmail.com', 'Apna Bazar');
         $mail->addAddress($email, $name);

         $mail->isHTML(true);

         $mail->Subject = "Order Confirmation - $order_id";

         $mail->Body = "

         <h2>Apna Bazar</h2>

         <p>Your order has been placed successfully.</p>

         <p><b>Order ID:</b> $order_id</p>

         <p><b>Total Price:</b> ₹$total_price</p>

         <p><b>Payment Method:</b> $method</p>

         <p><b>Delivery Address:</b> $address</p>

         <p>You can track your order using your Order ID.</p>

         <p>Your invoice is attached with this email.</p>

         ";
         $mail->addAttachment($pdf_file);
         $mail->send();

      } catch (Exception $e) {
         echo "Mail not sent.";
      }
      // redirect to tracking page
      header("Location: track_order.php?order_id=" . $order_id);
      exit();

      $message[] = "Order Placed Successfully! Your Order ID is $order_id";

   } else {
      $message[] = 'your cart is empty';
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="checkout-orders">

      <form action="" method="POST">

         <h3>your orders</h3>

         <div class="display-orders">
            <?php
            $grand_total = 0;
            $cart_items[] = '';
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  $cart_items[] = $fetch_cart['name'] . ' [Size: ' . $fetch_cart['size'] . '] (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
                  ?>
                  <p>
                     <?= $fetch_cart['name']; ?>

                     <?php if (!empty($fetch_cart['size'])) { ?>
                        - <b>Size: <?= $fetch_cart['size']; ?></b>
                     <?php } ?>

                     <span>(<?= '₹' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity']; ?>)</span>
                  </p>
                  <?php
               }
            } else {
               echo '<p class="empty">your cart is empty!</p>';
            }
            ?>
            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
            <div class="grand-total">grand total : <span>₹<?= $grand_total; ?>/-</span></div>
         </div>

         <h3>place your orders</h3>

         <div class="flex">
            <div class="inputBox">
               <span>your name :</span>
               <input type="text" name="name" placeholder="enter your name" class="box" maxlength="20" required>
            </div>
            <div class="inputBox">
               <span>your number :</span>
               <input type="number" name="number" placeholder="enter your number" class="box" min="0" max="9999999999"
                  onkeypress="if(this.value.length == 10) return false;" required>
            </div>
            <div class="inputBox">
               <span>your email :</span>
               <input type="email" name="email" placeholder="enter your email" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>payment method :</span>
               <select name="method" class="box" required>
                  <option value="cash on delivery">cash on delivery</option>
                  <!-- <option value="credit card">credit card</option>
               <option value="paytm">paytm</option>
               <option value="paypal">paypal</option> -->
               </select>
            </div>
            <div class="inputBox">
               <span>address line 01 :</span>
               <input type="text" name="flat" placeholder="e.g. flat number" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>address line 02 :</span>
               <input type="text" name="street" placeholder="e.g. street name" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>city :</span>
               <input type="text" name="city" placeholder="e.g. mumbai" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>state :</span>
               <input type="text" name="state" placeholder="e.g. maharashtra" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>country :</span>
               <input type="text" name="country" placeholder="e.g. India" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>pin code :</span>
               <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" min="0" max="999999"
                  onkeypress="if(this.value.length == 6) return false;" class="box" required>
            </div>
         </div>

         <input type="submit" name="order" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="place order">

      </form>

   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>