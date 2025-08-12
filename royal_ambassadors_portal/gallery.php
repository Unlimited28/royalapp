<?php
$page_title = "Gallery";
require_once "includes/db_connect.php";
include 'includes/header.php';

// Fetch gallery images
$images = [];
$result = $mysqli->query("SELECT image_path, caption FROM gallery ORDER BY uploaded_at DESC");
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$mysqli->close();
?>
<style>
    .page-header { text-align: center; padding: 50px 20px; background-color: #f4f7f9; }
    .gallery-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .gallery-item a { display: block; text-decoration: none; color: inherit; }
    .gallery-item img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; transition: transform 0.3s ease; }
    .gallery-item:hover img { transform: scale(1.05); }

    /* Lightbox styles */
    .lightbox { display: none; position: fixed; z-index: 999; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
    .lightbox img { max-width: 90%; max-height: 80%; }
    .lightbox .close { position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer; }
</style>

<header class="page-header">
    <h1>Event Gallery</h1>
    <p>A collection of photos from our past events and activities.</p>
</header>

<div class="gallery-container">
    <div class="gallery-grid">
        <?php foreach ($images as $image): ?>
        <div class="gallery-item">
            <a href="<?php echo htmlspecialchars(str_replace('../', '', $image['image_path'])); ?>" class="lightbox-trigger">
                <img src="<?php echo htmlspecialchars(str_replace('../', '', $image['image_path'])); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Lightbox container -->
<div id="lightbox" class="lightbox">
    <span class="close">&times;</span>
    <img class="lightbox-content" id="lightbox-img">
</div>

<script>
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('lightbox-trigger')) {
        e.preventDefault();
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        lightboxImg.src = e.target.parentElement.href;
        lightbox.style.display = 'flex';
    }
    if (e.target.classList.contains('close') || e.target.id === 'lightbox') {
        document.getElementById('lightbox').style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
