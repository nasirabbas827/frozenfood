<?php
session_start();
include('config.php');
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}
// Check if order ID is provided in the URL
if (!isset($_GET['order_id'])) {
    header("location: index.php"); // Redirect to homepage or any other page
    exit;
}

$order_id = $_GET['order_id'];

// Fetch order details from the database
$sql_order_details = "SELECT * FROM orders WHERE OrderID = $order_id";
$result_order_details = mysqli_query($conn, $sql_order_details);
$order_details = mysqli_fetch_assoc($result_order_details);

// Check if order exists
if (!$order_details) {
    header("location: index.php"); // Redirect to homepage or any other page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Thank you for your order!</h4>
                    <p>Your order has been placed successfully. Your order ID is: <?php echo $order_id; ?></p>
                    <hr>
                    <p class="mb-0">Total Price: Pkr<?php echo number_format($order_details['TotalPrice'], 2); ?></p>
                    <p class="mb-0">Delivery Address: <?php echo $order_details['DeliveryAddress']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
