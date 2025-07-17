<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$admin_id = $_SESSION['user_id'];

// Get the admin's association ID
$sql = "SELECT association_id FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$association_id = $result->fetch_assoc()['association_id'];

// Get the users in the admin's association
$sql = "SELECT users.*, ranks.name as rank_name FROM users
        JOIN ranks ON users.rank_id = ranks.id
        WHERE users.association_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $association_id);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Boys</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Manage Boys</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="users.php">Manage Boys</a></li>
            <li><a href="exams.php">Approve Exams</a></li>
            <li><a href="uploads.php">Upload Files</a></li>
            <li><a href="payments.php">Payment Status</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Users in Your Association</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Church</th>
                    <th>Age</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone']; ?></td>
                    <td><?php echo $user['church']; ?></td>
                    <td><?php echo $user['age']; ?></td>
                    <td><?php echo $user['rank_name']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
