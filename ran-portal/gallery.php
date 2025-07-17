<?php
require_once 'database/connection.php';

$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$images = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
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
        <h1>Gallery</h1>
        <div class="gallery-grid">
            <?php while ($image = $images->fetch_assoc()) { ?>
            <div class="gallery-item">
                <a href="<?php echo 'superadmin/' . substr($image['image_path'], 3); ?>" data-lightbox="gallery" data-title="<?php echo $image['caption']; ?>">
                    <img src="<?php echo 'superadmin/' . substr($image['image_path'], 3); ?>" alt="<?php echo $image['caption']; ?>">
                </a>
            </div>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2023 Royal Ambassadors of Nigeria, Ogun Baptist Conference. All Rights Reserved.</p>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>
