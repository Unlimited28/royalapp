<?php
require_once '../database/connection.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = password_hash(sanitize($_POST['password']), PASSWORD_DEFAULT);

    if ($role === 'ambassador') {
        $phone = sanitize($_POST['phone']);
        $church = sanitize($_POST['church']);
        $association_id = sanitize($_POST['association_id']);
        $age = sanitize($_POST['age']);
        $rank_id = sanitize($_POST['rank_id']);

        // Generate user ID
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $user_count = $row['count'];
        $user_id = "OGBC/RA/" . str_pad($user_count + 1, 4, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO users (user_id, full_name, email, phone, church, association_id, age, rank_id, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiiss", $user_id, $full_name, $email, $phone, $church, $association_id, $age, $rank_id, $password);
    } elseif ($role === 'admin') {
        $phone = $_POST['phone'];
        $association_id = $_POST['association_id'];
        $passcode = $_POST['passcode'];

        if ($passcode !== 'ogbc//assets//president') {
            die('Invalid passcode for admin sign-up.');
        }

        $sql = "INSERT INTO admins (full_name, email, phone, association_id, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $full_name, $email, $phone, $association_id, $password);
    } elseif ($role === 'super_admin') {
        $passcode = $_POST['passcode'];

        if ($passcode !== 'ogbc//assets//super') {
            die('Invalid passcode for super admin sign-up.');
        }

        $sql = "INSERT INTO super_admins (full_name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $full_name, $email, $password);
    }

    if ($stmt->execute()) {
        header('Location: login.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Sign Up</h1>
    <form action="signup.php" method="post">
        <label for="role">Role:</label>
        <select name="role" id="role" onchange="showFields()">
            <option value="ambassador">Ambassador</option>
            <option value="admin">Association President</option>
            <option value="super_admin">Super Admin</option>
        </select>

        <div id="ambassador-fields">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <label for="phone">Phone:</label>
            <input type="text" name="phone" required>
            <label for="church">Church Name:</label>
            <input type="text" name="church" required>
            <label for="association_id">Association:</label>
            <select name="association_id" required>
                <?php
                require_once '../database/connection.php';
                $sql = "SELECT * FROM associations";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <label for="age">Age:</label>
            <input type="number" name="age" required>
            <label for="rank_id">Rank:</label>
            <select name="rank_id" required>
                <?php
                $sql = "SELECT * FROM ranks";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
        </div>

        <div id="admin-fields" style="display: none;">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" >
            <label for="email">Email:</label>
            <input type="email" name="email" >
            <label for="phone">Phone:</label>
            <input type="text" name="phone" >
            <label for="association_id">Association:</label>
            <select name="association_id" >
                <?php
                $sql = "SELECT * FROM associations";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <label for="passcode">Passcode:</label>
            <input type="password" name="passcode" >
        </div>

        <div id="super-admin-fields" style="display: none;">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" >
            <label for="email">Email:</label>
            <input type="email" name="email" >
            <label for="passcode">Passcode:</label>
            <input type="password" name="passcode" >
        </div>

        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        <script>
            var password = document.getElementById("password")
            , confirm_password = document.getElementById("confirm_password");

            function validatePassword(){
            if(password.value != confirm_password.value) {
                confirm_password.setCustomValidity("Passwords Don't Match");
            } else {
                confirm_password.setCustomValidity('');
            }
            }

            password.onchange = validatePassword;
            confirm_password.onkeyup = validatePassword;
        </script>

        <button type="submit">Sign Up</button>
    </form>

    <script>
        function showFields() {
            var role = document.getElementById('role').value;
            document.getElementById('ambassador-fields').style.display = 'none';
            document.getElementById('admin-fields').style.display = 'none';
            document.getElementById('super-admin-fields').style.display = 'none';

            if (role === 'ambassador') {
                document.getElementById('ambassador-fields').style.display = 'block';
            } else if (role === 'admin') {
                document.getElementById('admin-fields').style.display = 'block';
            } else if (role === 'super_admin') {
                document.getElementById('super-admin-fields').style.display = 'block';
            }
        }
    </script>
</body>
</html>
