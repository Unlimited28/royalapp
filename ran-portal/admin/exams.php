<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../database/connection.php';

$admin_id = $_SESSION['user_id'];

// Get the admin's association ID
$sql = "SELECT association_id FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$association_id = $result->fetch_assoc()['association_id'];

<?php
require_once '../auth/functions.php';
?>
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = sanitize($_POST['user_id']);
    $approved = sanitize($_POST['approved']);

    $sql = "UPDATE users SET exam_approved = ? WHERE id = ? AND association_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $approved, $user_id, $association_id);
    $stmt->execute();
}

// Get the users in the admin's association
$sql = "SELECT users.*, ranks.name as rank_name FROM users
        JOIN ranks ON users.rank_id = ranks.id
        WHERE users.association_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $association_id);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Exams</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Approve Exams</h1>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="users.php">Manage Boys</a></li>
            <li><a href="exams.php">Approve Exams</a></li>
            <li><a href="uploads.php">Upload Files</a></li>
            <li><a href="payments.php">Payment Status</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Approve Users for Exams</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Rank</th>
                    <th>Exam Approved</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $user['user_id']; ?></td>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['rank_name']; ?></td>
                    <td><?php echo $user['exam_approved'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <form action="exams.php" method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="approved">
                                <option value="1" <?php if ($user['exam_approved']) echo 'selected'; ?>>Approve</option>
                                <option value="0" <?php if (!$user['exam_approved']) echo 'selected'; ?>>Disapprove</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>
</html>
