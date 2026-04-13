<?php

include 'components/connect.php';
session_start();

$otp_str = str_shuffle("0123456789");
$otp = substr($otp_str, 0, 6);

$act_str = rand(100000, 10000000);
$activation_code = str_shuffle("abcdefghijklmno" . $act_str);

if (isset($_POST['Register'])) {

   $name = $_POST['name'];
   $email = $_POST['email'];
   $gender = $_POST['gender'];

   $pass = md5($_POST['pass']);
   $cpass = md5($_POST['cpass']);

   // PASSWORD MATCH CHECK
   if ($pass != $cpass) {
      echo "<script>alert('Password and Confirm Password do not match')</script>";
   } else {

      // Check email
      $selectDatabase = $conn->prepare("SELECT * FROM users WHERE email = ?");
      $selectDatabase->execute([$email]);

      if ($selectDatabase->rowCount() > 0) {

         $selectRow = $selectDatabase->fetch(PDO::FETCH_ASSOC);
         $status = $selectRow['status'];

         if ($status == 'active') {

            echo "<script>alert('Email already registered')</script>";

         } else {

            $sqlupdate = $conn->prepare("UPDATE users 
                                      SET name=?, password=?, gender=?, otp=?, activation_code=? 
                                      WHERE email=?");

            $updateResult = $sqlupdate->execute([$name, $pass, $gender, $otp, $activation_code, $email]);

            if ($updateResult) {

               require 'PHPMailer/src/PHPMailer.php';
               require 'PHPMailer/src/SMTP.php';
               require 'PHPMailer/src/Exception.php';

               $mail = new PHPMailer\PHPMailer\PHPMailer();

               $mail->isSMTP();
               $mail->Host = 'smtp.gmail.com';
               $mail->Port = 465;
               $mail->SMTPAuth = true;
               $mail->Username = 'mufsshalmansuri@gmail.com';
               $mail->Password = '';//enter your email password
               $mail->SMTPSecure = 'ssl';

               $mail->setFrom('mufsshalmansuri@gmail.com', 'Mufsshal Mansuri');
               $mail->addAddress($email, $name);
               $mail->isHTML(true);
               $mail->Subject = 'Verification code for Verify Your Email Address';

               $mail->Body = "<p>For Verify your email address, enter this verification code: <b>" . $otp . "</b></p>";

               if ($mail->send()) {
                  echo '<script>alert("Please Check Your Email for Verification Code")</script>';
                  header('location:email_varify.php?code=' . $activation_code);
                  exit();
               } else {
                  echo $mail->ErrorInfo;
               }
            }
         }

      } else {

         $sqlInsert = $conn->prepare("INSERT INTO users(name, email, password, gender, otp, activation_code, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'inactive')");

         $insertResult = $sqlInsert->execute([$name, $email, $pass, $gender, $otp, $activation_code]);

         if ($insertResult) {

            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';
            require 'PHPMailer/src/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer();

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 465;
            $mail->SMTPAuth = true;
            $mail->Username = 'mufsshalmansuri@gmail.com';
            $mail->Password = '';//enter your email password
            $mail->SMTPSecure = 'ssl';

            $mail->setFrom('mufsshalmansuri@gmail.com', 'Mufsshal Mansuri');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Verification code for Verify Your Email Address';

            $mail->Body = "<p> For Verify your email address, enter this verification code: <b>" . $otp . "</b></p>";

            if ($mail->send()) {
               echo '<script>alert("Please Check Your Email for Verification Code")</script>';
               header('location:email_varify.php?code=' . $activation_code);
               exit();
            } else {
               echo $mail->ErrorInfo;
            }

         } else {
            echo '<script>alert("Oops something went wrong, failed to insert data")</script>';
         }
      }

   }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>register now</h3>

         <input type="text" name="name" required placeholder="enter your username" maxlength="20" class="box">

         <input type="email" name="email" required placeholder="enter your email" maxlength="50" class="box" pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$"
       title="Enter valid email (example: abc@gmail.com)">
         <!-- <p>Select Gender</p> -->
         <div class="gender-box">
            <label><input type="radio" name="gender" value="Male" required> Male</label>
            <label><input type="radio" name="gender" value="Female"> Female</label>

         </div>
         <div class="password-box">
            <input type="password" id="pass" name="pass" required placeholder="enter your new password" class="box" minlength="6"
               maxlength="6">
            <i class="fas fa-eye toggle-pass" onclick="togglePassword('pass', this)"></i>
         </div>

         <div class="password-box">
            <input type="password" id="cpass" name="cpass" required placeholder="confirm your new password" class="box"
               minlength="6" maxlength="6">
            <i class="fas fa-eye toggle-pass" onclick="togglePassword('cpass', this)"></i>
         </div>
         <!-- <input type="password" name="pass" required placeholder="enter your password" maxlength="20" class="box">

<input type="password" name="cpass" required placeholder="confirm your password" maxlength="20" class="box"> -->

         <input type="submit" value="register now" class="btn" name="Register">

         <p>already have an account?</p>

         <a href="user_login.php" class="option-btn">login now</a>

      </form>

   </section>



   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>