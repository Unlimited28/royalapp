<?php
session_start();
require_once "includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'ambassador') {
    header("location: login.php");
    exit;
}

require_once "includes/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_exam'])) {
    if (!validate_csrf_token($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $exam_id = $_POST['exam_id'];
    $user_id = $_SESSION['id'];
    $answers = $_POST['answers'];

    // Fetch correct answers for the exam
    $correct_answers = [];
    $stmt = $mysqli->prepare("SELECT id, correct_answer FROM questions WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $correct_answers[$row['id']] = $row['correct_answer'];
    }
    $stmt->close();

    // Calculate score
    $score = 0;
    $total_questions = count($correct_answers);
    foreach ($answers as $question_id => $user_answer) {
        if (isset($correct_answers[$question_id]) && $user_answer == $correct_answers[$question_id]) {
            $score++;
        }
    }

    $percentage_score = ($total_questions > 0) ? ($score / $total_questions) * 100 : 0;
    $status = ($percentage_score >= 50) ? 'pass' : 'fail';

    // Check if a result already exists to prevent re-submission
    $sql_check = "SELECT id FROM exam_results WHERE user_id = ? AND exam_id = ?";
    $stmt_check = $mysqli->prepare($sql_check);
    $stmt_check->bind_param("ii", $user_id, $exam_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows == 0) {
        // Save the result to the database
        $sql_insert = "INSERT INTO exam_results (user_id, exam_id, score, status) VALUES (?, ?, ?, ?)";
        if ($stmt_insert = $mysqli->prepare($sql_insert)) {
            $stmt_insert->bind_param("iids", $user_id, $exam_id, $percentage_score, $status);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
    }
    $stmt_check->close();

    $mysqli->close();

    // Redirect to results page
    header("location: results.php");
    exit;
} else {
    // If accessed directly, redirect to exams page
    header("location: exams.php");
    exit;
}
?>
