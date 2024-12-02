<?php
// start session and check if user is logged in as a buyer
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "buyer") {
    header("location: login.php");
    exit;
}

// Check if cart session exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty. <a href='buyer_home.php'>Go back to dashboard</a>";
    exit;
}


// Fetch product details for each item in the cart
$cart_items = $_SESSION['cart'];
$total_price = 0;

// Check if the remove button is clicked
if (isset($_GET['remove']) && isset($_GET['product_id'])) {
    $remove_product_id = $_GET['product_id'];
    foreach ($cart_items as $key => $item) {
        if ($item['product_id'] == $remove_product_id) {
            unset($cart_items[$key]); // Remove the item from the cart
            $_SESSION['cart'] = $cart_items; // Update the cart session
            header("Location: view_cart.php"); // Redirect back to view cart page
            exit;
        }
    }
}

// Update quantity if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($cart_items as &$item) {
        if ($item['product_id'] == $_POST['product_id']) {
            // Check if the new quantity exceeds available stock quantity
            $product_id = $_POST['product_id'];
            $sql_stock = "SELECT StockQuantity FROM products WHERE ProductID = $product_id";
            $result_stock = mysqli_query($conn, $sql_stock);
            $row_stock = mysqli_fetch_assoc($result_stock);
            $stock_quantity = $row_stock['StockQuantity'];
            if ($_POST['quantity'] <= $stock_quantity) {
                $item['quantity'] = $_POST['quantity'];
                $_SESSION['cart'] = $cart_items; // Update the cart session
            } else {
                echo "Quantity exceeds available stock for product: " . $item['product_name'];
            }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Cart</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('buyer_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Your Cart</h2>
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1">
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td><?php echo number_format($item['price'], 2); ?> Pkr</td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> Pkr</td>
                        <td>
                            <a href="view_cart.php?remove=true&product_id=<?php echo $item['product_id']; ?>"
                               class="btn btn-danger">Remove</a>
                        </td>
                    </tr>
                    <?php $total_price += $item['price'] * $item['quantity']; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-right">
                <h4>Total: <?php echo number_format($total_price, 2); ?> Pkr</h4>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>
