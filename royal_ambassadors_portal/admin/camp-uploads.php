<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Fetch all uploads
$uploads = [];
$sql = "SELECT cr.original_filename, cr.file_path, cr.uploaded_at, u.full_name as president_name, u.association
        FROM camp_registrations cr
        JOIN users u ON cr.user_id = u.id
        ORDER BY cr.uploaded_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $uploads[] = $row;
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Camp Registrations</title>
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
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li class="active"><a href="camp-uploads.php">Camp Registrations</a></li>
            <li><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>View Camp Registration Files</h1>
        <div class="table-container">
            <h2>Uploaded Files</h2>
            <table>
                <thead>
                    <tr>
                        <th>Association</th>
                        <th>President</th>
                        <th>File Name</th>
                        <th>Upload Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uploads as $upload): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($upload['association']); ?></td>
                        <td><?php echo htmlspecialchars($upload['president_name']); ?></td>
                        <td><?php echo htmlspecialchars($upload['original_filename']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($upload['uploaded_at'])); ?></td>
                        <td><a href="<?php echo htmlspecialchars(str_replace('../', '', $upload['file_path'])); ?>" download>Download</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($uploads)) echo '<tr><td colspan="5">No camp registration files have been uploaded.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
