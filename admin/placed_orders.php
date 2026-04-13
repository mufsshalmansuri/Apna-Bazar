<?php

include '../components/connect.php';

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}


// UPDATE PAYMENT STATUS
if(isset($_POST['update_payment'])){

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];

   $update_payment = $conn->prepare("UPDATE orders SET payment_status=? WHERE id=?");
   $update_payment->execute([$payment_status,$order_id]);

   $message[] = 'payment status updated!';
}


// UPDATE ORDER STATUS
if(isset($_POST['update_status'])){

   $order_id = $_POST['order_id'];
   $status = $_POST['status'];

   // GET ORDER DATA
   $get_order = $conn->prepare("SELECT * FROM orders WHERE id=?");
   $get_order->execute([$order_id]);
   $order = $get_order->fetch(PDO::FETCH_ASSOC);

   $email = $order['email'];
   $name = $order['name'];
   $order_number = $order['order_id'];

   // UPDATE STATUS
   $update_status = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
   $update_status->execute([$status,$order_id]);

   // IF DELIVERED
   if($status == "Delivered"){

      // AUTO COMPLETE PAYMENT
      $update_payment = $conn->prepare("UPDATE orders SET payment_status='completed' WHERE id=?");
      $update_payment->execute([$order_id]);

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
         $mail->Subject = 'Your Order Has Been Delivered';

         $mail->Body = "
         <h2>Order Delivered</h2>
         <p>Hello <b>$name</b>,</p>
         <p>Your order <b>#$order_number</b> has been successfully delivered.Thank you for shopping with us.</p>
         ";

         $mail->send();

      } catch (Exception $e) {
         // error ignore
      }

   }

   $message[] = 'order status updated!';
}


// DELETE ORDER
if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];

   $delete_order = $conn->prepare("DELETE FROM orders WHERE id=?");
   $delete_order->execute([$delete_id]);

   header('location:placed_orders.php');
   exit();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Placed Orders</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">

<h1 class="heading">placed orders</h1>

<div class="box-container">

<?php

$select_orders = $conn->prepare("SELECT * FROM orders ORDER BY id DESC");
$select_orders->execute();

if($select_orders->rowCount() > 0){

while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){

?>

<div class="box">

<p> Order ID : <span><?= $fetch_orders['order_id']; ?></span> </p>

<p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>

<p> name : <span><?= $fetch_orders['name']; ?></span> </p>

<p> email : <span><?= $fetch_orders['email']; ?></span> </p>

<p> number : <span><?= $fetch_orders['number']; ?></span> </p>

<p> address : <span><?= $fetch_orders['address']; ?></span> </p>

<p> total products : <span><?= $fetch_orders['total_products']; ?></span> </p>

<p> total price : <span>₹<?= $fetch_orders['total_price']; ?>/-</span> </p>

<p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>

<p> payment status : <span><?= $fetch_orders['payment_status']; ?></span> </p>

<p> order status : <span><?= $fetch_orders['status']; ?></span> </p>


<p>
Track Order :
<a href="../track_order.php?order_id=<?= $fetch_orders['order_id']; ?>" target="_blank">
View Tracking
</a>
</p>





<form action="" method="post">

<input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">

<select name="status" class="select">

<option selected disabled><?= $fetch_orders['status']; ?></option>

<option value="Pending">Pending</option>
<option value="Processing">Processing</option>
<option value="Shipped">Shipped</option>
<option value="Delivered">Delivered</option>

</select>

<div class="flex-btn">

<input type="submit" value="update status" class="option-btn" name="update_status">

<a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" 
class="delete-btn"
onclick="return confirm('delete this order?');">

delete

</a>

</div>

</form>

</div>

<?php

}

}else{

echo '<p class="empty">no orders placed yet!</p>';

}

?>

</div>

</section>


<script src="../js/admin_script.js"></script>

</body>
</html>