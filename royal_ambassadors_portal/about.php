<?php
$page_title = "About Us";
require_once "includes/db_connect.php";
include 'includes/header.php';

// Fetch settings for about page
$settings = [];
$result = $mysqli->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('org_name', 'tagline', 'history', 'mission', 'vision')");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();
?>
<link rel="stylesheet" href="styles/about.css" />
<style>
    .hero { text-align: center; padding: 50px 20px; background-color: #001f3f; color: white; }
    .image-banner img { width: 100%; height: 300px; object-fit: cover; }
    .about-content, .leadership-content { max-width: 800px; margin: 40px auto; text-align: center; }
    .mission-vision { display: flex; gap: 40px; margin: 40px 0; }
    .mission-vision > div { flex: 1; padding: 20px; background: #f9f9f9; border-radius: 8px; }
    .leader-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
    .leader-card { text-align: center; }
    .leader-card img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
</style>

<header class="hero">
    <div class="container">
      <h1>About <?php echo htmlspecialchars($settings['org_name'] ?? ''); ?></h1>
      <p><?php echo htmlspecialchars($settings['tagline'] ?? ''); ?></p>
    </div>
</header>

<section class="image-banner">
    <img src="assets/new_6.jpg" alt="Royal Ambassadors Group Photo" />
</section>

<section class="about-content container">
    <h2>Our Story</h2>
    <p><?php echo nl2br(htmlspecialchars($settings['history'] ?? '')); ?></p>
</section>

<section class="mission-vision container">
    <div class="mission">
      <h2>Our Mission</h2>
      <p><?php echo nl2br(htmlspecialchars($settings['mission'] ?? '')); ?></p>
    </div>
    <div class="vision">
      <h2>Our Vision</h2>
      <p><?php echo nl2br(htmlspecialchars($settings['vision'] ?? '')); ?></p>
    </div>
</section>

<section class="leadership-content container">
    <h2>Meet Our Leaders</h2>
    <div class="leader-grid">
      <!-- This part will be looped in the backend. For now, it's static. -->
      <div class="leader-card">
        <img src="assets/director.jpg" alt="Leader 1" />
        <h3>Coun. Okunlola Moses</h3>
        <p>Conference Director</p>
      </div>
      <div class="leader-card">
        <img src="assets/pro.jpg" alt="Leader 2" />
        <h3>Bro. Emmanuel Okeke</h3>
        <p>Training Director</p>
      </div>
      <div class="leader-card">
        <img src="assets/blog/blog-author.jpg" alt="Leader 3" />
        <h3>Mrs. Grace Adebayo</h3>
        <p>Media & Publicity</p>
      </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
