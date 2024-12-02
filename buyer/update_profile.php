<?php
// Start session and check if user is logged in as seller
session_start();
if (!isset($_SESSION["id"]) || $_SESSION["usertype"] != "buyer") {
    header("location: ../login.php");
    exit;
}

include 'config.php';

// Get logged-in user's ID
$user_id = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Process the form submission and update the profile

  // Get the updated values from the form
  $username = $_POST["username"];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password

  // Update the user's profile in the database
  $query = "UPDATE users SET username='$username', email='$email', phone='$phone', password='$password' WHERE id='$user_id'";
  $result = mysqli_query($conn, $query);

  if ($result) {
    // Profile update successful
    // You can redirect to a success page or display a success message here
    echo "<div class='alert alert-success'>Profile updated successfully!</div>";
  } else {
    // Profile update failed
    // You can redirect to an error page or display an error message here
    echo "Error updating profile.";
  }
}

// Fetch the user's current profile data from the database
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
  <title>User Profile</title>
  <!-- Add Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php 
include('buyer_navbar.php');

?>

<!-- Main Content -->
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <!-- Profile Update Form -->
      <h2>Update Profile</h2>
      <form action="" method="POST">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone:</label>
          <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
        </div>
        <div class="form-group">
          <label for="password">New Password:</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
      </form>
    </div>
  </div>
</div>

<!-- Add Bootstrap JS (optional) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNS" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
</body>
</html>
