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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $settings_to_update = [
        'org_name', 'tagline', 'org_email', 'org_phone', 'org_address',
        'about_excerpt', 'history', 'mission', 'vision'
    ];

    $stmt = $mysqli->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");

    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
    }
    $stmt->close();
    $_SESSION['flash_message'] = "Site content updated successfully!";
    header("location: site-content-management.php");
    exit;
}

// Fetch current settings
$settings = [];
$result = $mysqli->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Content Management</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .form-container { background: #fff; padding: 20px; border-radius: 8px; }
        textarea { width: 100%; min-height: 100px; }
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
            <li class="active"><a href="site-content-management.php">Site Content</a></li>
            <li><a href="ads-management.php">Ads Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Site Content Management</h1>
        <?php
        if (isset($_SESSION['flash_message'])) {
            echo '<div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">' . $_SESSION['flash_message'] . '</div>';
            unset($_SESSION['flash_message']);
        }
        ?>
        <div class="form-container">
            <form action="site-content-management.php" method="post">
                <?php csrf_input_field(); ?>

                <h2>Homepage Content</h2>
                <div class="form-group">
                    <label>Organization Name</label>
                    <input type="text" name="org_name" value="<?php echo htmlspecialchars($settings['org_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="tagline" value="<?php echo htmlspecialchars($settings['tagline'] ?? ''); ?>">
                </div>
                 <div class="form-group">
                    <label>About Excerpt (for homepage)</label>
                    <textarea name="about_excerpt"><?php echo htmlspecialchars($settings['about_excerpt'] ?? ''); ?></textarea>
                </div>

                <h2>About Page Content</h2>
                 <div class="form-group">
                    <label>History</label>
                    <textarea name="history"><?php echo htmlspecialchars($settings['history'] ?? ''); ?></textarea>
                </div>
                 <div class="form-group">
                    <label>Mission</label>
                    <textarea name="mission"><?php echo htmlspecialchars($settings['mission'] ?? ''); ?></textarea>
                </div>
                 <div class="form-group">
                    <label>Vision</label>
                    <textarea name="vision"><?php echo htmlspecialchars($settings['vision'] ?? ''); ?></textarea>
                </div>

                <h2>Contact Page Details</h2>
                <div class="form-group">
                    <label>Organization Email</label>
                    <input type="email" name="org_email" value="<?php echo htmlspecialchars($settings['org_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Organization Phone</label>
                    <input type="text" name="org_phone" value="<?php echo htmlspecialchars($settings['org_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Organization Address</label>
                    <textarea name="org_address"><?php echo htmlspecialchars($settings['org_address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="save_settings">Save All Settings</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
