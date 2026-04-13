<?php
if(session_status() == PHP_SESSION_NONE){
   session_start();
}

include_once '../components/connect.php';   // changed include to include_once

if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
/* ===== ADMIN SESSION CHECK ===== */
if(isset($_SESSION['admin_id'])){
   $admin_id = $_SESSION['admin_id'];

   $select_profile = $conn->prepare("SELECT name FROM admins WHERE id = ?");
   $select_profile->execute(array($admin_id));  // changed [] to array()

   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

   if(!$fetch_profile){
      $fetch_profile = array('name' => 'Admin');  // changed [] to array()
   }

}else{
   $fetch_profile = array('name' => 'Guest');   // changed [] to array()
}
?>

<header class="header">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

   <section class="flex">

      <a href="../admin/dashboard.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="../admin/dashboard.php">home</a>
         <a href="../admin/products.php">products</a>
         <a href="../admin/placed_orders.php">orders</a>
         <a href="../admin/admin_accounts.php">admins</a>
         <a href="../admin/users_accounts.php">users</a>
         <a href="../admin/messages.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
         <a href="../admin/update_profile.php" class="btn">update profile</a>
         <div class="flex-btn">
            <a href="../admin/register_admin.php" class="option-btn">register</a>
            <a href="../admin/admin_login.php" class="option-btn">login</a>
         </div>
         <a href="../components/admin_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">logout</a> 
      </div>

   </section>

</header>