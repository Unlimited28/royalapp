<?php
session_start();
$page_title = "Exam Results";
require_once "includes/functions.php";

// Check login and role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'ambassador') {
    header("location: login.php");
    exit;
}

require_once "includes/db_connect.php";

$user_id = $_SESSION['id'];

// Fetch user's published results
$results = [];
$sql = "SELECT er.score, er.status, e.title as exam_title, er.submitted_at
        FROM exam_results er
        JOIN exams e ON er.exam_id = e.id
        WHERE er.user_id = ? AND er.is_published = 1
        ORDER BY er.submitted_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
$mysqli->close();

include 'includes/header.php';
?>
<style>
    .results-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .result-item { border: 1px solid #eee; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
    .result-item h3 { margin: 0 0 10px 0; color: #001f3f; }
    .result-item p { margin: 5px 0; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
</style>

<div class="results-container">
    <h1>Your Exam Results</h1>
    <p>Here are your past exam results. Results are released by the Super Admin.</p>

    <?php if (!empty($results)) : ?>
        <?php foreach ($results as $res) : ?>
            <div class="result-item">
                <h3><?php echo htmlspecialchars($res['exam_title']); ?></h3>
                <p><strong>Score:</strong> <?php echo htmlspecialchars(number_format($res['score'], 2)); ?>%</p>
                <p><strong>Status:</strong> <span class="<?php echo $res['status']; ?>"><?php echo strtoupper($res['status']); ?></span></p>
                <p><small>Date Taken: <?php echo date('F j, Y', strtotime($res['submitted_at'])); ?></small></p>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>You have no published exam results yet.</p>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
