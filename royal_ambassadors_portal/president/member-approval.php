<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'president') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

$president_association = $_SESSION['association'];

// Handle member approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_member'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $member_id = $_POST['member_id'];

    $sql = "UPDATE users SET approved = 1 WHERE id = ? AND association = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("is", $member_id, $president_association);
        if ($stmt->execute()) {
            create_notification($mysqli, $member_id, "Congratulations! Your membership has been approved.", "exams.php");
        }
        $stmt->close();
    }
    header("location: member-approval.php");
    exit;
}

// Fetch members for this president's association
$unapproved_members = [];
$approved_members = [];

// Corrected logic to fetch members
$sql_unapproved = "SELECT id, full_name, email, rank, created_at FROM users WHERE association = ? AND role = 'ambassador' AND approved = 0 ORDER BY created_at DESC";
if ($stmt_unapproved = $mysqli->prepare($sql_unapproved)) {
    $stmt_unapproved->bind_param("s", $president_association);
    $stmt_unapproved->execute();
    $result = $stmt_unapproved->get_result();
    while ($row = $result->fetch_assoc()) {
        $unapproved_members[] = $row;
    }
    $stmt_unapproved->close();
}

$sql_approved = "SELECT id, full_name, email, rank, created_at FROM users WHERE association = ? AND role = 'ambassador' AND approved = 1 ORDER BY created_at DESC";
if ($stmt_approved = $mysqli->prepare($sql_approved)) {
    $stmt_approved->bind_param("s", $president_association);
    $stmt_approved->execute();
    $result = $stmt_approved->get_result();
    while ($row = $result->fetch_assoc()) {
        $approved_members[] = $row;
    }
    $stmt_approved->close();
}


$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Approval</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .table-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Association President Only -->
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">President</h2>
        </div>
        <ul class="nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li class="active"><a href="member-approval.php">Member Approval</a></li>
            <li><a href="#">Camp Registration</a></li>
            <li><a href="finance.php">Vouchers</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Member Approval for <?php echo htmlspecialchars($president_association); ?></h1>

        <div class="table-container">
            <h2>Pending Approval</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Rank</th>
                        <th>Date Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unapproved_members as $member) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['rank']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                        <td>
                            <form action="member-approval.php" method="post">
                                <?php csrf_input_field(); ?>
                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                <button type="submit" name="approve_member">Approve</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($unapproved_members)) echo '<tr><td colspan="5">No members pending approval.</td></tr>'; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Approved Members</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Rank</th>
                        <th>Date Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_members as $member) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['rank']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                     <?php if (empty($approved_members)) echo '<tr><td colspan="4">No approved members yet.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
