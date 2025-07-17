<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['camp_excel'])) {
        $target_dir = "../uploads/camp_excel/";
        $target_file = $target_dir . basename($_FILES["camp_excel"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "xls" && $file_type != "xlsx") {
            $error = "Only XLS and XLSX files are allowed.";
        } elseif ($_FILES["camp_excel"]["size"] > 5000000) {
            $error = "Sorry, your file is too large.";
        } else {
            if (move_uploaded_file($_FILES["camp_excel"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO camp_registrations (admin_id, excel_path) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $admin_id, $target_file);
                $stmt->execute();
                $success = "Camp registration file uploaded successfully.";
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (isset($_FILES['payment_receipt'])) {
        $target_dir = "../uploads/payment_receipts/";
        $target_file = $target_dir . basename($_FILES["payment_receipt"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "gif") {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES["payment_receipt"]["size"] > 5000000) {
            $error = "Sorry, your file is too large.";
        } else {
            if (move_uploaded_file($_FILES["payment_receipt"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO payment_requests (admin_id, screenshot_path) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $admin_id, $target_file);
                $stmt->execute();
                $success = "Payment receipt uploaded successfully.";
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Files</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Upload Files</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="users.php">Manage Boys</a></li>
            <li><a href="exams.php">Approve Exams</a></li>
            <li><a href="uploads.php">Upload Files</a></li>
            <li><a href="payments.php">Payment Status</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Upload Camp Registration (Excel)</h2>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form action="uploads.php" method="post" enctype="multipart/form-data">
            <input type="file" name="camp_excel" accept=".xls,.xlsx" required>
            <button type="submit">Upload</button>
        </form>

        <h2>Upload Payment Receipt (Screenshot)</h2>
        <form action="uploads.php" method="post" enctype="multipart/form-data">
            <input type="file" name="payment_receipt" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
    </main>
</body>
</html>
