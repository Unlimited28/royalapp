<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ambassador') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$user_id = $_SESSION['user_id'];

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    $sql = "INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $subject, $message);

    if ($stmt->execute()) {
        $success = "Support ticket submitted successfully.";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Support</h1>
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
        <h2>Submit a Support Ticket</h2>
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form action="support.php" method="post">
            <label for="subject">Subject:</label>
            <input type="text" name="subject" required>
            <label for="message">Message:</label>
            <textarea name="message" required></textarea>
            <button type="submit">Submit</button>
        </form>
    </main>
</body>
</html>
