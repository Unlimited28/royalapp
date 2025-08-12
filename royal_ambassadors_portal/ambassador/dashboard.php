<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'ambassador') {
    header("location: ../login.php");
    exit;
}

require_once "../includes/db_connect.php";
$notification_count = get_unread_notification_count($mysqli, $_SESSION['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ambassador Dashboard</title>
  <link rel="stylesheet" href="../styles/dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
  <div class="logo-box">
    <img src="../assets/logo.png" alt="Logo" class="logo-img">
    <h2 class="role">Ambassador</h2>
  </div>
  <ul class="nav">
    <li class="active"><a href="dashboard.php">Dashboard</a></li>
    <li><a href="../exams.php">Exams</a></li>
    <li><a href="../results.php">Results</a></li>
    <li><a href="../gallery.php">Gallery</a></li>
    <li><a href="../blog.php">Blog</a></li>
    <li><a href="#">Materials</a></li>
    <li><a href="#">Payments</a></li>
    <li><a href="../support.php">Support</a></li>
    <li><a href="../about.php">About Us</a></li>
    <li><a href="../auth/logout.php">Log out</a></li>
  </ul>
</aside>


    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Profile Card -->
      <div class="profile-card">
        <div class="profile-info">
          <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
<p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
<?php
// These session variables are not set at login, so I will add a check for them.
if (isset($_SESSION['rank'])) {
    echo "<p>Rank: " . htmlspecialchars($_SESSION['rank']) . "</p>";
}
if (isset($_SESSION['association'])) {
    echo "<p>Association: " . htmlspecialchars($_SESSION['association']) . "</p>";
}
?>
        </div>
        <div class="profile-actions">
            <a href="../notifications.php" class="notification-bell">
                <span>🔔</span>
                <?php if ($notification_count > 0): ?>
                    <span class="badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </a>
            <img src="../assets/director.jpg" alt="Profile Picture" class="avatar">
        </div>
      </div>

      <!-- Statistics -->
      <div class="stats-grid">
        <div class="stat-card">
          <p>Exams Written</p>
          <h3>1</h3>
        </div>
        <div class="stat-card">
          <p>Intending Rank</p>
          <h3>Senior Envoy</h3>
        </div>
        <div class="stat-card">
          <p>Events Attended</p>
          <h3>3</h3>
        </div>
        <div class="stat-card">
          <p>Materials</p>
          <h3>2</h3>
        </div>
      </div>

      <!-- Notifications -->
      <section class="notifications">
        <h3>Notifications</h3>
        <div class="note-item">
          <p>New exam available</p>
          <a href="#" class="btn">Take Exam</a>
        </div>
        <div class="note-item">
          <p>Payment due for dues</p>
          <a href="#" class="btn">Pay Now</a>
        </div>
        <div class="note-item">
          <p>Congratulations: You have been promoted to Senior Envoy</p>
        </div>
      </section>
    </main>
  </div>

</body>
</html>
