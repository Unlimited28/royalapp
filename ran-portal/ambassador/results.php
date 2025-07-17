<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ambassador') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT results.*, exams.title FROM results
        JOIN exams ON results.exam_id = exams.id
        WHERE results.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Results</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="exams.php">Exams</a></li>
            <li><a href="results.php">Results</a></li>
            <li><a href="../about.php">About</a></li>
            <li><a href="../blog.php">Blog</a></li>
            <li><a href="../gallery.php">Gallery</a></li>
            <li><a href="support.php">Support</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Your Exam Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Exam Title</th>
                    <th>Score</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['score']; ?>%</td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
