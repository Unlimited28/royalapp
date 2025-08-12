<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: ambassador/dashboard.php");
    exit;
}

// Include config file
require_once "includes/db_connect.php";
require_once "includes/functions.php";

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
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ? AND role = 'ambassador'";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);

            // Set parameters
            $param_email = $email;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $full_name, $email_db, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role;

                            // Redirect user to dashboard page
                            header("location: ambassador/dashboard.php");
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist, display a generic error message
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Royal Ambassadors</title>
  <link rel="stylesheet" href="styles/login.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <img src="assets/logo.png" alt="Logo" class="logo"/>
        <h2>Royal Ambassadors of Nigeria</h2>
        <p class="subtext">OGUN BAPTIST CONFERENCE</p>
      </div>
      <?php
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
      ?>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
        <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
          <label for="email">Email</label>
          <input type="text" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>">
          <span class="help-block"><?php echo $email_err; ?></span>
        </div>

        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password">
          <span class="help-block"><?php echo $password_err; ?></span>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-login">Log In</button>
        </div>

        <div class="form-footer">
          <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
