<?php

include 'connect.php';
session_start();

if(isset($_SESSION['user_id'])){

   $user_id = $_SESSION['user_id'];

   
   $update_status = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
   $update_status->execute([$user_id]);

}

session_unset();
session_destroy();

header('location:../home.php');
exit();

?>