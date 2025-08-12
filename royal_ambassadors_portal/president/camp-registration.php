<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'president') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

$president_id = $_SESSION['id'];
$association = $_SESSION['association'];
$upload_err = "";

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_file'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    if (isset($_FILES["camp_file"]) && $_FILES["camp_file"]["error"] == 0) {
        $allowed_mimes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($_FILES['camp_file']['type'], $allowed_mimes) && $_FILES['camp_file']['size'] <= $max_size) {
            $target_dir = "../uploads/camp_files/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

            $original_filename = basename($_FILES["camp_file"]["name"]);
            $filename = uniqid() . '-' . $original_filename;
            $destination_path = $target_dir . $filename;

            if (move_uploaded_file($_FILES["camp_file"]["tmp_name"], $destination_path)) {
                $sql = "INSERT INTO camp_registrations (user_id, association, file_path, original_filename) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("isss", $president_id, $association, $destination_path, $original_filename);
                $stmt->execute();
                $stmt->close();
            } else {
                $upload_err = "Failed to move uploaded file.";
            }
        } else {
            $upload_err = "Invalid file type or size is too large. Only .xls and .xlsx files under 5MB are allowed.";
        }
    } else {
        $upload_err = "No file uploaded or an error occurred.";
    }

    if (empty($upload_err)) {
        header("location: camp-registration.php");
        exit;
    }
}

// Fetch this president's uploads
$uploads = [];
$sql = "SELECT original_filename, uploaded_at FROM camp_registrations WHERE user_id = ? ORDER BY uploaded_at DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $president_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $uploads[] = $row;
    }
    $stmt->close();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camp Registration</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <style>
        .form-container, .table-container { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
<!-- ROLE: Association President Only -->
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">President</h2>
        </div>
        <ul class="nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="member-approval.php">Member Approval</a></li>
            <li class="active"><a href="camp-registration.php">Camp Registration</a></li>
            <li><a href="finance.php">Vouchers</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Camp Registration Upload</h1>
        <p>Please upload the completed Excel file with the list of boys attending the upcoming camp.</p>

        <?php if(!empty($upload_err)): ?>
            <div style="color: red; padding: 10px; background: #ffeeee; border-radius: 5px; margin-bottom: 20px;"><?php echo $upload_err; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Upload Registration File</h2>
            <form action="camp-registration.php" method="post" enctype="multipart/form-data">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Excel File (.xls, .xlsx)</label>
                    <input type="file" name="camp_file" accept=".xls,.xlsx" required>
                </div>
                <button type="submit" name="upload_file">Upload File</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Your Upload History</h2>
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Upload Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($uploads as $upload): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($upload['original_filename']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($upload['uploaded_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($uploads)) echo '<tr><td colspan="2">No files uploaded yet.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
