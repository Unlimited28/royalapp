<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $admin_id = $_GET['id'];
    $sql = "DELETE FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    header('Location: admins.php');
    exit();
}

$sql = "SELECT admins.*, associations.name as association_name FROM admins
        JOIN associations ON admins.association_id = associations.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Management</h1>
    </header>
    <aside>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="users.php">User Management</a></li>
                <li><a href="admins.php">Admin Management</a></li>
                <li><a href="exams.php">Exam Management</a></li>
                <li><a href="vouchers.php">Voucher Management</a></li>
                <li><a href="blog.php">Blog Management</a></li>
                <li><a href="gallery.php">Gallery Management</a></li>
                <li><a href="payments.php">Finance</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <h2>All Admins</h2>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Association</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $admin['full_name']; ?></td>
                    <td><?php echo $admin['email']; ?></td>
                    <td><?php echo $admin['phone']; ?></td>
                    <td><?php echo $admin['association_name']; ?></td>
                    <td>
                        <a href="admins.php?action=delete&id=<?php echo $admin['id']; ?>" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
