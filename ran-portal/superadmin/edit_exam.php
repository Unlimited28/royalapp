<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$exam_id = $_GET['id'];

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $rank_id = sanitize($_POST['rank_id']);
    $duration = sanitize($_POST['duration']);
    $status = sanitize($_POST['status']);

    $sql = "UPDATE exams SET title = ?, rank_id = ?, duration = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisi", $title, $rank_id, $duration, $status, $exam_id);
    $stmt->execute();
    header('Location: exams.php');
    exit();
}

$sql = "SELECT * FROM exams WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

$sql = "SELECT * FROM ranks";
$ranks = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Edit Exam</h1>
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
        <form action="edit_exam.php?id=<?php echo $exam_id; ?>" method="post">
            <input type="text" name="title" placeholder="Exam Title" value="<?php echo $exam['title']; ?>" required>
            <select name="rank_id" required>
                <option value="">Select Rank</option>
                <?php while ($rank = $ranks->fetch_assoc()) { ?>
                <option value="<?php echo $rank['id']; ?>" <?php if ($rank['id'] == $exam['rank_id']) echo 'selected'; ?>><?php echo $rank['name']; ?></option>
                <?php } ?>
            </select>
            <input type="number" name="duration" placeholder="Duration (minutes)" value="<?php echo $exam['duration']; ?>" required>
            <select name="status" required>
                <option value="active" <?php if ($exam['status'] == 'active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if ($exam['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
            </select>
            <button type="submit">Update Exam</button>
        </form>
    </main>
</body>
</html>
