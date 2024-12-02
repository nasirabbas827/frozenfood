<?php
// start session and check if user is logged in as a buyer
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "buyer") {
    header("location: login.php");
    exit;
}

// Retrieve all categories
$sql_categories = "SELECT id, name FROM categories";
$result_categories = mysqli_query($conn, $sql_categories);

// Retrieve all products with seller information and category name
$sql_products = "SELECT p.*, c.name AS category_name FROM products p 
                JOIN categories c ON p.CategoryID = c.id";

// Initialize search variables
$search_query = "";
$category_id = "all";
$product_name = "";
$min_price = "";
$max_price = "";

// Construct the WHERE clause based on search parameters
$where_conditions = array();
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    if (!empty($search_query)) {
        $where_conditions[] = "(p.ProductName LIKE '%$search_query%')";
    }
}

$category_id = $_GET['category'] ?? "all";
if ($category_id != "all") {
    $where_conditions[] = "p.CategoryID = $category_id";
}

if (isset($_GET['product_name'])) {
    $product_name = $_GET['product_name'];
    if (!empty($product_name)) {
        $where_conditions[] = "p.ProductName LIKE '%$product_name%'";
    }
}


if (isset($_GET['min_price'], $_GET['max_price'])) {
    $min_price = $_GET['min_price'];
    $max_price = $_GET['max_price'];
    if (!empty($min_price) && !empty($max_price)) {
        $where_conditions[] = "p.Price BETWEEN $min_price AND $max_price";
    }
}

// Add WHERE clause to SQL query if there are any search conditions
if (!empty($where_conditions)) {
    $sql_products .= " WHERE " . implode(" AND ", $where_conditions);
}

$result_products = mysqli_query($conn, $sql_products);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Buyer Home Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php
    include('buyer_navbar.php');
    ?>

    <!-- New Arrival Section -->
<div class="container mt-5">
    <h2 class="mb-4">New Arrivals</h2>
    <div id="new-arrivals-carousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php
            // Retrieve latest added products
            $sql_new_arrivals = "SELECT * FROM products ORDER BY Timestamp DESC LIMIT 5";
            $result_new_arrivals = mysqli_query($conn, $sql_new_arrivals);
            $products_count = mysqli_num_rows($result_new_arrivals);
            $active = true; // Set the first item as active

            // Display products in carousel format
            for ($i = 0; $i < $products_count; $i += 2) {
                ?>
                <div class="carousel-item <?php echo $active ? 'active' : ''; ?>">
                    <div class="row">
                        <?php
                        for ($j = $i; $j < min($i + 3, $products_count); $j++) {
                            mysqli_data_seek($result_new_arrivals, $j);
                            $row_new_arrival = mysqli_fetch_assoc($result_new_arrivals);
                            ?>
                            <div class="col-md-4">
                                <div class="card" style="height: 100%;">
                                    <img class="card-img-top" src="../admin/products_images/<?php echo $row_new_arrival['ImageURL']; ?>" alt="Product Image" height="200px">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $row_new_arrival['ProductName']; ?></h5>
                                        <p class="card-text"><?php echo number_format($row_new_arrival['Price'], 2); ?> Pkr</p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
                $active = false; // Set active to false after the first item
            }
            ?>
        </div>
        <a class="carousel-control-prev" href="#new-arrivals-carousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#new-arrivals-carousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>

    <div class="container mt-5">
        <h2 class="mb-4">Available Products</h2>

        <!-- Search form -->
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" placeholder="Search by product" name="search" value="<?php echo $search_query; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <select class="custom-select" name="category">
                        <option value="all" <?php if ($category_id == "all") echo "selected"; ?>>All Categories</option>
                        <?php while ($row_category = mysqli_fetch_assoc($result_categories)) : ?>
                            <option value="<?php echo $row_category['id']; ?>" <?php if ($row_category['id'] == $category_id) echo "selected"; ?>><?php echo $row_category['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <input type="text" class="form-control" placeholder="Search by product name" name="product_name" value="<?php echo $product_name; ?>">
                </div>
                <div class="col-md-1 mb-3">
                    <input type="number" class="form-control" placeholder="Min price" name="min_price" value="<?php echo $min_price; ?>">
                </div>
                <div class="col-md-1 mb-3">
                    <input type="number" class="form-control" placeholder="Max price" name="max_price" value="<?php echo $max_price; ?>">
                </div>
                <div class="col-md-1 mb-3">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </div>
        </form>

        <!-- Display products -->
        <?php if (mysqli_num_rows($result_products) > 0) : ?>
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result_products)) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img class="card-img-top" height="250px" src="../admin/products_images/<?php echo $row['ImageURL']; ?>" alt="Product Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['ProductName']; ?></h5>
                                <p class="card-text">Category: <?php echo $row['category_name']; ?></p>
                                <p class="card-text">Description: <?php echo $row['Description']; ?></p>
                                <p class="card-text">Quantity Available: <?php echo $row['StockQuantity']; ?></p>
                                <p class="card-text">Size Available: <?php echo $row['SizeGuide']; ?></p>
                                <p class="card-text"><strong>Price:</strong> <?php echo number_format($row['Price'], 2); ?> Pkr</p>
                                <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                    <input type="hidden" class="max-quantity" value="<?php echo $row['StockQuantity']; ?>">
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" class="form-control quantity" name="quantity" value="1" min="1">
                                    </div>
                                    <button type="submit" class="btn btn-primary add-to-cart-btn" name="add_to_cart">Add to Cart</button>
                                <a href="view_ratings_reviews.php?product_id=<?php echo $row['ProductID']; ?>" class="btn btn-primary">Reviews</a>

                                </form>
                        
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info" role="alert">No products found matching the search criteria.</div>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.add-to-cart-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    const quantityInput = form.querySelector('.quantity');
                    const maxQuantity = parseInt(form.querySelector('.max-quantity').value);
                    const enteredQuantity = parseInt(quantityInput.value);
                    if (enteredQuantity > maxQuantity) {
                        event.preventDefault(); // Prevent form submission
                        alert(`Quantity exceeds available stock (${maxQuantity}) for this product.`);
                    }
                });
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>

</html>