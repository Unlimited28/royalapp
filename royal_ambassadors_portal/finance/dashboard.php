<?php
session_start();
// This is a static placeholder for the UI build
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
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
            <li class="active"><a href="dashboard.php">Dashboard</a></li>
            <li><a href="voucher-management.php">Voucher Management</a></li>
            <li><a href="payment-requests.php">Payment Requests</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Finance Dashboard</h1>
        <p>Welcome to the Finance Panel. Manage vouchers and payment requests from here.</p>
        <!-- Placeholder for stats -->
    </main>
</div>
</body>
</html>
