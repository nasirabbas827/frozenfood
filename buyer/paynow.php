<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("location: my_orders.php");
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['id'];

// Fetch the order details
$sql_order = "SELECT * FROM orders WHERE OrderID = $order_id AND UserID = $user_id";
$result_order = mysqli_query($conn, $sql_order);
$order = mysqli_fetch_assoc($result_order);

if (!$order || $order['PaymentStatus'] == 'paid') {
    header("location: my_orders.php");
    exit;
}

// Define allowed payment methods and status
$payment_methods = ['cash_on_delivery', 'debit_card', 'easy_paisa', 'jazz_cash'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $transaction_image = $_FILES['transaction_image'];

    // Validate payment method
    if (!in_array($payment_method, $payment_methods)) {
        echo "Invalid payment method selected.";
        exit;
    }

    // Validate transaction image
    if ($transaction_image['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($transaction_image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($transaction_image["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            exit;
        }

        // Check file size (5MB max)
        if ($transaction_image["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            exit;
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            exit;
        }

        // Upload file
        if (move_uploaded_file($transaction_image["tmp_name"], $target_file)) {
            // Update the order with the transaction image and payment method
            $sql_update_payment = "UPDATE orders SET PaymentStatus = 'paid', PaymentMethod = '$payment_method', TransactionImage = '$target_file' WHERE OrderID = $order_id";
            if (mysqli_query($conn, $sql_update_payment)) {
                header("location: my_orders.php");
                exit;
            } else {
                echo "Error updating payment details: " . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    } else {
        echo "Please upload a valid transaction image.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Now</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h2 class="mb-4">Order ID: <?php echo $order['OrderID']; ?></h2>
        <p>Total Price: <?php echo number_format($order['TotalPrice'], 2); ?> Pkr</p>

        <form method="post" enctype="multipart/form-data">
            <!-- Payment Method Dropdown -->
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                    <option value="cash_on_delivery">Cash on Delivery</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="easy_paisa">Easy Paisa</option>
                    <option value="jazz_cash">Jazz Cash</option>
                </select>
            </div>

            <!-- Transaction Image Upload -->
            <div class="form-group">
                <label for="transaction_image">Transaction Image</label>
                <input type="file" class="form-control-file" id="transaction_image" name="transaction_image" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-success">Complete Payment</button>
        </form>
    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
