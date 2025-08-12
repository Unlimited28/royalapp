<?php
// Initialize the session
session_start();

// Include config file
require_once "includes/db_connect.php"; // This already includes config.php
require_once "includes/functions.php";

// Define variables and initialize with empty values
$full_name = $email = $password = $confirm_password = $passcode = $association = "";
$full_name_err = $email_err = $password_err = $confirm_password_err = $passcode_err = $association_err = "";
$role = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already taken.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }

    // Validate passcode
    $passcode = sanitize_input($_POST["passcode"]);
    if (empty($passcode)) {
        $passcode_err = "Please enter the secret passcode.";
    } else {
        if ($passcode == SUPERADMIN_PASSCODE) {
            $role = 'superadmin';
        } elseif ($passcode == PRESIDENT_PASSCODE) {
            $role = 'president';
        } else {
            $passcode_err = "The secret passcode is incorrect.";
        }
    }

    // If role is president, validate association
    if ($role == 'president') {
        $association = sanitize_input($_POST["association"]);
        if (empty($association)) {
            $association_err = "Please select your association.";
        }
    }

    // Validate password
    $password = sanitize_input($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    // Validate confirm password
    $confirm_password = sanitize_input($_POST["confirm_password"]);
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";
    } else {
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($passcode_err) && empty($association_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (full_name, email, password, role, association) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssss", $param_full_name, $param_email, $param_password, $param_role, $param_association);

            // Set parameters
            $param_full_name = $full_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_role = $role;
            $param_association = ($role == 'president') ? $association : NULL;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: admin/login.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
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
  <title>Admin Sign Up - Royal Ambassadors</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Marcellus&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Open Sans', sans-serif;
      background: linear-gradient(135deg, #001f3f 40%, #003366 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .signup-wrapper {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      width: 100%;
      max-width: 500px;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
      text-align: center;
    }
    .signup-wrapper img.logo {
      width: 80px;
      margin-bottom: 10px;
    }
    .signup-wrapper h2 {
      font-family: 'Marcellus', serif;
      font-size: 26px;
      color: #001f3f;
      margin-bottom: 5px;
    }
    .signup-wrapper p.subtitle {
      font-size: 14px;
      color: #666;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 18px;
      text-align: left;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #333;
    }
    .form-group input, .form-group select {
      width: 100%;
      padding: 12px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      transition: 0.3s;
    }
    .form-group input:focus, .form-group select:focus {
      border-color: #DAA520; /* Gold */
      outline: none;
    }
    .btn-submit {
      background-color: #001f3f;
      color: #fff;
      border: none;
      width: 100%;
      padding: 12px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 6px;
      margin-top: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn-submit:hover {
      background-color: #003366;
    }
    .login-redirect {
      margin-top: 20px;
      font-size: 14px;
      color: #444;
    }
    .login-redirect a {
      color: #DAA520; /* Gold accent */
      text-decoration: none;
      font-weight: bold;
    }
    .login-redirect a:hover {
      text-decoration: underline;
    }
    .help-block {
        color: red;
        font-size: 12px;
    }
  </style>
</head>
<body>

  <div class="signup-wrapper">
    <img src="assets/logo.png" alt="RAN Logo" class="logo" />

    <h2>Admin Sign Up</h2>
    <p class="subtitle">Royal Ambassadors of Nigeria - Ogun Baptist Conference</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
      <div class="form-group <?php echo (!empty($full_name_err)) ? 'has-error' : ''; ?>">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="full_name" placeholder="e.g. John Doe" value="<?php echo $full_name; ?>" />
        <span class="help-block"><?php echo $full_name_err; ?></span>
      </div>

      <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="e.g. john@example.com" value="<?php echo $email; ?>" />
        <span class="help-block"><?php echo $email_err; ?></span>
      </div>

      <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a password" />
        <span class="help-block"><?php echo $password_err; ?></span>
      </div>

      <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
        <label for="confirm-password">Confirm Password</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-type password" />
        <span class="help-block"><?php echo $confirm_password_err; ?></span>
      </div>

      <div class="form-group <?php echo (!empty($passcode_err)) ? 'has-error' : ''; ?>">
        <label for="secret-code">Secret Admin Code</label>
        <input type="text" id="secret-code" name="passcode" placeholder="e.g. passcode" value="<?php echo $passcode; ?>" />
        <span class="help-block"><?php echo $passcode_err; ?></span>
      </div>

      <div class="form-group <?php echo (!empty($association_err)) ? 'has-error' : ''; ?>">
        <label>Association (for Presidents only)</label>
        <select name="association">
            <option value="">Select Association</option>
            <option value="Agape Baptist Association" <?php if ($association == "Agape Baptist Association") echo "selected"; ?>>Agape Baptist Association</option>
            <option value="Abeokuta North West Baptist Association" <?php if ($association == "Abeokuta North West Baptist Association") echo "selected"; ?>>Abeokuta North West Baptist Association</option>
            <option value="Ketu Baptist Association" <?php if ($association == "Ketu Baptist Association") echo "selected"; ?>>Ketu Baptist Association</option>
            <option value="Irepodun Oke-Yewa Baptist Association" <?php if ($association == "Irepodun Oke-Yewa Baptist Association") echo "selected"; ?>>Irepodun Oke-Yewa Baptist Association</option>
            <option value="Zion Baptist Association" <?php if ($association == "Zion Baptist Association") echo "selected"; ?>>Zion Baptist Association</option>
            <option value="Abeokuta South Baptist Association" <?php if ($association == "Abeokuta South Baptist Association") echo "selected"; ?>>Abeokuta South Baptist Association</option>
            <option value="Ijebu North East Baptist Association" <?php if ($association == "Ijebu North East Baptist Association") echo "selected"; ?>>Ijebu North East Baptist Association</option>
            <option value="Great Grace Baptist Association" <?php if ($association == "Great Grace Baptist Association") echo "selected"; ?>>Great Grace Baptist Association</option>
            <option value="Abeokuta East Baptist Association" <?php if ($association == "Abeokuta East Baptist Association") echo "selected"; ?>>Abeokuta East Baptist Association</option>
            <option value="Upper Room Baptist Association" <?php if ($association == "Upper Room Baptist Association") echo "selected"; ?>>Upper Room Baptist Association</option>
            <option value="Ijebu North Baptist Association" <?php if ($association == "Ijebu North Baptist Association") echo "selected"; ?>>Ijebu North Baptist Association</option>
            <option value="Abeokuta North-East Baptist Association" <?php if ($association == "Abeokuta North-East Baptist Association") echo "selected"; ?>>Abeokuta North-East Baptist Association</option>
            <option value="Abeokuta West Baptist Association" <?php if ($association == "Abeokuta West Baptist Association") echo "selected"; ?>>Abeokuta West Baptist Association</option>
            <option value="Bethel Baptist Association" <?php if ($association == "Bethel Baptist Association") echo "selected"; ?>>Bethel Baptist Association</option>
            <option value="Ayetoro Baptist Association" <?php if ($association == "Ayetoro Baptist Association") echo "selected"; ?>>Ayetoro Baptist Association</option>
            <option value="Dominion Baptist Association" <?php if ($association == "Dominion Baptist Association") echo "selected"; ?>>Dominion Baptist Association</option>
            <option value="Iroyin Ayo Baptist Association" <?php if ($association == "Iroyin Ayo Baptist Association") echo "selected"; ?>>Iroyin Ayo Baptist Association</option>
            <option value="Ijebu Central Baptist Association" <?php if ($association == "Ijebu Central Baptist Association") echo "selected"; ?>>Ijebu Central Baptist Association</option>
            <option value="Rehoboth Baptist Association" <?php if ($association == "Rehoboth Baptist Association") echo "selected"; ?>>Rehoboth Baptist Association</option>
            <option value="Christlife Baptist Association" <?php if ($association == "Christlife Baptist Association") echo "selected"; ?>>Christlife Baptist Association</option>
            <option value="Ifeoluwa Baptist Association" <?php if ($association == "Ifeoluwa Baptist Association") echo "selected"; ?>>Ifeoluwa Baptist Association</option>
            <option value="Ijebu Progressive Baptist Association" <?php if ($association == "Ijebu Progressive Baptist Association") echo "selected"; ?>>Ijebu Progressive Baptist Association</option>
            <option value="Yewa Baptist Association" <?php if ($association == "Yewa Baptist Association") echo "selected"; ?>>Yewa Baptist Association</option>
            <option value="Ayooluwa Baptist Association" <?php if ($association == "Ayooluwa Baptist Association") echo "selected"; ?>>Ayooluwa Baptist Association</option>
            <option value="Macedonia Baptist Association" <?php if ($association == "Macedonia Baptist Association") echo "selected"; ?>>Macedonia Baptist Association</option>
        </select>
        <span class="help-block"><?php echo $association_err; ?></span>
      </div>

      <button type="submit" class="btn-submit">Create Admin Account</button>
    </form>

    <div class="login-redirect">
      Already have an account? <a href="admin/login.php">Login here</a>
    </div>
  </div>

</body>
</html>
