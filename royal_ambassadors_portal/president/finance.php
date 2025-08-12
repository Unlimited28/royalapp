<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'president') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

$upload_err = "";
$president_id = $_SESSION['id'];

// Handle receipt upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_receipt'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $description = trim($_POST['description']);

    if (isset($_FILES["receipt"]) && $_FILES["receipt"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['receipt']['type'], $allowed_types) && $_FILES['receipt']['size'] <= $max_size) {
            $target_dir = "../uploads/receipts/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($_FILES["receipt"]["name"]);
            $destination_path = $target_dir . $filename;

            if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $destination_path)) {
                $sql = "INSERT INTO finance_transactions (user_id, receipt_path, description) VALUES (?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("iss", $president_id, $destination_path, $description);
                $stmt->execute();
                $stmt->close();
            } else {
                $upload_err = "Failed to move uploaded file.";
            }
        } else {
            $upload_err = "Invalid file type or size is too large (Max 5MB).";
        }
    } else {
        $upload_err = "No file uploaded or an error occurred.";
    }

    if (empty($upload_err)) {
        header("location: finance.php");
        exit;
    }
}

// Fetch this president's uploads
$transactions = [];
$sql = "SELECT receipt_path, description, status, uploaded_at FROM finance_transactions WHERE user_id = ? ORDER BY uploaded_at DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $president_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
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
        .form-container, .table-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Association President Only -->
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">President</h2>
        </div>
        <ul class="nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="#">Member Approval</a></li>
            <li><a href="#">Camp Registration</a></li>
            <li class="active"><a href="finance.php">Vouchers</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Finance & Vouchers</h1>

        <?php if(!empty($upload_err)): ?>
            <div style="color: red; padding: 10px; background: #ffeeee; border-radius: 5px; margin-bottom: 20px;"><?php echo $upload_err; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Upload Proof of Payment</h2>
            <form action="finance.php" method="post" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Receipt File (JPG, PNG, or PDF - Max 5MB)</label>
                    <input type="file" name="receipt" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="e.g., Association Dues Q1" required>
                </div>
                <button type="submit" name="upload_receipt">Upload for Verification</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Upload History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $trans) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trans['description']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($trans['status'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($trans['uploaded_at'])); ?></td>
                            <td><a href="<?php echo htmlspecialchars(str_replace('../', '', $trans['receipt_path'])); ?>" target="_blank">View Receipt</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No uploads yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
