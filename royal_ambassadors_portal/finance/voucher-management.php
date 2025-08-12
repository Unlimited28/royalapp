<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'finance') {
    header("location: login.php");
    exit;
}

require_once "../includes/db_connect.php";

$finance_user_id = $_SESSION['id'];

// Handle form submission for creating vouchers
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_voucher'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $code = trim($_POST['code']);
    $description = trim($_POST['description']);
    $expiry_date = trim($_POST['expiry_date']);

    if (!empty($code) && !empty($description)) {
        $sql = "INSERT INTO vouchers (code, description, expiry_date, created_by) VALUES (?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssi", $code, $description, $expiry_date, $finance_user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("location: voucher-management.php");
    exit;
}

// Fetch all vouchers
$vouchers = [];
$sql = "SELECT code, description, expiry_date, status FROM vouchers ORDER BY created_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voucher Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .form-container, .table-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Finance Panel Only -->
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Finance Panel</h2>
        </div>
        <ul class="nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li class="active"><a href="voucher-management.php">Voucher Management</a></li>
            <li><a href="payment-requests.php">Payment Requests</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Voucher Management</h1>
        <div class="form-container">
            <h2>Create New Voucher</h2>
            <form action="voucher-management.php" method="post">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Voucher Code</label>
                    <input type="text" name="code" placeholder="e.g., CAMP2024" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="e.g., Camp Registration Fee" required>
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date">
                </div>
                <button type="submit" name="create_voucher">Create Voucher</button>
            </form>
        </div>
        <div class="table-container">
            <h2>Existing Vouchers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Expiry</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($voucher['code']); ?></td>
                        <td><?php echo htmlspecialchars($voucher['description']); ?></td>
                        <td><?php echo htmlspecialchars($voucher['expiry_date']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($voucher['status'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
