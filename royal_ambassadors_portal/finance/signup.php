<?php
session_start();
require_once "../includes/db_connect.php"; // This includes config.php
require_once "../includes/functions.php";

$full_name = $email = $password = $confirm_password = $passcode = "";
$full_name_err = $email_err = $password_err = $confirm_password_err = $passcode_err = "";
$role = "finance";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate passcode
    $passcode = sanitize_input($_POST["passcode"]);
    if (empty($passcode) || $passcode !== FINANCE_PASSCODE) {
        $passcode_err = "The secret passcode is incorrect.";
    }

    // Validate full name
    $full_name = sanitize_input($_POST["full_name"]);
    if (empty($full_name)) {
        $full_name_err = "Please enter your full name.";
    }

    // Validate email
    $email = sanitize_input($_POST["email"]);
    if (empty($email)) {
        $email_err = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already taken.";
                }
            }
            $stmt->close();
        }
    }

    // Validate password
    $password = sanitize_input($_POST["password"]);
    if (empty($password) || strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    // Validate confirm password
    $confirm_password = sanitize_input($_POST["confirm_password"]);
    if ($password != $confirm_password) {
        $confirm_password_err = "Password did not match.";
    }

    if (empty($passcode_err) && empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
            if ($stmt->execute()) {
                header("location: login.php");
                exit;
            } else {
                echo "Something went wrong. Please try again.";
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
    <title>Finance Panel Sign Up</title>
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/logo.png" alt="Logo" class="logo"/>
                <h2>Finance Panel Sign Up</h2>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $full_name; ?>" required>
                    <span class="help-block" style="color: red;"><?php echo $full_name_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $email; ?>" required>
                    <span class="help-block" style="color: red;"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                    <span class="help-block" style="color: red;"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                    <span class="help-block" style="color: red;"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Secret Passcode</label>
                    <input type="password" name="passcode" required>
                    <span class="help-block" style="color: red;"><?php echo $passcode_err; ?></span>
                </div>
                <button type="submit" class="btn-login">Sign Up</button>
            </form>
        </div>
    </div>
</body>
</html>
