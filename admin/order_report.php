<?php
session_start();
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch all orders with order items, product names, and usernames
$sql_select = "SELECT o.*, oi.*, p.ProductName, u.Username 
               FROM orders o 
               INNER JOIN order_items oi ON o.OrderID = oi.OrderID
               INNER JOIN products p ON oi.ProductID = p.ProductID
               INNER JOIN users u ON o.UserID = u.id";
$result = mysqli_query($conn, $sql_select);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* Custom styles for the table */
        .table th, .table td {
            vertical-align: middle;
        }
        .table img {
            max-width: 100px;
            height: auto;
        }
        .btn-update {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="text-center mb-4">View Orders</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Username</th>
                        <th>Total Price</th>
                        <th>Delivery Address</th>
                        <th>Order Status</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Transaction Image</th>
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
                            <td><?php echo $row['PaymentMethod']; ?></td>
                            <td><?php echo $row['PaymentStatus']; ?></td>
                            <td>
                                <?php if ($row['TransactionImage']): ?>
                                    <img src="../buyer/<?php echo $row['TransactionImage']; ?>" alt="Transaction Image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
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
                                    <button type="submit" class="btn btn-primary btn-update mt-2">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
