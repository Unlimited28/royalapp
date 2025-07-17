<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_voucher'])) {
        $code = uniqid('VOUCHER-');
        $sql = "INSERT INTO vouchers (code) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
    }
}

$sql = "SELECT * FROM vouchers";
$vouchers = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Voucher Management</h1>
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
        <h2>Generate Voucher</h2>
        <form action="vouchers.php" method="post">
            <button type="submit" name="generate_voucher">Generate Voucher</button>
        </form>

        <h2>All Vouchers</h2>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Used</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($voucher = $vouchers->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $voucher['code']; ?></td>
                    <td><?php echo $voucher['is_used'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $voucher['created_at']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
