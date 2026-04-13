<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}

$message = [];

if(isset($_POST['update'])){

   $pid = $_POST['pid'];

   $name = $_POST['name'];
   $price = $_POST['price'];
   $details = $_POST['details'];

   $category_id = $_POST['category_id'] ?? null;
   $sub_category_id = $_POST['sub_category_id'] ?? null;

   $update = $conn->prepare("UPDATE products 
      SET name=?, price=?, details=?, category_id=?, sub_category_id=? 
      WHERE id=?");
   $update->execute([$name,$price,$details,$category_id,$sub_category_id,$pid]);

   $message[] = "Product Updated Successfully!";

   function updateImage($conn,$pid,$file_input,$column_name,$old_image){

      if(!empty($_FILES[$file_input]['name'])){

         $new_name = time().'_'.$_FILES[$file_input]['name'];
         $tmp = $_FILES[$file_input]['tmp_name'];
         $path = '../uploaded_img/'.$new_name;

         move_uploaded_file($tmp,$path);

         $conn->prepare("UPDATE products SET $column_name=? WHERE id=?")
              ->execute([$new_name,$pid]);

         if(!empty($old_image)){
            $file_path = '../uploaded_img/'.$old_image;

            if(file_exists($file_path) && is_file($file_path)){
               @unlink($file_path);
            }
         }
      }
   }

   updateImage($conn,$pid,'image_01','image_01',$_POST['old_image_01']);
   updateImage($conn,$pid,'image_02','image_02',$_POST['old_image_02']);
   updateImage($conn,$pid,'image_03','image_03',$_POST['old_image_03']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Update Product</title>
<link rel="stylesheet" href="../css/admin_style.css">
</head>

<body>

<?php include '../components/admin_header.php'; ?>



<section class="update-product">

<h1 class="heading">Update Product</h1>

<?php
$update_id = $_GET['update'] ?? null;

if($update_id){

$select = $conn->prepare("SELECT * FROM products WHERE id=?");
$select->execute([$update_id]);

if($select->rowCount() > 0){
$row = $select->fetch(PDO::FETCH_ASSOC);
?>

<form action="" method="post" enctype="multipart/form-data">

<input type="hidden" name="pid" value="<?= $row['id']; ?>">
<input type="hidden" name="old_image_01" value="<?= $row['image_01']; ?>">
<input type="hidden" name="old_image_02" value="<?= $row['image_02']; ?>">
<input type="hidden" name="old_image_03" value="<?= $row['image_03']; ?>">

<div class="flex">

<div class="inputBox">
<span>Name</span>
<input type="text" name="name" value="<?= $row['name']; ?>" required class="box">
</div>

<div class="inputBox">
<span>Category</span>
<select name="category_id" id="category" class="box" required>

<?php
$cats = $conn->query("SELECT * FROM categories");
while($cat = $cats->fetch(PDO::FETCH_ASSOC)){

   $selected = ($cat['id']==$row['category_id']) ? 'selected' : '';

   echo "<option value='{$cat['id']}' data-name='{$cat['category_name']}' $selected>
   {$cat['category_name']}
   </option>";
}
?>

</select>
</div>

<div class="inputBox" id="subBox">
<span>Sub Category</span>

<select name="sub_category_id" class="box" placeholder="select sub_category">

<!-- <option value="">Select Sub Category</option> -->

<?php
$subs = $conn->prepare("SELECT * FROM sub_categories WHERE category_id=?");
$subs->execute([$row['category_id']]);

while($sub = $subs->fetch(PDO::FETCH_ASSOC)){

$selected = ($sub['id']==$row['sub_category_id']) ? 'selected' : '';

echo "<option value='{$sub['id']}' $selected>
{$sub['sub_category_name']}
</option>";

}
?>

</select>

</div>

<div class="inputBox">
<span>Price</span>
<input type="number" name="price" value="<?= $row['price']; ?>" required class="box">
</div>

<div class="inputBox">
<span>Details</span>
<textarea name="details" required class="box"><?= $row['details']; ?></textarea>
</div>

<div class="inputBox">
<span>Image 1</span>
<img src="../uploaded_img/<?= $row['image_01']; ?>" width="100">
<input type="file" name="image_01" class="box">
</div>

<div class="inputBox">
<span>Image 2</span>
<img src="../uploaded_img/<?= $row['image_02']; ?>" width="100">
<input type="file" name="image_02" class="box">
</div>

<div class="inputBox">
<span>Image 3</span>
<img src="../uploaded_img/<?= $row['image_03']; ?>" width="100">
<input type="file" name="image_03" class="box">
</div>

</div>

<input type="submit" name="update" value="Update Product" class="btn">

</form>

<?php
}else{
echo "<p style='text-align:center;'>Product Not Found</p>";
}
}
?>

</section>

<script>

let category = document.getElementById("category");
let subBox = document.getElementById("subBox");

function toggleSubCategory(){

let selectedText = category.options[category.selectedIndex].text.toLowerCase();

if(selectedText == "men" || selectedText == "women" || selectedText == "kids"){

   subBox.style.display = "block";

}else{

   subBox.style.display = "none";

}

}

toggleSubCategory();

category.addEventListener("change",toggleSubCategory);

</script>

</body>
</html>