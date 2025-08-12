<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

$admin_id = $_SESSION['id'];

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $transaction_id = $_POST['transaction_id'];
    $new_status = $_POST['status']; // 'verified' or 'rejected'

    $sql = "UPDATE finance_transactions SET status = ?, verified_by = ?, verified_at = NOW() WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sii", $new_status, $admin_id, $transaction_id);
        $stmt->execute();
        $stmt->close();
    }
    header("location: finance-management.php");
    exit;
}

// Fetch all transactions
$transactions = [];
$sql = "SELECT ft.id, ft.description, ft.receipt_path, ft.status, ft.uploaded_at, u.full_name as president_name, u.association
        FROM finance_transactions ft
        JOIN users u ON ft.user_id = u.id
        ORDER BY ft.uploaded_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .table-container { background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Super Admin Only -->
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li class="active"><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Finance & Voucher Management</h1>

        <div class="table-container">
            <h2>Pending Verifications</h2>
            <table>
                <thead>
                    <tr>
                        <th>President</th>
                        <th>Association</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans) : ?>
                        <?php if ($trans['status'] == 'pending'): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trans['president_name']); ?></td>
                            <td><?php echo htmlspecialchars($trans['association']); ?></td>
                            <td><?php echo htmlspecialchars($trans['description']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['uploaded_at'])); ?></td>
                            <td><a href="<?php echo htmlspecialchars(str_replace('../', '', $trans['receipt_path'])); ?>" target="_blank">View</a></td>
                            <td>
                                <form action="finance-management.php" method="post" style="display:inline;">
                                    <?php csrf_input_field(); ?>
                                    <input type="hidden" name="transaction_id" value="<?php echo $trans['id']; ?>">
                                    <input type="hidden" name="status" value="verified">
                                    <button type="submit" name="update_status">Verify</button>
                                </form>
                                <form action="finance-management.php" method="post" style="display:inline;">
                                    <?php csrf_input_field(); ?>
                                    <input type="hidden" name="transaction_id" value="<?php echo $trans['id']; ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" name="update_status" style="background: #c00;">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container" style="margin-top: 30px;">
            <h2>Transaction History</h2>
             <table>
                <thead>
                    <tr>
                        <th>President</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans) : ?>
                        <?php if ($trans['status'] != 'pending'): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trans['president_name']); ?></td>
                            <td><?php echo htmlspecialchars($trans['description']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['uploaded_at'])); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($trans['status'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
