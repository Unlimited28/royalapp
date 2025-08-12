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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_request'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $association = trim($_POST['association']);
    $title = trim($_POST['title']);
    $amount = trim($_POST['amount']);
    $due_date = trim($_POST['due_date']);

    if (!empty($association) && !empty($title) && !empty($amount)) {
        // Insert into payment_requests table
        $sql = "INSERT INTO payment_requests (association, title, amount, due_date, created_by) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssdsi", $association, $title, $amount, $due_date, $finance_user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Notify all presidents of that association
        $sql_users = "SELECT id FROM users WHERE role = 'president' AND association = ?";
        if ($stmt_users = $mysqli->prepare($sql_users)) {
            $stmt_users->bind_param("s", $association);
            $stmt_users->execute();
            $result = $stmt_users->get_result();
            while($president = $result->fetch_assoc()) {
                create_notification($mysqli, $president['id'], "A new payment request for '$title' has been issued.", "../president/finance.php");
            }
            $stmt_users->close();
        }
    }
    header("location: payment-requests.php");
    exit;
}

// Fetch existing requests
$requests = [];
$sql = "SELECT association, title, amount, due_date, status FROM payment_requests ORDER BY created_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

// Fetch associations for dropdown
$associations = ["Agape Baptist Association", "Abeokuta North West Baptist Association", "Ketu Baptist Association", "Irepodun Oke-Yewa Baptist Association", "Zion Baptist Association", "Abeokuta South Baptist Association", "Ijebu North East Baptist Association", "Great Grace Baptist Association", "Abeokuta East Baptist Association", "Upper Room Baptist Association", "Ijebu North Baptist Association", "Abeokuta North-East Baptist Association", "Abeokuta West Baptist Association", "Bethel Baptist Association", "Ayetoro Baptist Association", "Dominion Baptist Association", "Iroyin Ayo Baptist Association", "Ijebu Central Baptist Association", "Rehoboth Baptist Association", "Christlife Baptist Association", "Ifeoluwa Baptist Association", "Ijebu Progressive Baptist Association", "Yewa Baptist Association", "Ayooluwa Baptist Association", "Macedonia Baptist Association"];

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Requests</title>
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
            <li><a href="voucher-management.php">Voucher Management</a></li>
            <li class="active"><a href="payment-requests.php">Payment Requests</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Send Payment Request</h1>
        <div class="form-container">
            <h2>Create New Request</h2>
            <form action="payment-requests.php" method="post">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Select Association</label>
                    <select name="association" required>
                        <option value="">Select an Association</option>
                        <?php foreach($associations as $assoc): ?>
                            <option value="<?php echo $assoc; ?>"><?php echo $assoc; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Title</label>
                    <input type="text" name="title" placeholder="e.g., Annual Dues 2024" required>
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" required>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date">
                </div>
                <button type="submit" name="send_request">Send Request</button>
            </form>
        </div>
        <div class="table-container">
            <h2>Sent Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Association</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $req): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['association']); ?></td>
                        <td><?php echo htmlspecialchars($req['title']); ?></td>
                        <td><?php echo htmlspecialchars($req['amount']); ?></td>
                        <td><?php echo htmlspecialchars($req['due_date']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($req['status'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
