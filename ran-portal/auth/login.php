<?php
session_start();
require_once '../database/connection.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);

    if ($role === 'ambassador') {
        $sql = "SELECT * FROM users WHERE email = ?";
    } elseif ($role === 'admin') {
        $sql = "SELECT * FROM admins WHERE email = ?";
    } elseif ($role === 'super_admin') {
        $sql = "SELECT * FROM super_admins WHERE email = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['role'] = $role;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($role === 'ambassador') {
                header('Location: ../ambassador/index.php');
            } elseif ($role === 'admin') {
                header('Location: ../admin/index.php');
            } elseif ($role === 'super_admin') {
                header('Location: ../superadmin/index.php');
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email address.";
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
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Login</h1>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form action="login.php" method="post">
        <label for="role">Role:</label>
        <select name="role" id="role">
            <option value="ambassador">Ambassador</option>
            <option value="admin">Association President</option>
            <option value="super_admin">Super Admin</option>
        </select>
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
