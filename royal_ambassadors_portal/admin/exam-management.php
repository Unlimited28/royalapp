<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

// Handle form submission for creating exams
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_exam'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $title = trim($_POST['title']);
    $rank = trim($_POST['rank_association']);

    if (!empty($title) && !empty($rank)) {
        $sql = "INSERT INTO exams (title, rank_association) VALUES (?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ss", $title, $rank);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("location: exam-management.php");
    exit;
}

// Handle exam deletion
if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if (!validate_csrf_token($_GET['token'])) {
        die('Invalid CSRF token');
    }
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM exams WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("location: exam-management.php");
    exit;
}

// Fetch all exams
$exams = [];
$sql = "SELECT id, title, rank_association, created_at FROM exams ORDER BY created_at DESC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
}
$mysqli->close();

$ranks = [
    'Candidate', 'Assistant Intern', 'Intern', 'Senior Intern', 'Envoy',
    'Senior Envoy', 'Special Envoy', 'Ambassador', 'Ambassador Extraordinary', 'Ambassador Plenipotentiary'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Management</title>
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
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../assets/logo.png" alt="Logo" class="logo-img">
            <h2 class="role">Super Admin</h2>
        </div>
        <ul class="nav">
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="#">User Management</a></li>
            <li class="active"><a href="exam-management.php">Exam Management</a></li>
            <li><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Exam Management</h1>

        <div class="form-container">
            <h2>Create New Exam</h2>
            <form action="exam-management.php" method="post">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label>Exam Title</label>
                    <input type="text" name="title" placeholder="e.g., Candidate Exam 2024" required>
                </div>
                <div class="form-group">
                    <label>For Rank</label>
                    <select name="rank_association" required>
                        <option value="">Select Rank</option>
                        <?php foreach ($ranks as $rank): ?>
                            <option value="<?php echo $rank; ?>"><?php echo $rank; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="create_exam">Create Exam</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Existing Exams</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>For Rank</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $exam) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exam['title']); ?></td>
                        <td><?php echo htmlspecialchars($exam['rank_association']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($exam['created_at'])); ?></td>
                        <td>
                            <a href="question-management.php?exam_id=<?php echo $exam['id']; ?>">Manage Questions</a> |
                            <a href="exam-management.php?delete_id=<?php echo $exam['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
