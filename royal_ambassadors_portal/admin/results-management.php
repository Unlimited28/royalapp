<?php
session_start();
require_once "../includes/functions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_publish_status'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $result_id = $_POST['result_id'];
    $user_id = $_POST['user_id'];
    $new_status = $_POST['is_published'];

    $stmt = $mysqli->prepare("UPDATE exam_results SET is_published = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $result_id);
    $stmt->execute();
    $stmt->close();

    if ($new_status == 1) {
        create_notification($mysqli, $user_id, "Your exam result has been published.", "results.php");
    }

    header("location: results-management.php");
    exit;
}

// Fetch all results
$results = [];
$sql = "SELECT er.id, er.user_id, er.score, er.status, er.is_published, er.submitted_at, u.full_name, e.title as exam_title
        FROM exam_results er
        JOIN users u ON er.user_id = u.id
        JOIN exams e ON er.exam_id = e.id
        ORDER BY er.submitted_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .table-container { background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li class="active"><a href="exam-management.php">Exam Management</a></li>
            <li><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Exam Results Management</h1>
        <div class="table-container">
            <h2>All Submitted Exam Results</h2>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Published</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $res) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($res['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($res['exam_title']); ?></td>
                        <td><?php echo htmlspecialchars($res['score']); ?>%</td>
                        <td><?php echo ucfirst($res['status']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($res['submitted_at'])); ?></td>
                        <td><?php echo $res['is_published'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form action="results-management.php" method="post">
                                <?php csrf_input_field(); ?>
                                <input type="hidden" name="result_id" value="<?php echo $res['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $res['user_id']; ?>">
                                <?php if ($res['is_published']): ?>
                                    <input type="hidden" name="is_published" value="0">
                                    <button type="submit" name="update_publish_status">Unpublish</button>
                                <?php else: ?>
                                    <input type="hidden" name="is_published" value="1">
                                    <button type="submit" name="update_publish_status">Publish</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
