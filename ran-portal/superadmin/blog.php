<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_post'])) {
        $title = sanitize($_POST['title']);
        $content = sanitize($_POST['content']);
        $author_id = $_SESSION['user_id'];
        $author_role = $_SESSION['role'];
        $image = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/blog/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        $sql = "INSERT INTO blog_posts (title, content, image, author_id, author_role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $title, $content, $image, $author_id, $author_role);
        $stmt->execute();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $sql = "DELETE FROM blog_posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    header('Location: blog.php');
    exit();
}

$sql = "SELECT * FROM blog_posts ORDER BY created_at DESC";
$posts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Blog Management</h1>
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
        <h2>Create Post</h2>
        <form action="blog.php" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Content" required></textarea>
            <input type="file" name="image" accept="image/*">
            <button type="submit" name="create_post">Create Post</button>
        </form>

        <h2>All Posts</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = $posts->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $post['title']; ?></td>
                    <td><?php echo $post['created_at']; ?></td>
                    <td>
                        <a href="blog.php?action=delete&id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
