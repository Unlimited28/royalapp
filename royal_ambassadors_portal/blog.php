<?php
$page_title = "Blog";
require_once "includes/db_connect.php";
include 'includes/header.php';

// Fetch posts
$posts = [];
$post_result = $mysqli->query("SELECT id, title, content, image_path FROM posts ORDER BY created_at DESC");
while ($row = $post_result->fetch_assoc()) {
    $posts[] = $row;
}

// Fetch ads
$ads = [];
$ads_result = $mysqli->query("SELECT content_path, link_url, ad_title FROM ads WHERE status = 'active' AND (expiry_date IS NULL OR expiry_date >= CURDATE()) LIMIT 2");
while ($row = $ads_result->fetch_assoc()) {
    $ads[] = $row;
}

$mysqli->close();
?>
<link rel="stylesheet" href="styles/blog.css">
<style>
    .page-header { text-align: center; padding: 50px 20px; background-color: #f4f7f9; }
    .blog-layout { display: flex; gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .blog-main { flex: 3; }
    .blog-sidebar { flex: 1; }
    .blog-card { margin-bottom: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .blog-card img { width: 100%; height: 250px; object-fit: cover; border-top-left-radius: 8px; border-top-right-radius: 8px;}
    .blog-card-content { padding: 20px; }
    .sidebar-widget { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
    .sidebar-widget ul { list-style: none; padding: 0; }
    .sidebar-widget .ads-list img { max-width: 100%; border-radius: 8px; margin-bottom: 10px; }
    .pagination { text-align: center; margin-top: 40px; }
</style>

<header class="page-header">
    <h1>Our Blog</h1>
    <p>News, stories, and insights from the Royal Ambassadors.</p>
</header>

<div class="blog-layout">
    <main class="blog-main">
        <?php foreach ($posts as $post): ?>
        <article class="blog-card">
            <?php if ($post['image_path']): ?>
            <img src="<?php echo htmlspecialchars(str_replace('../', '', $post['image_path'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <?php endif; ?>
            <div class="blog-card-content">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <p><?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)); ?>...</p>
                <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
            </div>
        </article>
        <?php endforeach; ?>

        <div class="pagination">
            <!-- Pagination logic would go here -->
        </div>
    </main>

    <aside class="blog-sidebar">
        <div class="sidebar-widget">
            <h3>Categories</h3>
            <ul>
                <li><a href="#">Leadership</a></li>
                <li><a href="#">Events</a></li>
                <li><a href="#">Faith</a></li>
            </ul>
        </div>
        <div class="sidebar-widget">
            <h3>Sponsored Ads</h3>
            <div class="ads-list">
                <?php foreach ($ads as $ad): ?>
                <a href="<?php echo htmlspecialchars($ad['link_url']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars(str_replace('../', '', $ad['content_path'])); ?>" alt="<?php echo htmlspecialchars($ad['ad_title']); ?>">
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>

<?php include 'includes/footer.php'; ?>
