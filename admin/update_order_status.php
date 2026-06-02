<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// Check if user is logged in as admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    // Fetch user email based on the order ID
    $sql_user_email = "SELECT u.Email, o.OrderStatus FROM orders o
                       INNER JOIN users u ON o.UserID = u.id
                       WHERE o.OrderID = ?";
    if ($stmt = mysqli_prepare($conn, $sql_user_email)) {
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $user_email = $row['Email'];
            $previous_status = $row['OrderStatus'];
            
            // Update the order status in the database
            $sql_update = "UPDATE orders SET OrderStatus = ? WHERE OrderID = ?";
            if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                mysqli_stmt_bind_param($stmt_update, "si", $order_status, $order_id);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    $_SESSION['success_message'] = "Order status updated successfully.";

                    // Send email notification to the user
                    $mail = new PHPMailer(true);
                    try {
                        //Server settings
                        $mail->isSMTP();                                           // Set mailer to use SMTP
                        $mail->Host = 'smtp.gmail.com';                            // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                                    // Enable SMTP authentication
                        $mail->Username = 'nasiryt.827@gmail.com';                  // SMTP username
                        $mail->Password = "YOUR_OWN_API_KEY";                   // SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable TLS encryption
                        $mail->Port = 587;                                         // TCP port to connect to

                        //Recipients
                        $mail->setFrom('nasiryt.827@gmail.com', 'Frozen Food Panda');
                        $mail->addAddress($user_email);                             // Add a recipient

                        // Content
                        $mail->isHTML(true);                                        // Set email format to HTML
                        $mail->Subject = 'Your Order Status has been Updated';
                        $mail->Body    = "Dear Customer,<br><br>Your order with ID <strong>#{$order_id}</strong> has been updated. The status is now: <strong>{$order_status}</strong>.<br><br>Thank you for shopping with us!<br><br>Best Regards,<br>Frozen Food Panda";

                        $mail->send();
                    } catch (Exception $e) {
                        $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $_SESSION['error_message'] = "Error updating order status. Please try again.";
                }

                mysqli_stmt_close($stmt_update);
            }
        } else {
            $_SESSION['error_message'] = "Order not found.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Error preparing query.";
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
