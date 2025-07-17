<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ambassador') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$exam_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if the user is eligible to take the exam
$sql = "SELECT exams.* FROM exams
        JOIN users ON exams.rank_id = users.rank_id
        WHERE users.id = ? AND exams.id = ? AND exams.status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: exams.php');
    exit();
}

$exam = $result->fetch_assoc();

// Get the questions for the exam
$sql = "SELECT * FROM questions WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$questions = $stmt->get_result();

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    foreach ($_POST['answers'] as $question_id => $answer) {
        $question_id = sanitize($question_id);
        $answer = sanitize($answer);
        $sql = "SELECT answer FROM questions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $correct_answer = $result->fetch_assoc()['answer'];
        if ($answer === $correct_answer) {
            $score++;
        }
    }

    $total_questions = $questions->num_rows;
    $percentage = ($score / $total_questions) * 100;

    // Save the result
    $sql = "INSERT INTO results (user_id, exam_id, score) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $exam_id, $percentage);
    $stmt->execute();

    // Auto-promote if score is >= 40%
    if ($percentage >= 40) {
        $sql = "UPDATE users SET rank_id = rank_id + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    header('Location: results.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1><?php echo $exam['title']; ?></h1>
    </header>
    <main>
        <form action="take_exam.php?id=<?php echo $exam_id; ?>" method="post">
            <?php while ($question = $questions->fetch_assoc()) { ?>
            <div class="question">
                <h3><?php echo $question['question']; ?></h3>
                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A" required> <?php echo $question['option_a']; ?><br>
                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B"> <?php echo $question['option_b']; ?><br>
                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C"> <?php echo $question['option_c']; ?><br>
                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D"> <?php echo $question['option_d']; ?><br>
            </div>
            <?php } ?>
            <button type="submit">Submit Exam</button>
        </form>
    </main>
</body>
</html>
