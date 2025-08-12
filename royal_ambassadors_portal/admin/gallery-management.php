<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

$upload_err = "";

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_image'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $caption = trim($_POST['caption']);

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $target_dir = "../uploads/gallery/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($_FILES["image"]["name"]);
            $destination_path = $target_dir . $filename;

            if (process_image_upload($_FILES["image"]["tmp_name"], $destination_path)) {
                $sql = "INSERT INTO gallery (image_path, caption) VALUES (?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("ss", $destination_path, $caption);
                $stmt->execute();
                $stmt->close();
            } else {
                $upload_err = "Failed to process image.";
            }
        } else {
            $upload_err = "Invalid file type or size is too large (Max 5MB).";
        }
    } else {
        $upload_err = "No file uploaded or an error occurred.";
    }

    if (empty($upload_err)) {
        header("location: gallery-management.php");
        exit;
    }
}

// Handle image deletion
if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if (!validate_csrf_token($_GET['token'])) {
        die('Invalid CSRF token');
    }

    $delete_id = $_GET['delete_id'];

    $sql_select = "SELECT image_path FROM gallery WHERE id = ?";
    $stmt_select = $mysqli->prepare($sql_select);
    $stmt_select->bind_param("i", $delete_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if($row = $result->fetch_assoc()){
        if(file_exists($row['image_path'])){
            unlink($row['image_path']);
        }
    }
    $stmt_select->close();

    $sql_delete = "DELETE FROM gallery WHERE id = ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    header("location: gallery-management.php");
    exit;
}

// Fetch all images
$images = [];
$sql = "SELECT id, image_path, caption, uploaded_at FROM gallery ORDER BY uploaded_at DESC";
if ($result = $mysqli->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .form-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .gallery-item { border: 1px solid #ddd; padding: 10px; border-radius: 8px; text-align: center; }
        .gallery-item img { max-width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
        .gallery-item a { color: red; }
    </style>
</head>
<body>
<!-- ROLE: Super Admin Only -->
<div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li><a href="#">Exam Management</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li class="active"><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Gallery Management</h1>

        <?php if(!empty($upload_err)): ?>
            <div style="color: red; padding: 10px; background: #ffeeee; border-radius: 5px; margin-bottom: 20px;"><?php echo $upload_err; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Upload New Image</h2>
            <form action="gallery-management.php" method="post" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Image File (JPEG or PNG, Max 5MB)</label>
                    <input type="file" name="image" required>
                </div>
                <div class="form-group">
                    <label>Caption</label>
                    <input type="text" name="caption" placeholder="e.g., Annual Camp 2023">
                </div>
                <button type="submit" name="upload_image">Upload Image</button>
            </form>
        </div>

        <div class="form-container">
            <h2>Filter Gallery</h2>
            <form action="gallery-management.php" method="get">
                <div class="form-group">
                    <label>Search by caption</label>
                    <input type="text" name="search" placeholder="Enter caption keyword...">
                </div>
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="gallery-grid">
            <!-- Placeholder for filtered images -->
            <div class="gallery-item">
                <img src="<?php echo htmlspecialchars(str_replace('../', '', $image['image_path'])); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                <p><?php echo htmlspecialchars($image['caption']); ?></p>
                <a href="gallery-management.php?delete_id=<?php echo $image['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
</body>
</html>
