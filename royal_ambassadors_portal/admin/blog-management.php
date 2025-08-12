<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Handle form submission for creating/updating posts
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_post'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']); // TinyMCE will provide clean HTML
    $author_id = $_SESSION['id'];
    $post_id = $_POST['post_id'];

    // Handle file upload
    $image_path = $_POST['existing_image_path'] ?? null; // Keep existing image if new one isn't uploaded
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../uploads/blog/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    if (empty($post_id)) {
        // Create new post
        $sql = "INSERT INTO posts (title, content, image_path, author_id) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssi", $title, $content, $image_path, $author_id);
    } else {
        // Update existing post
        $sql = "UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssi", $title, $content, $image_path, $post_id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Post saved successfully!";
    } else {
        $_SESSION['flash_message'] = "Error: Could not save post.";
    }
    $stmt->close();
    header("location: blog-management.php");
    exit;
}

// Handle post deletion
if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if (!validate_csrf_token($_GET['token'])) {
        die('Invalid CSRF token');
    }
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Post deleted successfully!";
    }
    $stmt->close();
    header("location: blog-management.php");
    exit;
}

// Fetch all posts to display
$posts = [];
$sql = "SELECT p.id, p.title, p.created_at, u.full_name FROM posts p JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC";
if ($result = $mysqli->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
}

// Fetch post for editing
$edit_post = ['id' => '', 'title' => '', 'content' => '', 'image_path' => ''];
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT id, title, content, image_path FROM posts WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_post = $result->fetch_assoc();
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: 'textarea[name="content"]',
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
      });
    </script>
    <style>
        .form-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .table-container { background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
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
            <li class="active"><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Blog Management</h1>

        <?php
        if (isset($_SESSION['flash_message'])) {
            echo '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">' . $_SESSION['flash_message'] . '</div>';
            unset($_SESSION['flash_message']);
        }
        ?>

        <div class="form-container">
            <h2><?php echo empty($edit_post['id']) ? 'Create New Post' : 'Edit Post'; ?></h2>
            <form action="blog-management.php" method="post" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($edit_post['id']); ?>">
                <input type="hidden" name="existing_image_path" value="<?php echo htmlspecialchars($edit_post['image_path']); ?>">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($edit_post['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="15"><?php echo htmlspecialchars($edit_post['content']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" name="image">
                    <?php if (!empty($edit_post['image_path'])): ?>
                        <p><small>Current image: <?php echo basename($edit_post['image_path']); ?>. Upload a new file to replace it.</small></p>
                    <?php endif; ?>
                </div>
                <button type="submit" name="save_post">Save Post</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Existing Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['full_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <a href="blog-management.php?edit_id=<?php echo $post['id']; ?>">Edit</a> |
                            <a href="blog-management.php?delete_id=<?php echo $post['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
