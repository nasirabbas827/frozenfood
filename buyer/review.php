<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: ../login.php");
    exit;
}

// Check if order ID is provided in the URL
if (!isset($_GET['order_id'])) {
    header("location: my_orders.php"); // Redirect to my_orders.php or any other page
    exit;
}

$order_id = $_GET['order_id'];

// Fetch order details from the database
$sql_order_details = "SELECT * FROM orders WHERE OrderID = $order_id AND UserID = {$_SESSION['id']}";
$result_order_details = mysqli_query($conn, $sql_order_details);
$order_details = mysqli_fetch_assoc($result_order_details);

// Check if order exists and belongs to the logged-in user
if (!$order_details) {
    header("location: my_orders.php"); // Redirect to my_orders.php or any other page
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $rating = $_POST['rating'];
    $image = $_FILES['image'];

    // Validate uploaded image
    $image_name = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_error = $_FILES['image']['error'];
    $image_type = $_FILES['image']['type'];

    // Check if image is uploaded
    if ($image_error === 0) {
        // Check file size
        if ($image_size > 5000000) { // 5MB limit
            $upload_error = "Sorry, your file is too large.";
        } else {
            // Generate a unique name for the image
            $image_new_name = uniqid('', true) . '.' . strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $image_destination = 'uploads/' . $image_new_name;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($image_tmp_name, $image_destination)) {
                // Insert review into the database with the status as 'pending'
                $sql_insert_review = "INSERT INTO reviews (OrderID, UserID, Comment, Rating, Image, Status) 
                                      VALUES ($order_id, {$_SESSION['id']}, '$comment', $rating, '$image_new_name', 'pending')";
                if (mysqli_query($conn, $sql_insert_review)) {
                    $success_message = "Review submitted successfully. It will be approved after verification.";
                } else {
                    $upload_error = "Error: " . mysqli_error($conn);
                }
            } else {
                $upload_error = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $upload_error = "Error uploading image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h2 class="mb-4">Submit Review</h2>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($upload_error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $upload_error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="comment">Comment:</label>
                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="rating">Rating:</label>
                <select class="form-control" id="rating" name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Upload Image:</label>
                <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
