<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? '';
$message = trim($_POST['message'] ?? '');

if (empty($receiver_id) || empty($message)) {
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// Check if they are friends first
$sqlCheck = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
if ($stmtCheck->rowCount() == 0) {
    echo json_encode(['error' => 'Not friends']);
    exit;
}

// Insert the message
$sql = "INSERT INTO friend_messages (sender_id, receiver_id, message, timestamp)
        VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if ($stmt->execute([$sender_id, $receiver_id, $message])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
?>
