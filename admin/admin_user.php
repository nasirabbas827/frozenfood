<?php
// Database connection details
session_start();
include 'config.php';

if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "admin") {
    header("location: admin_login.php");
    exit;
}

// If form is submitted, update user status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['user_id']) && isset($_POST['new_status'])) {
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'];
        
        // Update user status in the database
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Fetch all users from the database
$sql = "SELECT id, username, email, usertype, status FROM users";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Manage Users</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['usertype'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>
                            <form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "' method='post'>
                                <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                <div class='form-group'>
                                    <select class='form-control' name='new_status'>
                                        <option value='pending'>Pending</option>
                                        <option value='approved'>Approved</option>
                                        <option value='rejected'>Rejected</option>
                                    </select>
                                </div>
                                <button type='submit' class='btn btn-primary'>Update Status</button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>
