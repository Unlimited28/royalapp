<?php
session_start();
require_once "../includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'superadmin') {
    header("location: ../admin/login.php");
    exit;
}

require_once "../includes/db_connect.php";

if (!isset($_GET['exam_id'])) {
    header("location: exam-management.php");
    exit;
}

$exam_id = $_GET['exam_id'];

// Handle form submission for questions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_question'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) { die('Invalid CSRF token'); }

    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_answer = trim($_POST['correct_answer']);
    $question_id = $_POST['question_id'];

    if (empty($question_id)) {
        $sql = "INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("issssss", $exam_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer);
    } else {
        $sql = "UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssssssi", $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id);
    }
    $stmt->execute();
    $stmt->close();
    header("location: question-management.php?exam_id=" . $exam_id);
    exit;
}

// Handle question deletion
if (isset($_GET['delete_id']) && isset($_GET['token'])) {
    if (!validate_csrf_token($_GET['token'])) { die('Invalid CSRF token'); }
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM questions WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("location: question-management.php?exam_id=" . $exam_id);
    exit;
}

// Fetch exam details
$exam = null;
$stmt = $mysqli->prepare("SELECT title FROM exams WHERE id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();
$stmt->close();

// Fetch questions for this exam
$questions = [];
$stmt = $mysqli->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// Fetch question for editing
$edit_question = ['id' => '', 'question_text' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'correct_answer' => ''];
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $mysqli->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_question = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Questions</title>
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
            <li class="active"><a href="exam-management.php">Exam Management</a></li>
            <li><a href="finance-management.php">Finance</a></li>
            <li><a href="blog-management.php">Blog Management</a></li>
            <li><a href="gallery-management.php">Gallery Management</a></li>
            <li><a href="#">Notifications</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="../auth/logout.php">Log out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h1>Manage Questions for: <?php echo htmlspecialchars($exam['title']); ?></h1>
        <a href="exam-management.php">&laquo; Back to Exam List</a>

        <div class="form-container">
            <h2><?php echo empty($edit_question['id']) ? 'Add New Question' : 'Edit Question'; ?></h2>
            <form action="question-management.php?exam_id=<?php echo $exam_id; ?>" method="post">
                <?php csrf_input_field(); ?>
                <input type="hidden" name="question_id" value="<?php echo $edit_question['id']; ?>">
                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="question_text" rows="3" required><?php echo htmlspecialchars($edit_question['question_text']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Option A</label>
                    <input type="text" name="option_a" value="<?php echo htmlspecialchars($edit_question['option_a']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Option B</label>
                    <input type="text" name="option_b" value="<?php echo htmlspecialchars($edit_question['option_b']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Option C</label>
                    <input type="text" name="option_c" value="<?php echo htmlspecialchars($edit_question['option_c']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Option D</label>
                    <input type="text" name="option_d" value="<?php echo htmlspecialchars($edit_question['option_d']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Correct Answer</label>
                    <select name="correct_answer" required>
                        <option value="A" <?php if($edit_question['correct_answer'] == 'A') echo 'selected'; ?>>A</option>
                        <option value="B" <?php if($edit_question['correct_answer'] == 'B') echo 'selected'; ?>>B</option>
                        <option value="C" <?php if($edit_question['correct_answer'] == 'C') echo 'selected'; ?>>C</option>
                        <option value="D" <?php if($edit_question['correct_answer'] == 'D') echo 'selected'; ?>>D</option>
                    </select>
                </div>
                <button type="submit" name="save_question">Save Question</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Existing Questions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Correct Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($q['question_text'], 0, 80)); ?>...</td>
                        <td><?php echo htmlspecialchars($q['correct_answer']); ?></td>
                        <td>
                            <a href="question-management.php?exam_id=<?php echo $exam_id; ?>&edit_id=<?php echo $q['id']; ?>">Edit</a> |
                            <a href="question-management.php?exam_id=<?php echo $exam_id; ?>&delete_id=<?php echo $q['id']; ?>&token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
