<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

// Check if product ID is provided
if (!isset($_GET['product_id'])) {
    header("location: buyer_home.php");
    exit;
}

$product_id = $_GET['product_id'];

// Fetch reviews and ratings for the product
$sql_reviews = "SELECT u.Username, r.Comment, r.Rating, r.Image 
                FROM orders o 
                INNER JOIN users u ON o.UserID = u.id 
                INNER JOIN order_items oi ON o.OrderID = oi.OrderID 
                INNER JOIN reviews r ON oi.OrderID = r.OrderID 
                WHERE oi.ProductID = $product_id";

$result_reviews = mysqli_query($conn, $sql_reviews);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .fa-star{
            color:gold;
        }
    </style>
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="mb-4">Product Reviews</h2>
        <?php if (mysqli_num_rows($result_reviews) > 0) : ?>
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result_reviews)) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if (!empty($row['Image'])) : ?>
                                <img class="card-img-top" src="uploads/<?php echo $row['Image']; ?>" alt="Review Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">User <?php echo $row['Username']; ?> Review</h5>
                                <p class="card-text">
                                    <strong>Rating:</strong> 
                                    <?php 
                                    $rating = intval($row['Rating']);
                                    for ($i = 0; $i < $rating; $i++) {
                                        echo '<span class="fa fa-star checked"></span>';
                                    }
                                    ?>
                                </p>
                                <p class="card-text"><strong>Comment:</strong> <?php echo $row['Comment']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info" role="alert">No reviews found for this product.</div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
