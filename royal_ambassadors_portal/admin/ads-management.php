<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_ad'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $ad_title = trim($_POST['ad_title']);
    $ad_type = trim($_POST['ad_type']);
    $link_url = trim($_POST['link_url']);
    $status = trim($_POST['status']);
    $expiry_date = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;

    $content_path = '';
    if (isset($_FILES["ad_file"]) && $_FILES["ad_file"]["error"] == 0) {
        $target_dir = "../uploads/ads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $filename = uniqid() . '-' . basename($_FILES["ad_file"]["name"]);
        $content_path = $target_dir . $filename;
        move_uploaded_file($_FILES["ad_file"]["tmp_name"], $content_path);
    }

    $sql = "INSERT INTO ads (ad_title, ad_type, content_path, link_url, status, expiry_date) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssssss", $ad_title, $ad_type, $content_path, $link_url, $status, $expiry_date);
        $stmt->execute();
        $stmt->close();
    }
    header("location: ads-management.php");
    exit;
}

// Handle deletion
if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if (!validate_csrf_token($_GET['token'])) { die('Invalid CSRF token'); }
    $delete_id = $_GET['delete_id'];
    // You should also delete the file from the server
    $stmt = $mysqli->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("location: ads-management.php");
    exit;
}

// Fetch existing ads
$ads = [];
$sql = "SELECT * FROM ads ORDER BY expiry_date DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $ads[] = $row;
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ads Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .form-container, .table-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Super Admin Only -->
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li><a href="exam-management.php">Exam Management</a></li>
            <li><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="site-content-management.php">Site Content</a></li>
            <li class="active"><a href="ads-management.php">Ads Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Sponsored Ads Management</h1>
        <div class="form-container">
            <h2>Add New Ad</h2>
            <form action="ads-management.php" method="post" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Ad Title</label>
                    <input type="text" name="ad_title" required>
                </div>
                <div class="form-group">
                    <label>Ad Type</label>
                    <select name="ad_type" required>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ad File (Image or Video)</label>
                    <input type="file" name="ad_file" required>
                </div>
                <div class="form-group">
                    <label>Link URL (optional)</label>
                    <input type="url" name="link_url" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Expiry Date (optional)</label>
                    <input type="date" name="expiry_date">
                </div>
                <button type="submit" name="save_ad">Save Ad</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Existing Ads</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Expiry</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ads as $ad): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ad['ad_title']); ?></td>
                        <td><?php echo htmlspecialchars($ad['ad_type']); ?></td>
                        <td><?php echo htmlspecialchars($ad['status']); ?></td>
                        <td><?php echo htmlspecialchars($ad['expiry_date']); ?></td>
                        <td>
                            <a href="ads-management.php?delete_id=<?php echo $ad['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
