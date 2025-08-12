<?php
session_start();
if (isset($_FILES['avatar'])) {
  $target = "uploads/avatars/";
  $fileName = basename($_FILES['avatar']['name']);
  $targetFile = $target . $fileName;

  move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile);

  // Save to DB
  $conn = new mysqli("localhost", "root", "", "royal_ambassador");
  $id = $_SESSION['user_id'];
  $sql = "UPDATE users SET avatar='$fileName' WHERE id='$id'";
  $conn->query($sql);

  // Update session
  $_SESSION['avatar'] = $fileName;

  header("Location: dashboard.php");
}
?>
