<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Check if user is logged in as a buyer
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Fetch user details from the database
$user_id = $_SESSION["id"];
$query = "SELECT email FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Check if user email is retrieved successfully
if (!$row) {
    echo "Error: Unable to fetch user email.";
    exit;
}

$user_email = $row['email'];

// Check if total price, delivery address, and payment method are provided
if (!isset($_POST['total_price']) || !isset($_POST['delivery_address']) || !isset($_POST['payment_method'])) {
    header("location: checkout.php");
    exit;
}

$total_price = $_POST['total_price'];
$delivery_address = $_POST['delivery_address'];
$payment_method = $_POST['payment_method'];

// Get user ID
$user_id = $_SESSION['id'];

// Set PaymentStatus to 'unpaid' by default
$payment_status = 'unpaid'; // You can change this to 'paid' when the payment is confirmed

// Insert order into database with PaymentStatus and Timestamp
$sql_insert_order = "INSERT INTO orders (UserID, TotalPrice, DeliveryAddress, PaymentMethod, PaymentStatus, OrderStatus) 
                     VALUES ('$user_id', '$total_price', '$delivery_address', '$payment_method', '$payment_status', 'Pending')";

if (mysqli_query($conn, $sql_insert_order)) {
    // Get the ID of the last inserted order
    $order_id = mysqli_insert_id($conn);
    
    // Insert order items into order_items table
    foreach ($_SESSION['cart'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Decrease product quantity in the products table
        $sql_update_quantity = "UPDATE products SET StockQuantity = StockQuantity - $quantity WHERE ProductID = $product_id";
        mysqli_query($conn, $sql_update_quantity);
        
        // Insert order item into order_items table with Timestamp
        $sql_insert_order_item = "INSERT INTO order_items (OrderID, ProductID, Quantity) 
                                 VALUES ('$order_id', '$product_id', '$quantity')";
        mysqli_query($conn, $sql_insert_order_item);
    }

    // Clear the cart after placing the order
    unset($_SESSION['cart']);

    // Create a new instance of PHPMailer
    $mail = new PHPMailer(true);
    // Configure SMTP settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'nasiryt.827@gmail.com'; // Replace with your SMTP username
    $mail->Password = "YOUR_OWN_API_KEY"; // Replace with your SMTP password
    $mail->Port = 587; // Replace with your SMTP port (usually 587)

    // Send the email
    $mail->setFrom('nasiryt.827@gmail.com', 'Frozen Food Panda');
    $mail->addAddress($user_email);

    // Set email subject and body
    $mail->Subject = 'Order Confirmation';
    $mail->Body = 'Your order has been placed successfully. Order ID: ' . $order_id;

    // Send email
    if ($mail->send()) {
        // Redirect to a confirmation page
        header("location: order_confirmation.php?order_id=$order_id");
        exit;
    } else {
        echo 'Email could not be sent.';
    }
} else {
    echo "Error: " . $sql_insert_order . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>
