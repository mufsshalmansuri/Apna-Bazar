<?php

include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

<style>
.filter-box{
   margin:20px;
   display:flex;
   gap:10px;
}
</style>

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Our Products</h1>

   <!-- FILTER SECTION -->
   <form method="GET" class="filter-box">

      <!-- CATEGORY FILTER -->
      <select name="category" id="category">
         <option value="">All Categories</option>
         <option value="1" <?= (isset($_GET['category']) && $_GET['category']=="1") ? 'selected' : ''; ?>>Home Decor</option>
         <option value="3" <?= (isset($_GET['category']) && $_GET['category']=="3") ? 'selected' : ''; ?>>Smart Things</option>
         <option value="2" <?= (isset($_GET['category']) && $_GET['category']=="2") ? 'selected' : ''; ?>>Electronics</option>
         <option value="4" <?= (isset($_GET['category']) && $_GET['category']=="4") ? 'selected' : ''; ?>>Kids</option>
         <option value="5" <?= (isset($_GET['category']) && $_GET['category']=="5") ? 'selected' : ''; ?>>Women</option>
         <option value="6" <?= (isset($_GET['category']) && $_GET['category']=="6") ? 'selected' : ''; ?>>Men</option>
      </select>

      <!-- SUB CATEGORY -->
      <select name="sub_category" id="sub_category" style="display:none;">

<option value="">Sub Category</option>
<option value="1">Top</option>
<option value="2">Bottom</option>
<option value="3">Footwear</option>

</select>

      <!-- PRICE SORT -->
      <select name="sort">
         <option value="">Default Sorting</option>
         <option value="low" <?= (isset($_GET['sort']) && $_GET['sort']=="low") ? 'selected' : ''; ?>>Price: Low to High</option>
         <option value="high" <?= (isset($_GET['sort']) && $_GET['sort']=="high") ? 'selected' : ''; ?>>Price: High to Low</option>
      </select>

      <input type="submit" value="Apply" class="btn">
   </form>

   <div class="box-container">

   <?php

      $query = "SELECT * FROM products WHERE 1";
      $params = [];

      // CATEGORY FILTER
      if(isset($_GET['category']) && $_GET['category'] != ''){
         $query .= " AND category_id = ?";
         $params[] = $_GET['category'];
      }

      // SUB CATEGORY FILTER
      if(isset($_GET['sub_category']) && $_GET['sub_category'] != ''){
         $query .= " AND sub_category_id = ?";
         $params[] = $_GET['sub_category'];
      }

      // PRICE SORT
      if(isset($_GET['sort'])){
         if($_GET['sort'] == "low"){
            $query .= " ORDER BY price ASC";
         }elseif($_GET['sort'] == "high"){
            $query .= " ORDER BY price DESC";
         }
      }

      $select_products = $conn->prepare($query);
      $select_products->execute($params);

      if($select_products->rowCount() > 0){
         while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>

   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">

      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>

      <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
      <div class="name"><?= $fetch_product['name']; ?></div>

      <div class="flex">
         <div class="price"><span>₹</span><?= $fetch_product['price']; ?><span>/-</span></div>
         <input type="number" name="qty" class="qty" min="1" max="99" value="1">
      </div>

      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
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

<script src="js/script.js"></script>

<script>

let category = document.getElementById("category");
let subCategory = document.getElementById("sub_category");

function checkCategory(){

   let value = category.value;

   if(value == "4" || value == "5" || value == "6"){
      subCategory.style.display = "block";

      if(value == "6"){ // Men
         subCategory.innerHTML = `
            <option value="">Sub Category</option>
            <option value="7">Top</option>
            <option value="8">Bottom</option>
            <option value="9">Footwear</option>
         `;
      }
      else if(value == "5"){ // Women
         subCategory.innerHTML = `
            <option value="">Sub Category</option>
            <option value="4">Top</option>
            <option value="5">Bottom</option>
            <option value="6">Footwear</option>
         `;
      }
      else if(value == "4"){ // Kids
         subCategory.innerHTML = `
            <option value="">Sub Category</option>
            <option value="1">Top</option>
            <option value="2">Bottom</option>
            <option value="3">Footwear</option>
         `;
      }

   }else{
      subCategory.style.display = "none";
      subCategory.innerHTML = `<option value="">Sub Category</option>`;
   }

}

category.addEventListener("change", checkCategory);
window.onload = checkCategory;

</script>



</body>
</html>