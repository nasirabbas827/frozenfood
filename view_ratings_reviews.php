<?php
session_start();
include('config.php');



// Check if product ID is provided
if (!isset($_GET['product_id'])) {
    header("location: index.php");
    exit;
}

$product_id = $_GET['product_id'];

// Fetch product details
$sql_product = "SELECT * FROM products WHERE ProductID = $product_id";
$result_product = mysqli_query($conn, $sql_product);

// Fetch approved reviews and ratings for the product
$sql_reviews = "SELECT u.Username, r.Comment, r.Rating, r.Image 
                FROM orders o 
                INNER JOIN users u ON o.UserID = u.id 
                INNER JOIN order_items oi ON o.OrderID = oi.OrderID 
                INNER JOIN reviews r ON oi.OrderID = r.OrderID 
                WHERE oi.ProductID = $product_id AND r.Status = 'approved'";

$result_reviews = mysqli_query($conn, $sql_reviews);

// Check if the product exists
if (mysqli_num_rows($result_product) == 0) {
    header("location: buyer_home.php");
    exit;
}

$product = mysqli_fetch_assoc($result_product);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['ProductName']; ?> - Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    <style>
 
        .product-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .product-details {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .fa-star {
            color: #ffc107;
        }
        .review-card {
            transition: transform 0.3s ease-in-out;
        }
        .review-card:hover {
            transform: translateY(-5px);
        }
        .review-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <!-- Product Details Section -->
        <div class="row g-4">
            <div class="col-md-6">
                <img src="./admin/products_images/<?php echo $product['ImageURL']; ?>" alt="<?php echo $product['ProductName']; ?>" class="product-image img-fluid w-100">
            </div>
            <div class="col-md-6">
                <div class="product-details h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h1 class="mb-4"><?php echo $product['ProductName']; ?></h1>
                        <p class="fs-4 text-primary fw-bold">PKR <?php echo number_format($product['Price'], 2); ?></p>
                        <p class="mb-3"><i class="fas fa-box me-2"></i> <strong>Stock:</strong> <?php echo $product['StockQuantity']; ?> units</p>
                        <p class="mb-4"><i class="fas fa-info-circle me-2"></i> <strong>Description:</strong> <?php echo $product['Description']; ?></p>
                    </div>
                    <div>
                      
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <h2 class="mt-5 mb-4">Customer Reviews</h2>
        <?php if (mysqli_num_rows($result_reviews) > 0) : ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while ($row = mysqli_fetch_assoc($result_reviews)) : ?>
                    <div class="col">
                        <div class="card h-100 review-card">
                            <?php if (!empty($row['Image'])) : ?>
                                <img class="card-img-top review-image" src="buyer/uploads/<?php echo $row['Image']; ?>" alt="Review by <?php echo $row['Username']; ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['Username']; ?>'s Review</h5>
                                <p class="card-text">
                                    <?php 
                                    $rating = intval($row['Rating']);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </p>
                                <p class="card-text"><?php echo $row['Comment']; ?></p>
                            </div>
                            
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i> No reviews found for this product.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


