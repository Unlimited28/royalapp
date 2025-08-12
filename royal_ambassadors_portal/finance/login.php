<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/functions.php";

// Redirect if already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && $_SESSION["role"] === 'finance') {
    header("location: dashboard.php");
    exit;
}

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate credentials
    $email = sanitize_input($_POST["email"]);
    if (empty($email)) {
        $email_err = "Please enter email.";
    }

    $password = sanitize_input($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'finance'";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $full_name, $email_db, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role;
                            header("location: dashboard.php");
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
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
    <title>Finance Panel Login</title>
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/logo.png" alt="Logo" class="logo"/>
                <h2>Finance Panel Login</h2>
            </div>
            <?php if(!empty($login_err)) echo '<div style="color:red; padding-bottom:10px;">'.$login_err.'</div>'; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo $email; ?>">
                    <span class="help-block" style="color: red;"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <span class="help-block" style="color: red;"><?php echo $password_err; ?></span>
                </div>
                <button type="submit" class="btn-login">Log In</button>
                 <div class="form-footer">
                    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
