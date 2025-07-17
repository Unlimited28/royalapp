<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_image'])) {
        $caption = sanitize($_POST['caption']);
        $image = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/gallery/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        $sql = "INSERT INTO gallery (image_path, caption) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $image, $caption);
        $stmt->execute();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $image_id = $_GET['id'];

    // First, get the image path to delete the file
    $sql = "SELECT image_path FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    if ($image && file_exists($image['image_path'])) {
        unlink($image['image_path']);
    }

    // Then, delete the record from the database
    $sql = "DELETE FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();

    header('Location: gallery.php');
    exit();
}

$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$images = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Gallery Management</h1>
    </header>
    <aside>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="users.php">User Management</a></li>
                <li><a href="admins.php">Admin Management</a></li>
                <li><a href="exams.php">Exam Management</a></li>
                <li><a href="vouchers.php">Voucher Management</a></li>
                <li><a href="blog.php">Blog Management</a></li>
                <li><a href="gallery.php">Gallery Management</a></li>
                <li><a href="payments.php">Finance</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main>
        <h2>Upload Image</h2>
        <form action="gallery.php" method="post" enctype="multipart/form-data">
            <input type="text" name="caption" placeholder="Caption" required>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" name="upload_image">Upload Image</button>
        </form>

        <h2>All Images</h2>
        <div class="gallery-images">
            <?php while ($image = $images->fetch_assoc()) { ?>
            <div class="gallery-item">
                <img src="<?php echo '../' . substr($image['image_path'], 3); ?>" alt="<?php echo $image['caption']; ?>">
                <p><?php echo $image['caption']; ?></p>
                <a href="gallery.php?action=delete&id=<?php echo $image['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>
