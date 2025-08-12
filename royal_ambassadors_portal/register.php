<?php
// Initialize the session
session_start();

// Include config file
require_once "includes/db_connect.php";
require_once "includes/functions.php";

// Define variables and initialize with empty values
$full_name = $email = $phone = $church = $association = $age = $rank = $password = $confirm_password = "";
$full_name_err = $email_err = $phone_err = $church_err = $association_err = $age_err = $rank_err = $password_err = $confirm_password_err = "";

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

    // Validate phone
    $phone = sanitize_input($_POST["phone"]);
    if (empty($phone)) {
        $phone_err = "Please enter your phone number.";
    }

    // Validate church
    $church = sanitize_input($_POST["church"]);
    if (empty($church)) {
        $church_err = "Please enter your church name.";
    }

    // Validate association
    $association = sanitize_input($_POST["association"]);
    if (empty($association)) {
        $association_err = "Please enter your association.";
    }

    // Validate age
    $age = sanitize_input($_POST["age"]);
    if (empty($age)) {
        $age_err = "Please enter your age.";
    } elseif(!ctype_digit($age)){
        $age_err = "Please enter a valid age.";
    }

    // Validate rank
    $rank = sanitize_input($_POST["rank"]);
    if (empty($rank)) {
        $rank_err = "Please select your rank.";
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
    if (empty($full_name_err) && empty($email_err) && empty($phone_err) && empty($church_err) && empty($association_err) && empty($age_err) && empty($rank_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (full_name, email, phone, church, association, age, rank, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssisss", $param_full_name, $param_email, $param_phone, $param_church, $param_association, $param_age, $param_rank, $param_password, $param_role);

            // Set parameters
            $param_full_name = $full_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_church = $church;
            $param_association = $association;
            $param_age = $age;
            $param_rank = $rank;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = 'ambassador';

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Get the ID of the new user
                $new_user_id = $stmt->insert_id;

                // Generate the unique ID
                $unique_id = "OGBC/RA/" . str_pad($new_user_id, 4, '0', STR_PAD_LEFT);

                // Update the user with the unique ID
                $update_sql = "UPDATE users SET unique_id = ? WHERE id = ?";
                if ($update_stmt = $mysqli->prepare($update_sql)) {
                    $update_stmt->bind_param("si", $unique_id, $new_user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Redirect to login page
                header("location: login.php");
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | Royal Ambassadors</title>
  <link rel="stylesheet" href="styles/register.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <div class="signup-container">
    <div class="form-box">
      <div class="logo-header">
        <img src="assets/logo.png" alt="RA Logo">
        <h2>Sign Up</h2>
        <p>Join the Royal Ambassadors platform</p>
      </div>

      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="signup-form">
        <div class="form-grid">
          <div class="form-group <?php echo (!empty($full_name_err)) ? 'has-error' : ''; ?>">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Enter full name" value="<?php echo $full_name; ?>">
            <span class="help-block"><?php echo $full_name_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="example@email.com" value="<?php echo $email; ?>">
            <span class="help-block"><?php echo $email_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($phone_err)) ? 'has-error' : ''; ?>">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="0801-234-5678" value="<?php echo $phone; ?>">
            <span class="help-block"><?php echo $phone_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($church_err)) ? 'has-error' : ''; ?>">
            <label>Church Name</label>
            <input type="text" name="church" placeholder="e.g. First Baptist, Abeokuta" value="<?php echo $church; ?>">
            <span class="help-block"><?php echo $church_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($association_err)) ? 'has-error' : ''; ?>">
            <label>Association</label>
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

          <div class="form-group <?php echo (!empty($age_err)) ? 'has-error' : ''; ?>">
            <label>Age</label>
            <input type="number" name="age" placeholder="Your age" min="6" value="<?php echo $age; ?>">
            <span class="help-block"><?php echo $age_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($rank_err)) ? 'has-error' : ''; ?>">
            <label>Rank</label>
            <select name="rank">
                <option value="">Select Rank</option>
                <option value="Candidate" <?php if ($rank == "Candidate") echo "selected"; ?>>Candidate</option>
                <option value="Assistant Intern" <?php if ($rank == "Assistant Intern") echo "selected"; ?>>Assistant Intern</option>
                <option value="Intern" <?php if ($rank == "Intern") echo "selected"; ?>>Intern</option>
                <option value="Senior Intern" <?php if ($rank == "Senior Intern") echo "selected"; ?>>Senior Intern</option>
                <option value="Envoy" <?php if ($rank == "Envoy") echo "selected"; ?>>Envoy</option>
                <option value="Senior Envoy" <?php if ($rank == "Senior Envoy") echo "selected"; ?>>Senior Envoy</option>
                <option value="Special Envoy" <?php if ($rank == "Special Envoy") echo "selected"; ?>>Special Envoy</option>
                <option value="Ambassador" <?php if ($rank == "Ambassador") echo "selected"; ?>>Ambassador</option>
                <option value="Ambassador Extraordinary" <?php if ($rank == "Ambassador Extraordinary") echo "selected"; ?>>Ambassador Extraordinary</option>
                <option value="Ambassador Plenipotentiary" <?php if ($rank == "Ambassador Plenipotentiary") echo "selected"; ?>>Ambassador Plenipotentiary</option>
            </select>
            <span class="help-block"><?php echo $rank_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password">
            <span class="help-block"><?php echo $password_err; ?></span>
          </div>

          <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm password">
            <span class="help-block"><?php echo $confirm_password_err; ?></span>
          </div>
        </div>

        <button type="submit" class="signup-btn">Create Account</button>
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
      </form>
    </div>
  </div>

</body>
</html>
