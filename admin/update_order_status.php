<?php
session_start();
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "admin") {
    header("location: admin_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Update the order status in the database
    $sql_update = "UPDATE orders SET OrderStatus = ? WHERE OrderID = ?";
    if ($stmt = mysqli_prepare($conn, $sql_update)) {
        mysqli_stmt_bind_param($stmt, "si", $order_status, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Order status updated successfully.";
        } else {
            $_SESSION['error_message'] = "Error updating order status. Please try again.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Error preparing statement. Please try again.";
    }

    mysqli_close($conn);

    // Redirect back to the orders page
    header("location: order_report.php");
    exit;
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("location: order_report.php");
    exit;
}
?>
