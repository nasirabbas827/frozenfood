<?php
// start session and check if user is logged in as a buyer
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>
      
        .card {
            transition: transform 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .carousel-item img {
            object-fit: cover;
            height: 300px;
        }
        .search-section {
            background: linear-gradient(135deg, #3498db, #2980b9);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .search-section h2 {
            color: white;
            margin-bottom: 1.5rem;
        }
        .search-form .form-control, .search-form .form-select {
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
        }
        .search-form .btn-primary {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: bold;
            background-color: #2c3e50;
            border: none;
            transition: all 0.3s ease;
        }
        .search-form .btn-primary:hover {
            background-color: #34495e;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-5">Welcome to Your Dashboard</h1>

        <!-- New Arrival Section -->
        <section class="mb-5">
            <h2 class="text-center mb-4">New Arrivals</h2>
            <div id="new-arrivals-carousel" class="carousel slide" data-bs-ride="carousel">
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
                                            <img class="card-img-top" src="../admin/products_images/<?php echo $row_new_arrival['ImageURL']; ?>" alt="Product Image" style="height: 200px; object-fit: cover;">
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
                <button class="carousel-control-prev" type="button" data-bs-target="#new-arrivals-carousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#new-arrivals-carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        <!-- Search Section -->
        <section class="search-section mb-5">
            <h2 class="text-center">Find Your Perfect Product</h2>
            <form method="GET" action="" class="search-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search by product" name="search" value="<?php echo $search_query; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-list"></i></span>
                            <select class="form-select" name="category">
                                <option value="all" <?php if ($category_id == "all") echo "selected"; ?>>All Categories</option>
                                <?php mysqli_data_seek($result_categories, 0); // Reset the pointer to the beginning of the result set
                                while ($row_category = mysqli_fetch_assoc($result_categories)) : ?>
                                    <option value="<?php echo $row_category['id']; ?>" <?php if ($row_category['id'] == $category_id) echo "selected"; ?>><?php echo $row_category['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" placeholder="Product name" name="product_name" value="<?php echo $product_name; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number" class="form-control" placeholder="Min price" name="min_price" value="<?php echo $min_price; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-dollar-sign"></i></span>
                            <input type="number" class="form-control" placeholder="Max price" name="max_price" value="<?php echo $max_price; ?>">
                        </div>
                    </div>
                    <div class="col-12 text-center">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>Search Products
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Products Display Section -->
        <section>
            <h2 class="text-center mb-4">Available Products</h2>
            <?php if (mysqli_num_rows($result_products) > 0) : ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($row = mysqli_fetch_assoc($result_products)) : ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="../admin/products_images/<?php echo $row['ImageURL']; ?>" class="card-img-top" alt="<?php echo $row['ProductName']; ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['ProductName']; ?></h5>
                                    <p class="card-text"><small class="text-muted">Category: <?php echo $row['category_name']; ?></small></p>
                                    <p class="card-text"><?php echo $row['Description']; ?></p>
                                    <p class="card-text">
                                        <strong>Price:</strong> <?php echo number_format($row['Price'], 2); ?> Pkr<br>
                                        <strong>In Stock:</strong> <?php echo $row['StockQuantity']; ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $row['ProductID']; ?>">
                                        <input type="hidden" class="max-quantity" value="<?php echo $row['StockQuantity']; ?>">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control quantity" name="quantity" value="1" min="1">
                                            <button class="btn btn-outline-primary add-to-cart-btn" type="submit" name="add_to_cart">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </div>
                                    </form>
                                    <a href="view_ratings_reviews.php?product_id=<?php echo $row['ProductID']; ?>" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-star"></i> Reviews
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No products found matching the search criteria.
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.add-to-cart-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    const quantityInput = form.querySelector('.quantity');
                    const maxQuantity = parseInt(form.querySelector('.max-quantity').value);
                    const enteredQuantity = parseInt(quantityInput.value);
                    if (enteredQuantity > maxQuantity) {
                        event.preventDefault();
                        alert(`Quantity exceeds available stock (${maxQuantity}) for this product.`);
                    }
                });
            });
        });
    </script>
</body>
</html>

