<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}

$message = [];

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $price = $_POST['price'];
   $details = $_POST['details'];
   $category_id = $_POST['category_id'];
   $sub_category_id = $_POST['sub_category_id'];

   $image_01 = $_FILES['image_01']['name'];
   $image_02 = $_FILES['image_02']['name'];
   $image_03 = $_FILES['image_03']['name'];

   $tmp_01 = $_FILES['image_01']['tmp_name'];
   $tmp_02 = $_FILES['image_02']['tmp_name'];
   $tmp_03 = $_FILES['image_03']['tmp_name'];

   if(empty($category_id)){
      $message[] = "Please select category!";
   }else{

      // Subcategory required only for Kids(4), Women(5), Men(6)
      if(($category_id == 4 || $category_id == 5 || $category_id == 6) 
         && empty($sub_category_id)){

         $message[] = "Please select sub category!";

      }else{

         if(empty($sub_category_id)){
            $sub_category_id = 0;
         }

         $insert = $conn->prepare("INSERT INTO products
         (name, details, price, image_01, image_02, image_03, category_id, sub_category_id)
         VALUES(?,?,?,?,?,?,?,?)");

         $success = $insert->execute([
            $name,
            $details,
            $price,
            $image_01,
            $image_02,
            $image_03,
            $category_id,
            $sub_category_id
         ]);

         if($success){

            if(!empty($image_01)){
               move_uploaded_file($tmp_01, '../uploaded_img/'.$image_01);
            }
            if(!empty($image_02)){
               move_uploaded_file($tmp_02, '../uploaded_img/'.$image_02);
            }
            if(!empty($image_03)){
               move_uploaded_file($tmp_03, '../uploaded_img/'.$image_03);
            }

            $message[] = "Product Added Successfully!";
         }else{
            $message[] = "Product Not Added!";
         }
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $conn->prepare("DELETE FROM products WHERE id=?")->execute([$delete_id]);
   header('location:products.php');
   exit();
}
?>
<!DOCTYPE html>
<html>
<head>
   <title>Products</title>
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">
<h1 class="heading">Add Product</h1>

<form action="" method="post" enctype="multipart/form-data">
<div class="flex">

<div class="inputBox">
<span>Product Name</span>
<input type="text" name="name" required class="box">
</div>

<div class="inputBox">
<span>Parent Category</span>
<select name="category_id" id="category" required class="box">
<option value="">Select Category</option>
<?php
$select_cat = $conn->prepare("SELECT * FROM categories");
$select_cat->execute();
while($cat = $select_cat->fetch(PDO::FETCH_ASSOC)){
   echo '<option value="'.$cat['id'].'">'.$cat['category_name'].'</option>';
}
?>
</select>
</div>

<div class="inputBox">
<span>Sub Category</span>
<select name="sub_category_id" id="sub_category" class="box">
<option value="">Not Required</option>
</select>
</div>

<div class="inputBox">
<span>Price</span>
<input type="number" name="price" required class="box">
</div>

<div class="inputBox">
<span>Image 1</span>
<input type="file" name="image_01" required class="box">
</div>

<div class="inputBox">
<span>Image 2</span>
<input type="file" name="image_02" required class="box">
</div>

<div class="inputBox">
<span>Image 3</span>
<input type="file" name="image_03" required class="box">
</div>

<div class="inputBox">
<span>Details</span>
<textarea name="details" required class="box"></textarea>
</div>

</div>

<input type="submit" value="Add Product" name="add_product" class="btn">
</form>
</section>

<section class="show-products">
<h1 class="heading">Products Added</h1>

<div class="box-container">

<?php
$select = $conn->prepare("
SELECT products.*, 
       categories.category_name, 
       sub_categories.sub_category_name
FROM products
LEFT JOIN categories ON products.category_id = categories.id
LEFT JOIN sub_categories ON products.sub_category_id = sub_categories.id
ORDER BY products.id DESC
");
$select->execute();

if($select->rowCount() > 0){
   while($row = $select->fetch(PDO::FETCH_ASSOC)){
?>

<div class="box">
<img src="../uploaded_img/<?= $row['image_01']; ?>" width="100%">
<div class="name"><?= $row['name']; ?></div>

<div>
Category: <?= $row['category_name']; ?> 
<?php if(!empty($row['sub_category_name'])){ ?>
(<?= $row['sub_category_name']; ?>)
<?php } ?>
</div>

<div class="price">₹<?= $row['price']; ?></div>
<!-- <div><?= $row['details']; ?></div> -->

<div class="flex-btn">
   <a href="update_product.php?update=<?= $row['id']; ?>" class="option-btn">Update</a>
   <a href="products.php?delete=<?= $row['id']; ?>" 
      class="delete-btn" 
      onclick="return confirm('Delete this product?');">Delete</a>
</div>

</div>

<?php
   }
}else{
   echo "<p style='text-align:center;'>No Products Added Yet!</p>";
}
?>

</div>
</section>

<script>
var categorySelect = document.getElementById('category');
var subCategorySelect = document.getElementById('sub_category');

categorySelect.addEventListener('change', function(){

   var category_id = this.value;

   if(category_id == 4 || category_id == 5 || category_id == 6){

      subCategorySelect.required = true;

      var xhr = new XMLHttpRequest();
      xhr.open("POST","get_subcategory.php",true);
      xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

      xhr.onload = function(){
         subCategorySelect.innerHTML =
         '<option value="">Select Sub Category</option>' + this.responseText;
      }

      xhr.send("category_id="+category_id);

   }else{

      subCategorySelect.required = false;
      subCategorySelect.innerHTML =
      '<option value="">Not Required</option>';

   }
});
</script>

</body>
</html>