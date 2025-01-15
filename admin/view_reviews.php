<?php
session_start();
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Approve or reject a review
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = $_POST['review_id'];
    $status = $_POST['status'];

    // Update the review status in the database
    $sql_update = "UPDATE reviews SET Status = ? WHERE ReviewID = ?";
    if ($stmt = mysqli_prepare($conn, $sql_update)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $review_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Review status updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating review status. Please try again.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Error preparing statement. Please try again.";
    }

    mysqli_close($conn);

    // Redirect back to the same page to refresh the data
    header("location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all reviews with product, user, and order details
$sql_select = "SELECT r.*, p.ProductName, u.Username, oi.ProductID, oi.Quantity, o.OrderID 
               FROM reviews r 
               INNER JOIN order_items oi ON r.OrderID = oi.OrderID 
               INNER JOIN orders o ON oi.OrderID = o.OrderID 
               INNER JOIN products p ON oi.ProductID = p.ProductID
               INNER JOIN users u ON r.UserID = u.id";
$result = mysqli_query($conn, $sql_select);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - View Reviews</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-approve, .btn-reject {
            padding: 5px 10px;
            font-size: 14px;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container mt-4">
        <h2>View Reviews</h2>
        <?php
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
        ?>
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Review ID</th>
                    <th>Username</th>
                    <th>Product Name</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo $row['ReviewID']; ?></td>
                        <td><?php echo $row['Username']; ?></td>
                        <td><?php echo $row['ProductName']; ?></td>
                        <td><?php echo $row['Rating']; ?></td>
                        <td><?php echo $row['Comment']; ?></td>
                        <td><?php echo ucfirst($row['Status']); ?></td>
                        <td>
                            <?php if ($row['Status'] == 'pending') : ?>
                                <form action="" method="post">
                                    <input type="hidden" name="review_id" value="<?php echo $row['ReviewID']; ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>
                            <?php elseif ($row['Status'] == 'approved') : ?>
                                <form action="" method="post">
                                    <input type="hidden" name="review_id" value="<?php echo $row['ReviewID']; ?>">
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-reject">Reject</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
