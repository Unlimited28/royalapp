<?php
session_start();
$page_title = "Exams";
require_once "includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'ambassador') {
    header("location: login.php");
    exit;
}

require_once "includes/db_connect.php";

$user_id = $_SESSION['id'];

// Fetch user details (rank and approval status)
$user_rank = '';
$user_approved = 0;
$stmt_user = $mysqli->prepare("SELECT `rank`, approved FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($user_details = $result_user->fetch_assoc()) {
    $user_rank = $user_details['rank'];
    $user_approved = $user_details['approved'];
}
$stmt_user->close();

$exams = [];
if ($user_approved) {
    // Fetch exams for the user's rank that they haven't taken yet
    $sql = "SELECT e.id, e.title
            FROM exams e
            LEFT JOIN exam_results er ON e.id = er.exam_id AND er.user_id = ?
            WHERE e.rank_association = ? AND er.id IS NULL";

    if ($stmt_exams = $mysqli->prepare($sql)) {
        $stmt_exams->bind_param("is", $user_id, $user_rank);
        $stmt_exams->execute();
        $result_exams = $stmt_exams->get_result();
        while ($row = $result_exams->fetch_assoc()) {
            $exams[] = $row;
        }
        $stmt_exams->close();
    }
}
$mysqli->close();

include 'includes/header.php';
?>
<style>
    .exams-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .exam-item { border-bottom: 1px solid #eee; padding: 20px 10px; display: flex; justify-content: space-between; align-items: center; }
    .exam-item:last-child { border-bottom: none; }
    .exam-item h3 { margin: 0; color: #001f3f; }
    .exam-item .btn { background-color: #DAA520; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    .message-box { padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; }
</style>

<div class="exams-container">
    <h1>Available Exams</h1>

    <?php if (!$user_approved) : ?>
        <div class="message-box">
            Your account is pending approval from your association's President. You cannot take any exams until your membership is approved.
        </div>
    <?php elseif (empty($exams)) : ?>
        <div class="message-box" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
            There are currently no new exams available for your rank, or you have already completed all available exams. Well done!
        </div>
    <?php else : ?>
        <p>Here are the exams available for your rank. Please read the instructions carefully before you begin.</p>
        <?php foreach ($exams as $exam) : ?>
            <div class="exam-item">
                <div>
                    <h3><?php echo htmlspecialchars($exam['title']); ?></h3>
                    <p>Status: <span style="color: green;">Available</span></p>
                </div>
                <a href="exam-taking.php?exam_id=<?php echo $exam['id']; ?>" class="btn">Start Exam</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
