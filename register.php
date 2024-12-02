<?php
include('config.php');

// define variables and initialize with empty values
$username = $password = $usertype = $email = $phone = "";
$username_err = $password_err = $usertype_err = $email_err = $phone_err = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // check if username already exists in database
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = trim($_POST["username"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_err = "This username is already taken.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    // validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // validate user type
    if (empty(trim($_POST["usertype"]))) {
        $usertype_err = "Please select a user type.";
    } else {
        $usertype = trim($_POST["usertype"]);
    }

    // validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        if (!preg_match('/@gmail\.com$/', $email)) {
            $email_err = "Please enter a valid Gmail address.";
        }
        // check if email already exists in database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email address is already taken.";
        }
    }

    // validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        // check if phone number already exists in database
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_phone);
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (!preg_match('/^\d{10,11}$/', $phone)) {
            $phone_err = "Please enter a valid phone number.";
        }
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $phone_err = "This phone number is already taken.";
        }
    }

    // if no errors, insert user into database
    if (empty($username_err) && empty($password_err) && empty($usertype_err) && empty($email_err) && empty($phone_err)) {
        // Set the user status to "pending"
        $status = "pending";

        $sql = "INSERT INTO users (username, password, usertype, email, phone, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $param_username, $param_password, $param_usertype, $param_email, $param_phone, $status);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_usertype = $usertype;
        $param_email = $email;
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo '<div class="alert alert-success" role="alert">User registered successfully. Your account is pending approval.</div>';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mt-5">User Registration</h2>
        <p class="text-center">Please fill in your details to register.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>

            <div class="form-group">
                <label>User Type</label>
                <div class="ml-5 form-check form-check-inline <?php echo (!empty($usertype_err)) ? 'is-invalid' : ''; ?>">
                    <input class="form-check-input" type="radio" name="usertype" id="buyer" value="buyer" <?php if ($usertype == "buyer") echo " checked"; ?>>
                    <label class="form-check-label" for="buyer">Buyer ( Customer )</label>
                </div>
                <span class="invalid-feedback"><?php echo $usertype_err; ?></span>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="number" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                <span class="invalid-feedback"><?php echo $phone_err; ?></span>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Register">
            </div>
        </form>

        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
        <p class="text-center">Go to <a href="index.php">Home Page</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>