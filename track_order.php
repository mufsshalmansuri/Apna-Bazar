<?php

include 'components/connect.php';

session_start();

$status = "";

if(isset($_POST['track'])){

$order_id = $_POST['order_id'];

$select = $conn->prepare("SELECT * FROM orders WHERE order_id=?");
$select->execute([$order_id]);

if($select->rowCount() > 0){

$order = $select->fetch(PDO::FETCH_ASSOC);

$status = $order['status'];

}else{

$status = "not_found";

}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Track Order</title>

<!-- font awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<!-- project css -->
<link rel="stylesheet" href="css/style.css">

<style>

.track-section{
max-width:800px;
margin:50px auto;
background:#fff;
padding:40px;
border-radius:10px;
box-shadow:0 5px 20px rgba(0,0,0,0.1);
text-align:center;
}

.track-section h2{
font-size:28px;
margin-bottom:20px;
}

.track-section form{
margin-bottom:30px;
}

.track-section input{
width:70%;
padding:12px;
border:1px solid #ccc;
border-radius:5px;
}

.track-section button{
padding:12px 25px;
background:#27ae60;
border:none;
color:#fff;
border-radius:5px;
cursor:pointer;
}

.track-section button:hover{
background:#219150;
}

.progress-bar{
display:flex;
justify-content:space-between;
margin-top:50px;
position:relative;
}

.step{
width:25%;
text-align:center;
position:relative;
}

.circle{
width:45px;
height:45px;
border-radius:50%;
background:#ccc;
margin:auto;
line-height:45px;
color:#fff;
font-weight:bold;
}

.step p{
margin-top:10px;
font-size:14px;
}

.line{
position:absolute;
top:22px;
left:-50%;
width:100%;
height:4px;
background:#ccc;
z-index:-1;
}

.active .circle{
background:#27ae60;
}

.active .line{
background:#27ae60;
}

.status-text{
margin-top:30px;
font-size:18px;
font-weight:bold;
color:#27ae60;
}

.not-found{
margin-top:20px;
color:red;
font-size:18px;
}

</style>

</head>

<body>

<?php include 'components/user_header.php'; ?>

<section class="track-section">

<h2>Track Your Order</h2>

<form method="POST">

<input type="text" name="order_id" placeholder="Enter Order ID" required>

<button name="track">Track</button>

</form>

<?php if($status && $status != "not_found"){ ?>

<div class="progress-bar">

<div class="step <?= ($status=="Pending" || $status=="Processing" || $status=="Shipped" || $status=="Delivered") ? 'active' : '' ?>">

<div class="circle">1</div>
<p>Order Placed</p>

</div>

<div class="step <?= ($status=="Processing" || $status=="Shipped" || $status=="Delivered") ? 'active' : '' ?>">

<div class="line"></div>
<div class="circle">2</div>
<p>Processing</p>

</div>

<div class="step <?= ($status=="Shipped" || $status=="Delivered") ? 'active' : '' ?>">

<div class="line"></div>
<div class="circle">3</div>
<p>Shipped</p>

</div>

<div class="step <?= ($status=="Delivered") ? 'active' : '' ?>">

<div class="line"></div>
<div class="circle">4</div>
<p>Delivered</p>

</div>

</div>

<div class="status-text">

Current Status : <?= $status; ?>

</div>

<?php } ?>

<?php if($status=="not_found"){ ?>

<div class="not-found">Order Not Found</div>

<?php } ?>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>