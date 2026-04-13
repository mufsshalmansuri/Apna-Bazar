<?php
include 'components/connect.php';
session_start();

if(isset($_GET['code'])){
    $code = $_GET['code'];

    if(isset($_POST['verify'])){

        $otp = $_POST['otp'];

        $select = $conn->prepare("SELECT * FROM users WHERE activation_code = ?");
        $select->execute([$code]);

        if($select->rowCount() > 0){

            $row = $select->fetch(PDO::FETCH_ASSOC);

            if($row['otp'] == $otp){

                $update = $conn->prepare("UPDATE users SET status='active', otp='' WHERE activation_code=?");
                $update->execute([$code]);

                echo "<script>alert('Email Verified Successfully'); window.location.href='user_login.php';</script>";

            } else {
                echo "<script>alert('Invalid OTP');</script>";
            }

        } else {
            echo "<script>alert('Invalid Activation Code');</script>";
        }
    }

} else {
    header('location:register.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Email Verification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Email Verification</h3>
      <input type="text" name="otp" required placeholder="Enter OTP" maxlength="6" class="box">
      <input type="submit" value="Verify" name="verify" class="btn">
   </form>

</section>

<?php include 'components/footer.php'; ?>

</body>
</html>