<?php
session_start();
include('config.php');

// Check if user is logged in as admin
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "admin") {
    header("location: admin_login.php");
    exit;
}

// Fetch all complaints
$sql_complaints = "SELECT c.ComplaintID, u.Username, c.ComplaintReason, c.Text, c.SubmissionDate 
                   FROM complaints c 
                   INNER JOIN users u ON c.UserID = u.id";

$result_complaints = mysqli_query($conn, $sql_complaints);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View Complaints</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-4">

        <h2>View Complaints</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Complaint ID</th>
                    <th>User</th>
                    <th>Reason</th>
                    <th>Text</th>
                    <th>Submission Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result_complaints)) : ?>
                    <tr>
                        <td><?php echo $row['ComplaintID']; ?></td>
                        <td><?php echo $row['Username']; ?></td>
                        <td><?php echo $row['ComplaintReason']; ?></td>
                        <td><?php echo $row['Text']; ?></td>
                        <td><?php echo $row['SubmissionDate']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
