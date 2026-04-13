<?php

include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

include 'components/wishlist_cart.php';

if(isset($_GET['category']) && is_numeric($_GET['category'])){
   $category_id = $_GET['category'];
}else{
   header('location:home.php');
   exit();
}

// GET CATEGORY NAME
$select_category = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$select_category->execute([$category_id]);

if($select_category->rowCount() == 0){
   header('location:home.php');
   exit();
}

$fetch_category = $select_category->fetch(PDO::FETCH_ASSOC);
$category_name = $fetch_category['category_name'];

?>

<!DOCTYPE html>
<html>
<head>
   <title><?= htmlspecialchars($category_name); ?> Products</title>
   <link rel="stylesheet" href="css/style.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="products">

<h1 class="heading"><?= htmlspecialchars($category_name); ?> Products</h1>

<div class="box-container">

<?php

//  CHECK IF THIS CATEGORY HAS SUBCATEGORIES
$check_sub = $conn->prepare("SELECT * FROM sub_categories WHERE category_id = ?");
$check_sub->execute([$category_id]);

if($check_sub->rowCount() > 0){

   //  CATEGORY HAS SUBCATEGORIES (Men, Women, Kids)

   if(isset($_GET['sub']) && is_numeric($_GET['sub'])){
      $sub_id = $_GET['sub'];

      $select_products = $conn->prepare("SELECT * FROM products WHERE sub_category_id = ?");
      $select_products->execute([$sub_id]);

   }else{

      // Show all products of that category if no sub selected
      $select_products = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
      $select_products->execute([$category_id]);
   }

}else{

   //  CATEGORY HAS NO SUBCATEGORY (Home Decor, Electronics, etc.)
   $select_products = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
   $select_products->execute([$category_id]);
}

// DISPLAY PRODUCTS
if($select_products->rowCount() > 0){
   while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
?>

<form action="" method="post" class="box">
   <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
   <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name']); ?>">
   <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
   <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">

   <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
   <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>

   <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
   <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>

   <div class="flex">
      <div class="price"><span>₹</span><?= $fetch_product['price']; ?><span>/-</span></div>
      <input type="number" name="qty" class="qty" min="1" max="99" value="1">
   </div>

   <input type="submit" value="add to cart" class="btn" name="add_to_cart">
</form>

<?php
   }
}else{
   echo '<p class="empty">No products found!</p>';
}
?>

</div>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>