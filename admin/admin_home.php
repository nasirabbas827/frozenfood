<?php
session_start();
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "admin") {
    header("location: admin_login.php");
    exit;
}

// Fetch total counts from the database
$sql_counts = "SELECT 
                (SELECT COUNT(*) FROM users WHERE usertype = 'buyer') AS total_buyers,
                              (SELECT COUNT(*) FROM products) AS total_products,
                (SELECT COUNT(*) FROM orders) AS total_orders,
                (SELECT COUNT(*) FROM reviews) AS total_reviews,
                (SELECT COUNT(*) FROM complaints) AS total_complaints";
$result = mysqli_query($conn, $sql_counts);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Buyers</h5>
                        <p class="card-text"><?php echo $row['total_buyers']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mt-2">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text"><?php echo $row['total_products']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mt-2">
                    <div class="card-body">
                        <h5 class="card-title">Total orders</h5>
                        <p class="card-text"><?php echo $row['total_orders']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mt-2">
                    <div class="card-body">
                        <h5 class="card-title">Total Reviews</h5>
                        <p class="card-text"><?php echo $row['total_reviews']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mt-2">
                    <div class="card-body">
                        <h5 class="card-title">Total Complaints</h5>
                        <p class="card-text"><?php echo $row['total_complaints']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS (Optional) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
