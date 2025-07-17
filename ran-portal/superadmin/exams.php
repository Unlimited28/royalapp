<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

// Handle form submissions for creating/editing exams
<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_exam'])) {
        $title = sanitize($_POST['title']);
        $rank_id = sanitize($_POST['rank_id']);
        $duration = sanitize($_POST['duration']);
        $status = sanitize($_POST['status']);

        $sql = "INSERT INTO exams (title, rank_id, duration, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siis", $title, $rank_id, $duration, $status);
        $stmt->execute();
    } elseif (isset($_POST['update_exam'])) {
        $exam_id = $_POST['exam_id'];
        $title = $_POST['title'];
        $rank_id = $_POST['rank_id'];
        $duration = $_POST['duration'];
        $status = $_POST['status'];

        $sql = "UPDATE exams SET title = ?, rank_id = ?, duration = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisi", $title, $rank_id, $duration, $status, $exam_id);
        $stmt->execute();
    }
}

// Handle exam deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    $sql = "DELETE FROM exams WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    header('Location: exams.php');
    exit();
}

$sql = "SELECT exams.*, ranks.name as rank_name FROM exams
        JOIN ranks ON exams.rank_id = ranks.id";
$exams = $conn->query($sql);

$sql = "SELECT * FROM ranks";
$ranks = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Exam Management</h1>
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
        <h2>Create Exam</h2>
        <form action="exams.php" method="post">
            <input type="text" name="title" placeholder="Exam Title" required>
            <select name="rank_id" required>
                <option value="">Select Rank</option>
                <?php while ($rank = $ranks->fetch_assoc()) { ?>
                <option value="<?php echo $rank['id']; ?>"><?php echo $rank['name']; ?></option>
                <?php } ?>
            </select>
            <input type="number" name="duration" placeholder="Duration (minutes)" required>
            <select name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit" name="create_exam">Create Exam</button>
        </form>

        <h2>All Exams</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Rank</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($exam = $exams->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $exam['title']; ?></td>
                    <td><?php echo $exam['rank_name']; ?></td>
                    <td><?php echo $exam['duration']; ?></td>
                    <td><?php echo $exam['status']; ?></td>
                    <td>
                        <a href="edit_exam.php?id=<?php echo $exam['id']; ?>">Edit</a>
                        <a href="exams.php?action=delete&id=<?php echo $exam['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        <a href="questions.php?exam_id=<?php echo $exam['id']; ?>">Manage Questions</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
