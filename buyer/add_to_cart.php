<?php
// start session and check if user is logged in as a buyer
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Check if product_id and quantity are set
if(isset($_POST['product_id'], $_POST['quantity'])) {
    // Sanitize input
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);

    // Retrieve product details
    $sql_product = "SELECT * FROM products WHERE ProductID = $product_id";
    $result_product = mysqli_query($conn, $sql_product);

    if(mysqli_num_rows($result_product) > 0) {
        $product = mysqli_fetch_assoc($result_product);

        // Check if quantity is valid
        if($quantity <= 0 || $quantity > $product['StockQuantity']) {
            echo "Invalid quantity.";
            exit;
        }

        // Add product to cart
        $cart_item = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['Price'],
            'product_name' => $product['ProductName']
        ];

        // Initialize cart session if not already initialized
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add product to cart session
        $_SESSION['cart'][] = $cart_item;

        // Redirect back to previous page with success message
        header("location: {$_SERVER['HTTP_REFERER']}?success=1");
        exit;
    } else {
        echo "Product not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
