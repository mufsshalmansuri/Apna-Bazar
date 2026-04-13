<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

/* FETCH USER DATA */
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

   $old_pass = md5($_POST['old_pass']);
   $db_pass = $fetch_profile['password'];

   $new_pass = md5($_POST['new_pass']);
   $cpass = md5($_POST['cpass']);

   /* PASSWORD CHANGE KARNA HAI YA NAHI */
   if(!empty($_POST['old_pass'])){

      if($old_pass != $db_pass){
         $message[] = 'old password not matched!';
      }
      elseif($new_pass != $cpass){
         $message[] = 'confirm password not matched!';
      }
      else{

         $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass->execute([$new_pass, $user_id]);

         /* NAME UPDATE */
         $update_profile = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
         $update_profile->execute([$name, $user_id]);

         $message[] = 'profile updated successfully!';
      }

   }else{

      /* SIRF NAME UPDATE */
      $update_profile = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
      $update_profile->execute([$name, $user_id]);

      $message[] = 'profile updated successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>update now</h3>

      <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box" value="<?= $fetch_profile["name"]; ?>">

      <!-- EMAIL SIRF SHOW HOGA -->
      <input type="email" value="<?= $fetch_profile["email"]; ?>" class="box" disabled>

     <div class="password-box">
   <input type="password" name="old_pass" id="old_pass" placeholder="enter your old password" class="box" minlength="6"
  maxlength="6">
   <i class="fas fa-eye toggle-pass" onclick="togglePassword('old_pass', this)"></i>
</div>

<div class="password-box">
   <input type="password" name="new_pass" id="new_pass" placeholder="enter your new password" class="box" minlength="6"
  maxlength="6">
   <i class="fas fa-eye toggle-pass" onclick="togglePassword('new_pass', this)"></i>
</div>

<div class="password-box">
   <input type="password" name="cpass" id="cpass" placeholder="confirm your new password" class="box" minlength="6"
  maxlength="6">
   <i class="fas fa-eye toggle-pass" onclick="togglePassword('cpass', this)"></i>
</div>

      <input type="submit" value="update now" class="btn" name="submit">

   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>