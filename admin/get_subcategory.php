<?php
include '../components/connect.php';

if(isset($_POST['category_id'])){

   $category_id = $_POST['category_id'];

   $select = $conn->prepare("SELECT id, sub_category_name 
                             FROM sub_categories 
                             WHERE category_id = ?");
   $select->execute([$category_id]);

   if($select->rowCount() > 0){
      while($row = $select->fetch(PDO::FETCH_ASSOC)){
         echo '<option value="'.$row['id'].'">'.$row['sub_category_name'].'</option>';
      }
   }else{
      echo '<option value="">No Sub Category Found</option>';
   }
}
?>