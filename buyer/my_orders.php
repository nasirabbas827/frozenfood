<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Fetch orders for the logged-in user
$sql_orders = "SELECT * FROM orders WHERE UserID = $user_id ORDER BY OrderID DESC";
$result_orders = mysqli_query($conn, $sql_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="mb-4">My Orders</h2>
        <div class="row">
            <div class="col-md-12">
                <?php if (mysqli_num_rows($result_orders) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Total Price</th>
                                <th>Delivery Address</th>
                                <th>Order Status</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Items</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_orders)): ?>
                                <tr>
                                    <td><?php echo $row['OrderID']; ?></td>
                                    <td><?php echo number_format($row['TotalPrice'], 2); ?> Pkr</td>
                                    <td><?php echo $row['DeliveryAddress']; ?></td>
                                    <td><?php echo $row['OrderStatus']; ?></td>
                                    <td><?php echo $row['PaymentMethod']; ?></td>
                                    <td><?php echo $row['PaymentStatus']; ?></td>
                                    <td>
                                        <?php
                                        $order_id = $row['OrderID'];
                                        $sql_order_items = "SELECT products.ProductName, order_items.Quantity 
                                                            FROM order_items 
                                                            JOIN products ON order_items.ProductID = products.ProductID 
                                                            WHERE order_items.OrderID = $order_id";
                                        $result_order_items = mysqli_query($conn, $sql_order_items);
                                        if (mysqli_num_rows($result_order_items) > 0) {
                                            while ($item = mysqli_fetch_assoc($result_order_items)) {
                                                echo $item['ProductName'] . ' (' . $item['Quantity'] . '), ';
                                            }
                                        } else {
                                            echo "No items found";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['OrderStatus'] == 'Pending' && $row['PaymentStatus'] == 'unpaid' && $row['PaymentMethod'] != 'Cash on Delivery'): ?>
                                            <a href="paynow.php?order_id=<?php echo $row['OrderID']; ?>" class="btn btn-success">Pay Now</a>
                                        <?php endif; ?>
                                        <?php if ($row['OrderStatus'] == 'Delivered'): ?>
                                            <a href="review.php?order_id=<?php echo $row['OrderID']; ?>" class="btn btn-primary">Review</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">You have no orders yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
