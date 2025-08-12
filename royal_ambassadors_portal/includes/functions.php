<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generates a CSRF token and stores it in the session.
 * @return string The generated token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the submitted CSRF token.
 * @param string $token The token submitted with the form.
 * @return bool True if the token is valid, false otherwise.
 */
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Token is valid, unset it to prevent reuse
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

/**
 * A helper function to echo the CSRF hidden input field in forms.
 */
function csrf_input_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Compresses and resizes an uploaded image.
 * @param string $source_path The temporary path of the uploaded file.
 * @param string $destination_path The path to save the new image.
 * @param int $quality The quality for JPEG compression.
 * @return bool True on success, false on failure.
 */
function process_image_upload($source_path, $destination_path, $quality = 75, $max_width = 1200) {
    // ... function content ...
}

/**
 * Creates a notification for a user.
 * @param mysqli $db The database connection object.
 * @param int $user_id The ID of the user to notify.
 * @param string $message The notification message.
 * @param string $link An optional link for the notification.
 */
function create_notification($db, $user_id, $message, $link = null) {
    $sql = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("iss", $user_id, $message, $link);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Gets the count of unread notifications for a user.
 * @param mysqli $db The database connection object.
 * @param int $user_id The ID of the user.
 * @return int The count of unread notifications.
 */
function get_unread_notification_count($db, $user_id) {
    $count = 0;
    $sql = "SELECT COUNT(id) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $count = $row['unread_count'];
        }
        $stmt->close();
    }
    return $count;
}

/**
 * Renders a placeholder for static builds, to be replaced by dynamic data.
 * @param string $placeholder_text The placeholder text to display.
 */
function render_placeholder($placeholder_text) {
    echo htmlspecialchars($placeholder_text);
}
?>
