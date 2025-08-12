<?php
session_start();
// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-box">
                <img src="../assets/logo.png" alt="Logo" class="logo-img">
                <h2 class="role">Super Admin</h2>
            </div>
            <ul class="nav">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="#">User Management</a></li>
                <li><a href="#">Exam Management</a></li>
                <li><a href="#">Blog Management</a></li>
                <li><a href="#">Gallery Management</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="../auth/logout.php">Log out</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="profile-card">
                <div class="profile-info">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
                    <p>You have full control over the system.</p>
                </div>
            </div>

            <!-- Placeholder for stats and other content -->
            <div class="stats-grid">
                <div class="stat-card">
                    <p>Total Users</p>
                    <h3>0</h3>
                </div>
                <div class="stat-card">
                    <p>Total Exams</p>
                    <h3>0</h3>
                </div>
                <div class="stat-card">
                    <p>Pending Approvals</p>
                    <h3>0</h3>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
