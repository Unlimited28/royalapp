<?php
$page_title = "Home";
require_once "includes/db_connect.php";
require_once "includes/functions.php";
include 'includes/header.php';

// Fetch settings
$settings = [];
$result = $mysqli->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch latest 3 blog posts
$latest_posts = [];
$post_result = $mysqli->query("SELECT id, title, image_path FROM posts ORDER BY created_at DESC LIMIT 3");
while ($row = $post_result->fetch_assoc()) {
    $latest_posts[] = $row;
}

// Fetch latest 4 gallery images
$gallery_images = [];
$gallery_result = $mysqli->query("SELECT image_path, caption FROM gallery ORDER BY uploaded_at DESC LIMIT 4");
while ($row = $gallery_result->fetch_assoc()) {
    $gallery_images[] = $row;
}

// Fetch 4 active ads
$ads = [];
$ads_result = $mysqli->query("SELECT content_path, link_url, ad_title FROM ads WHERE status = 'active' AND (expiry_date IS NULL OR expiry_date >= CURDATE()) LIMIT 4");
while ($row = $ads_result->fetch_assoc()) {
    $ads[] = $row;
}

$mysqli->close();
?>
<link rel="stylesheet" href="styles/index.css">
<style>
    .section-title { text-align: center; margin-bottom: 40px; font-size: 28px; color: #001f3f; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .card { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; text-align: left; }
    .card img { width: 100%; height: 200px; object-fit: cover; }
    .card-content { padding: 20px; }
    .event-item, .ad-item { text-align: center; padding: 20px; }
    .ad-item img { height: 150px; }
    @media (max-width: 768px) { .grid-3, .grid-4 { grid-template-columns: 1fr; } }
</style>

<!-- Hero Banner -->
<div class="container hero-text">
    <h2><?php echo htmlspecialchars($settings['tagline'] ?? 'Touching the lives of boys...'); ?></h2>
    <p class="bible-quote">“We are Ambassadors for Christ” – 2 Corinthians 5:20</p>
    <div class="hero-buttons">
        <a href="login.php" class="btn">Log In</a>
        <a href="register.php" class="btn">Sign Up</a>
    </div>
</div>

<!-- About Snippet -->
<section class="welcome-section container">
    <div class="welcome-content">
        <img src="assets/director.jpg" alt="Director" class="director-img" />
        <div class="welcome-text">
            <h2>Welcome to <?php echo htmlspecialchars($settings['org_name'] ?? 'OGBC RA'); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($settings['about_excerpt'] ?? 'Welcome to our official website.')); ?></p>
            <a href="about.php" class="btn btn-primary">Learn More</a>
        </div>
    </div>
</section>

<!-- Latest Blogs -->
<section class="container">
    <h2 class="section-title">Latest from Our Blog</h2>
    <div class="grid-3">
        <?php foreach($latest_posts as $post): ?>
        <div class="card">
            <img src="<?php echo htmlspecialchars(str_replace('../', '', $post['image_path'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <div class="card-content">
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <a href="blog-post.php?id=<?php echo $post['id']; ?>">Read More</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Gallery Preview -->
<section class="container">
    <h2 class="section-title">Gallery Highlights</h2>
    <div class="grid-4">
        <?php foreach($gallery_images as $image): ?>
        <div class="card"><img src="<?php echo htmlspecialchars(str_replace('../', '', $image['image_path'])); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>"></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Upcoming Events -->
<section class="container">
    <h2 class="section-title">Upcoming Events</h2>
    <div class="grid-3">
        <div class="card event-item"><h3>State Congress</h3><p>June 2024</p></div>
        <div class="card event-item"><h3>Annual Camp</h3><p>August 2024</p></div>
        <div class="card event-item"><h3>Leadership Summit</h3><p>October 2024</p></div>
    </div>
</section>

<!-- Sponsored Ads -->
<section class="container">
    <h2 class="section-title">Our Sponsors</h2>
    <div class="grid-4">
        <?php foreach($ads as $ad): ?>
        <div class="card ad-item">
            <a href="<?php echo htmlspecialchars($ad['link_url']); ?>" target="_blank">
                <img src="<?php echo htmlspecialchars(str_replace('../', '', $ad['content_path'])); ?>" alt="<?php echo htmlspecialchars($ad['ad_title']); ?>">
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Admin Access Section -->
<section class="admin-access-section" style="margin: 60px auto; max-width: 800px; background-color: #f9f9f9; padding: 40px 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
  <h2 style="color: #001f3f; font-size: 24px; margin-bottom: 15px;">Administrator Portal Access</h2>
  <div style="margin-top: 25px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
    <a href="admin/login.php" class="btn btn-secondary">🔐 Admin Login</a>
    <a href="admin-signup.php" class="btn btn-primary">📝 Admin Register</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
