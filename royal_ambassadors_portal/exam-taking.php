<?php
session_start();
require_once "includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'ambassador') {
    header("location: login.php");
    exit;
}

require_once "includes/db_connect.php";

if (!isset($_GET['exam_id'])) {
    header("location: exams.php");
    exit;
}

$exam_id = $_GET['exam_id'];
$user_id = $_SESSION['id'];

// Full eligibility check
$is_eligible = false;
$stmt_check = $mysqli->prepare("
    SELECT u.approved, e.id as exam_id
    FROM users u
    JOIN exams e ON u.rank = e.rank_association
    LEFT JOIN exam_results er ON e.id = er.exam_id AND er.user_id = u.id
    WHERE u.id = ? AND e.id = ? AND er.id IS NULL
");
$stmt_check->bind_param("ii", $user_id, $exam_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($details = $result_check->fetch_assoc()) {
    if ($details['approved'] == 1) {
        $is_eligible = true;
    }
}
$stmt_check->close();

if (!$is_eligible) {
    // Redirect if not eligible (not approved, wrong rank, or already taken)
    header("location: exams.php");
    exit;
}

// Fetch exam details and questions
$exam = null;
$questions = [];

$stmt_exam = $mysqli->prepare("SELECT title FROM exams WHERE id = ?");
$stmt_exam->bind_param("i", $exam_id);
$stmt_exam->execute();
$result_exam = $stmt_exam->get_result();
$exam = $result_exam->fetch_assoc();
$stmt_exam->close();

if (!$exam) {
    header("location: exams.php");
    exit;
}

$stmt_q = $mysqli->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE exam_id = ? ORDER BY RAND()");
$stmt_q->bind_param("i", $exam_id);
$stmt_q->execute();
$result_q = $stmt_q->get_result();
while ($row = $result_q->fetch_assoc()) {
    $questions[] = $row;
}
$stmt_q->close();
$mysqli->close();

$page_title = "Taking Exam: " . $exam['title'];
include 'includes/header.php';
?>
<style>
    .exam-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
    .question-box { margin-bottom: 30px; }
    .question-box p { font-weight: bold; margin-bottom: 10px; }
    .options label { display: block; margin-bottom: 10px; background: #f9f9f9; padding: 10px; border-radius: 5px; }
    #timer { position: fixed; top: 20px; right: 20px; background: #001f3f; color: #fff; padding: 10px; border-radius: 5px; font-weight: bold; }
</style>

<div id="timer">Time Left: 30:00</div>

<div class="exam-container">
    <h1><?php echo htmlspecialchars($exam['title']); ?></h1>
    <p>Please answer all questions. The exam will be submitted automatically when the timer runs out.</p>

    <form action="submit-exam.php" method="post" id="exam-form">
        <?php csrf_input_field(); ?>
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-box">
                <p>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['question_text']); ?></p>
                <div class="options">
                    <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" required> A) <?php echo htmlspecialchars($q['option_a']); ?></label>
                    <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="B"> B) <?php echo htmlspecialchars($q['option_b']); ?></label>
                    <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="C"> C) <?php echo htmlspecialchars($q['option_c']); ?></label>
                    <label><input type="radio" name="answers[<?php echo $q['id']; ?>]" value="D"> D) <?php echo htmlspecialchars($q['option_d']); ?></label>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" name="submit_exam">Submit Exam</button>
    </form>
</div>

<script>
    function startTimer(duration, display) {
        let timer = duration, minutes, seconds;
        const interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = "Time Left: " + minutes + ":" + seconds;

            if (--timer < 0) {
                clearInterval(interval);
                document.getElementById('exam-form').submit();
            }
        }, 1000);
    }

    window.onload = function () {
        const thirtyMinutes = 60 * 30;
        const display = document.querySelector('#timer');
        startTimer(thirtyMinutes, display);
    };
</script>

<?php include 'includes/footer.php'; ?>
