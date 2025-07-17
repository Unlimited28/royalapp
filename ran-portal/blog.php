<?php
require_once 'database/connection.php';

$sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";
$posts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="assets/logo.png" alt="RA Nigeria Logo">
            <h1>Royal Ambassadors of Nigeria</h1>
            <h2>Ogun Baptist Conference</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/signup.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Blog</h1>
        <div class="blog-posts">
            <?php while ($post = $posts->fetch_assoc()) { ?>
            <div class="post">
                <h2><?php echo $post['title']; ?></h2>
                <p><small>Posted on <?php echo $post['created_at']; ?></small></p>
                <?php if ($post['image']) { ?>
                <img src="<?php echo 'superadmin/' . substr($post['image'], 3); ?>" alt="Blog Image">
                <?php } ?>
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            </div>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2023 Royal Ambassadors of Nigeria, Ogun Baptist Conference. All Rights Reserved.</p>
    </footer>
</body>
</html>
