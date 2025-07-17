<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ambassador') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT exams.* FROM exams
        JOIN users ON exams.rank_id = users.rank_id
        WHERE users.id = ? AND exams.status = 'active'";
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
    <title>Exams</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Exams</h1>
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
        <h2>Available Exams</h2>
        <table>
            <thead>
                <tr>
                    <th>Exam Title</th>
                    <th>Duration</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['duration']; ?> minutes</td>
                    <td><a href="take_exam.php?id=<?php echo $row['id']; ?>">Take Exam</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
