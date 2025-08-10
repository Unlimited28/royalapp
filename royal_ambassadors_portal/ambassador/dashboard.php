<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}
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
    <li><a href="../exams.html">Exams</a></li>
    <li><a href="../results.html">Results</a></li>
    <li><a href="#">Resources & Materials</a></li>
    <li><a href="#">Payments</a></li>
    <li><a href="../contact.html">Support</a></li>
    <li><a href="../blog.html">Blog</a></li>
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
        <img src="../assets/director.jpg" alt="Profile Picture" class="avatar">
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
