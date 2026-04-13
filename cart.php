<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $conn->prepare("DELETE FROM `cart` WHERE id = ?")->execute([$cart_id]);
}

if(isset($_GET['delete_all'])){
   $conn->prepare("DELETE FROM `cart` WHERE user_id = ?")->execute([$user_id]);
   header('location:cart.php');
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?")->execute([$qty, $cart_id]);
}

if(isset($_POST['update_size'])){
   $cart_id = $_POST['cart_id'];
   $size = $_POST['size'];
   $conn->prepare("UPDATE `cart` SET size = ? WHERE id = ?")->execute([$size, $cart_id]);
}

/* ✅ CHECK SIZE FOR CHECKOUT */
$size_missing = false;

$check = $conn->prepare("
SELECT cart.*, sub_categories.sub_category_name
FROM cart
LEFT JOIN products ON cart.pid = products.id
LEFT JOIN sub_categories ON products.sub_category_id = sub_categories.id
WHERE cart.user_id = ?
");
$check->execute([$user_id]);

while($row = $check->fetch(PDO::FETCH_ASSOC)){
   $sub = strtolower($row['sub_category_name']);

   if(($sub == 'top' || $sub == 'bottom' || $sub == 'footwear') && empty($row['size'])){
      $size_missing = true;
      break;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>shopping cart</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">

<style>
.size-box{
   display:flex;
   gap:10px;
   margin:10px 0;
}
.size-box select,
.size-box .option-btn{
   width:50%;
   height:40px;
}
</style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">

<h3 class="heading">shopping cart</h3>

<div class="box-container">

<?php
$grand_total = 0;

$select_cart = $conn->prepare("
SELECT cart.*, sub_categories.sub_category_name
FROM cart
LEFT JOIN products ON cart.pid = products.id
LEFT JOIN sub_categories ON products.sub_category_id = sub_categories.id
WHERE cart.user_id = ?
");
$select_cart->execute([$user_id]);

if($select_cart->rowCount() > 0){
while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
?>

<form action="" method="post" class="box">

<input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">

<img src="uploaded_img/<?= $fetch_cart['image']; ?>">

<div class="name"><?= $fetch_cart['name']; ?></div>

<?php
$sub = strtolower($fetch_cart['sub_category_name']);

if($sub == 'top' || $sub == 'bottom' || $sub == 'footwear'){
?>

<div class="size-box">

<select name="size" required>
   <option value="" disabled <?= empty($fetch_cart['size'])?'selected':''; ?>>
      Select Size
   </option>

   <?php
   if($sub == 'top' || $sub == 'bottom'){
      $sizes = ['S','M','L','XL','2XL'];
   } else {
      $sizes = ['6','7','8','9','10'];
   }

   foreach($sizes as $s){
      $selected = ($fetch_cart['size'] == $s) ? 'selected' : '';
      echo "<option value='$s' $selected>$s</option>";
   }
   ?>
</select>

<button type="submit" name="update_size" class="option-btn">
   Update
</button>

</div>

<?php } ?>

<div class="flex">
   <div class="price">₹<?= $fetch_cart['price']; ?>/-</div>

   <input type="number" name="qty" value="<?= $fetch_cart['quantity']; ?>" min="1" class="qty">

   <button type="submit" name="update_qty" class="fas fa-edit"></button>
</div>

<div class="sub-total">
₹<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>
</div>

<input type="submit" name="delete" value="delete item" class="delete-btn">

</form>

<?php
$grand_total += $sub_total;
}
}else{
echo '<p class="empty">your cart is empty</p>';
}
?>

</div>

<div class="cart-total">

<p>grand total : ₹<?= $grand_total; ?></p>

<?php
if($size_missing){
   echo "<p style='color:red;'>Please select size for all products!</p>";
} 
?>

<a href="shop.php" class="option-btn">continue shopping</a>

<a href="checkout.php"
class="btn <?= ($grand_total > 0 && !$size_missing)?'':'disabled'; ?>">
proceed to checkout
</a>
<!-- ✅ DELETE ALL FIXED -->
<a href="cart.php?delete_all"
class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>"
onclick="return confirm('delete all from cart?');">
delete all item
</a>

</div>

</section>

<?php include 'components/footer.php'; ?>

</body>
</html>