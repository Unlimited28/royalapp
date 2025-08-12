<?php
require_once "includes/db_connect.php";
require_once "includes/functions.php";

if (!isset($_GET['id'])) {
    header("location: blog.php");
    exit;
}
$post_id = $_GET['id'];

// Fetch single post
$post = null;
$sql = "SELECT p.title, p.content, p.image_path, p.created_at, u.full_name as author_name
        FROM posts p
        JOIN users u ON p.author_id = u.id
        WHERE p.id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $post = $result->fetch_assoc();
    }
    $stmt->close();
}

if ($post === null) {
    header("location: blog.php");
    exit;
}

$page_title = $post['title'];
include 'includes/header.php';
?>
<link rel="stylesheet" href="styles/blog-post.css">
<style>
    .blog-layout { display: flex; gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .blog-main { flex: 3; }
    .blog-sidebar { flex: 1; }
    .sidebar-widget { margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
    .post-image { width: 100%; height: auto; border-radius: 8px; margin-bottom: 20px; }
    .comments-section, .related-posts { margin-top: 40px; }
</style>

<div class="blog-layout">
    <main class="blog-main">
        <article class="blog-post">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span>By <?php echo htmlspecialchars($post['author_name']); ?></span> |
                <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            </div>

            <?php if ($post['image_path']): ?>
            <img src="<?php echo htmlspecialchars(str_replace('../', '', $post['image_path'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
            <?php endif; ?>

            <div class="post-content">
                <?php echo $post['content']; // Content from TinyMCE is already HTML ?>
            </div>
        </article>

        <section class="related-posts">
            <h2>Related Posts</h2>
            <!-- Placeholder for related posts loop -->
        </section>

        <section class="comments-section">
            <h2>Comments</h2>
            <!-- Placeholder for comments -->
        </section>
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
                <!-- Placeholder for ads -->
            </div>
        </div>
    </aside>
</div>

<?php include 'includes/footer.php'; ?>
