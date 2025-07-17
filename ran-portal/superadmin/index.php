<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Super Admin Dashboard</h1>
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
        <h2>Welcome, Super Admin!</h2>
        <div class="stats-cards">
            <div class="card">
                <h3>Total Users</h3>
                <p>[Number]</p>
            </div>
            <div class="card">
                <h3>Active Exams</h3>
                <p>[Number]</p>
            </div>
            <div class="card">
                <h3>Blog Posts</h3>
                <p>[Number]</p>
            </div>
            <div class="card">
                <h3>Vouchers Generated</h3>
                <p>[Number]</p>
            </div>
        </div>
        <div class="quick-actions">
            <a href="users.php" class="button">Manage Users</a>
            <a href="exams.php?action=create" class="button">Create Exam</a>
            <a href="blog.php?action=create" class="button">Post Blog</a>
            <a href="vouchers.php?action=create" class="button">Generate Vouchers</a>
        </div>
    </main>
</body>
</html>
