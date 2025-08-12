<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["role"] == "superadmin") {
        header("location: ../superadmin/dashboard.php");
    } elseif ($_SESSION["role"] == "president") {
        header("location: ../president/dashboard.php");
    }
    exit;
}

// Include config file
require_once "../includes/db_connect.php";
require_once "../includes/functions.php";

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    $email = sanitize_input($_POST["email"]);
    if (empty($email)) {
        $email_err = "Please enter email.";
    }

    // Check if password is empty
    $password = sanitize_input($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, full_name, email, password, role, association FROM users WHERE email = ? AND (role = 'superadmin' OR role = 'president')";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $full_name, $email_db, $hashed_password, $role, $association);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role;

                            if ($role == "president") {
                                $_SESSION["association"] = $association;
                            }

                            if ($role == "superadmin") {
                                header("location: ../superadmin/dashboard.php");
                            } elseif ($role == "president") {
                                header("location: ../president/dashboard.php");
                            }
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Royal Ambassadors</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../assets/logo.png" alt="Organization Logo" class="logo">
                <h2>Admin Panel Login</h2>
                <p>Royal Ambassadors of Nigeria (Ogun Baptist Conference)</p>
            </div>
            <?php
            if(!empty($login_err)){
                echo '<div class="alert alert-danger" style="color: red; padding-bottom: 15px;">' . $login_err . '</div>';
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo $email; ?>">
                    <span class="help-block" style="color: red;"><?php echo $email_err; ?></span>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <span class="help-block" style="color: red;"><?php echo $password_err; ?></span>
                </div>
                <button type="submit" class="login-button">Login</button>
            </form>
            <div class="login-footer">
                 <p>Don't have an account? <a href="../admin-signup.php">Sign Up</a></p>
                <p>&copy; <?php echo date("Y"); ?> Royal Ambassadors of Nigeria (Ogun Baptist Conference)</p>
            </div>
        </div>
    </div>
</body>
</html>
