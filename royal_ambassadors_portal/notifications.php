<?php
session_start();
$page_title = "Notifications";
require_once "includes/functions.php";

// Check login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "includes/db_connect.php";

$user_id = $_SESSION['id'];

// Mark a notification as read
if (isset($_GET['read_id'])) {
    $read_id = $_GET['read_id'];
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $read_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all notifications for the user
$notifications = [];
$sql = "SELECT id, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}
$mysqli->close();

include 'includes/header.php';
?>
<style>
    .notifications-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
    .notification-item { border-bottom: 1px solid #eee; padding: 15px; }
    .notification-item.unread { background-color: #f8f9fa; }
    .notification-item a { text-decoration: none; color: #001f3f; }
</style>

<div class="notifications-container">
    <h1>Your Notifications</h1>

    <?php if (empty($notifications)) : ?>
        <p>You have no notifications.</p>
    <?php else : ?>
        <?php foreach ($notifications as $notif) : ?>
            <div class="notification-item <?php if(!$notif['is_read']) echo 'unread'; ?>">
                <a href="<?php echo htmlspecialchars($notif['link'] ?? '#'); ?>?read_id=<?php echo $notif['id']; ?>">
                    <p><?php echo htmlspecialchars($notif['message']); ?></p>
                    <small><?php echo date('F j, Y, g:i a', strtotime($notif['created_at'])); ?></small>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
