<?php
include('config.php');

// define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // if no errors, check credentials and log in user
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, email, password, usertype, status FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $usertype, $status);
            if (mysqli_stmt_fetch($stmt)) {
                if ($status == "approved") {
                    if (password_verify($password, $hashed_password)) {
                        // password is correct, start session and log in user
                        session_start();
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email;
                        $_SESSION["usertype"] = $usertype;
                        if ($usertype == "buyer") {
                            header("location: ./buyer/buyer_home.php");
                        } else {
                            header("location: index.php");
                        }
                    } else {
                        // password is incorrect
                        $password_err = "The password you entered is incorrect.";
                    }
                } else {
                    // account status is not approved
                    $email_err = "Your account is not approved yet. Please wait for approval.";
                }
            }
        } else {
            // email not found in database
            $email_err = "No account found with that email.";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>User Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

   
</head>

<body>
<?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">User Login</h2>
        <p class="text-center">Please fill in your credentials to log in.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
                <span><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span><?php echo $password_err; ?></span>
            </div>
            <div class="form-group text-center">
                <input type="submit" value="Log in" class="btn btn-primary">
            </div>
        </form>
        <p class="text-center">Have'nt any Account <a href="register.php">Register here</a></p>
        <p class="text-center">Go to  <a href="index.php">Home Page</a></p>
        
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>

