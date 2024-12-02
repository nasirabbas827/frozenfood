<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

// Process complaint submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["id"];
    $complaint_reason = $_POST["complaint_reason"];
    $complaint_text = $_POST["complaint_text"];

    // Insert the complaint into the database
    $sql_insert = "INSERT INTO complaints (UserID, ComplaintReason, Text) VALUES ('$user_id', '$complaint_reason', '$complaint_text')";
    $result_insert = mysqli_query($conn, $sql_insert);

    if ($result_insert) {
        echo "<div class='alert alert-success'>Complaint submitted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error submitting complaint.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('buyer_navbar.php'); ?>

    <div class="container mt-5">
        <h2>Submit Complaint</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="complaint_reason">Complaint Reason:</label>
                <input type="text" class="form-control" id="complaint_reason" name="complaint_reason" required>
            </div>
            <div class="form-group">
                <label for="complaint_text">Complaint Text:</label>
                <textarea class="form-control" id="complaint_text" name="complaint_text" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
