<?php
session_start();
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "admin") {
    header("location: admin_login.php");
    exit;
}

// Fetch all orders with order items and usernames
$sql_select = "SELECT o.*, oi.*, p.ProductName, u.Username 
               FROM orders o 
               INNER JOIN order_items oi ON o.OrderID = oi.OrderID
               INNER JOIN products p ON oi.ProductID = p.ProductID
               INNER JOIN users u ON o.UserID = u.id";
$result = mysqli_query($conn, $sql_select);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - View Orders</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">

        <h2>View Orders</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Total Price</th>
                    <th>Delivery Address</th>
                    <th>Order Status</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo $row['OrderID']; ?></td>
                        <td><?php echo $row['Username']; ?></td>
                        <td><?php echo $row['TotalPrice']; ?></td>
                        <td><?php echo $row['DeliveryAddress']; ?></td>
                        <td><?php echo $row['OrderStatus']; ?></td>
                        <td><?php echo $row['ProductName']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td>
                            <form action="update_order_status.php" method="post">
                                <input type="hidden" name="order_id" value="<?php echo $row['OrderID']; ?>">
                                <select name="order_status" class="form-control">
                                    <option value="Pending" <?php if ($row['OrderStatus'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Processing" <?php if ($row['OrderStatus'] == 'Processing') echo 'selected'; ?>>Processing</option>
                                    <option value="Shipped" <?php if ($row['OrderStatus'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="Delivered" <?php if ($row['OrderStatus'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                </select>
                                <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Bootstrap JS (Optional) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
