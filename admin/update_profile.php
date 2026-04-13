<?php
include '../components/connect.php';
session_start();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit();
}

$admin_id = $_SESSION['admin_id'];
$message = [];

/* ===== FETCH ADMIN DATA ===== */

$select_profile = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

/* ===== UPDATE LOGIC ===== */

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $old_pass_input = $_POST['old_pass'];
   $new_pass_input = $_POST['new_pass'];
   $confirm_pass_input = $_POST['confirm_pass'];

   $error = false;
   $updated = false;

   /* ===== PASSWORD UPDATE ===== */

   if(!empty($old_pass_input) || !empty($new_pass_input) || !empty($confirm_pass_input)){

      $old_pass = sha1($old_pass_input);
      $new_pass = sha1($new_pass_input);
      $confirm_pass = sha1($confirm_pass_input);

      if($old_pass != $fetch_profile['password']){
         $message[] = 'Old password is incorrect!';
         $error = true;
      }
      elseif($new_pass != $confirm_pass){
         $message[] = 'Confirm password does not match!';
         $error = true;
      }
      else{
         $update_pass = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
         $update_pass->execute([$new_pass, $admin_id]);
         $updated = true;
      }
   }

   /* ===== NAME UPDATE ===== */

   if(!$error){
      $update_name = $conn->prepare("UPDATE admins SET name = ? WHERE id = ?");
      $update_name->execute([$name, $admin_id]);
      $updated = true;
   }

   /* ===== SINGLE MESSAGE ===== */

   if($updated && !$error){
      $message[] = 'Profile updated successfully!';
   }

   /* ===== REFRESH DATA ===== */

   $select_profile->execute([$admin_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Profile</title>
<link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">



<form action="" method="post">

<h3>Update Profile</h3>

<input type="text"
name="name"
value="<?= htmlspecialchars($fetch_profile['name']); ?>"
maxlength="50"
class="box"
placeholder="Enter your name"
required>

<div class="password-box">
<input type="password"
id="old_pass"
name="old_pass"
placeholder="Enter old password (leave blank if not changing)"
minlength="6"
  maxlength="6"
class="box">
<i class="fas fa-eye toggle-pass" onclick="togglePassword('old_pass', this)"></i>
</div>

<div class="password-box">
<input type="password"
id="new_pass"
name="new_pass"
placeholder="Enter new password"
minlength="6"
  maxlength="6"
class="box">
<i class="fas fa-eye toggle-pass" onclick="togglePassword('new_pass', this)"></i>
</div>

<div class="password-box">
<input type="password"
id="confirm_pass"
name="confirm_pass"
placeholder="Confirm new password"
minlength="6"
  maxlength="6"
class="box">
<i class="fas fa-eye toggle-pass" onclick="togglePassword('confirm_pass', this)"></i>
</div>
<!-- <input type="password"
name="old_pass"
placeholder="Enter old password (leave blank if not changing)"
maxlength="50"
class="box">

<input type="password"
name="new_pass"
placeholder="Enter new password"
maxlength="50"
class="box">

<input type="password"
name="confirm_pass"
placeholder="Confirm new password"
maxlength="50"
class="box"> -->

<input type="submit"
value="Update Now"
class="btn"
name="submit">

</form>

</section>

<script src="../js/admin_script.js"></script>
</body>
</html>