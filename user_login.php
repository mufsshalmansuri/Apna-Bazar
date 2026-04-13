<?php

include 'components/connect.php';
session_start();

$message = [];

// if(isset($_SESSION['user_id'])){
//    header('location:home.php');
//    exit();
// }

if (isset($_POST['submit'])) {

   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass = md5($_POST['pass']);

   $select_user = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $select_user->execute([$email]);

   if ($select_user->rowCount() > 0) {

      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      // PASSWORD CHECK
      if ($row['password'] != $pass) {

         $message[] = "Incorrect password!";

      } else {

         // EMAIL VERIFIED CHECK
         if ($row['status'] != 'active') {

            $message[] = "Please verify your email first!";

         } else {

            $_SESSION['user_id'] = $row['id'];

            if ($row['welcome_sent'] == 0) {

               require 'PHPMailer/src/PHPMailer.php';
               require 'PHPMailer/src/SMTP.php';
               require 'PHPMailer/src/Exception.php';

               $mail = new PHPMailer\PHPMailer\PHPMailer();

               $mail->isSMTP();
               $mail->Host = 'smtp.gmail.com';
               $mail->Port = 465;
               $mail->SMTPAuth = true;
               $mail->Username = 'mufsshalmansuri@gmail.com';
               $mail->Password = 'ffshcnrskkugvsdb';
               $mail->SMTPSecure = 'ssl';

               $mail->setFrom('mufsshalmansuri@gmail.com', 'Apna Bazar');
               $mail->addAddress($row['email'], $row['name']);
               $mail->isHTML(true);
               $mail->Subject = 'Welcome to Apna Bazar';

               $mail->Body = "
                  <h2>Welcome " . $row['name'] . " 🎉</h2>
                  <p>Your account is successfully verified.</p>
                  <p>Thank you for joining our ecommerce store.</p>
               ";

               if ($mail->send()) {
                  $update = $conn->prepare("UPDATE users SET welcome_sent = 1 WHERE id = ?");
                  $update->execute([$row['id']]);
               }
            }

            header('location:home.php');
            exit();

         }

      }

   } else {

      $message[] = "Email not registered!";

   }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">


      <form action="" method="post">
         <h3>Login Now</h3>
         <input type="email" name="email" required placeholder="Enter your email" class="box">
         <!-- <input type="password" name="pass" required placeholder="Enter your password" class="box"> -->
         <div class="password-box">
            <input type="password" name="pass" id="login_pass" required placeholder="Enter your password" class="box"
               minlength="6" maxlength="6">
            <i class="fas fa-eye toggle-pass" onclick="togglePassword('login_pass', this)"></i>
         </div>
         <input type="submit" value="Login Now" class="btn" name="submit">
         <p>Don't have an account?</p>
         <a href="user_register.php" class="option-btn">Register Now</a>
      </form>

   </section>

   <?php include 'components/footer.php'; ?>
   <script src="js/script.js"></script>
</body>

</html>