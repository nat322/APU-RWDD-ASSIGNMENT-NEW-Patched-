<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Remove user from active_users table
    $stmt = $conn->prepare("DELETE FROM active_users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Destroy session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>
