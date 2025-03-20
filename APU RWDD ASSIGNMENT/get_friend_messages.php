<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'] ?? '';

if (empty($friend_id)) {
    echo json_encode(['error' => 'Friend ID missing']);
    exit;
}

// Validate friendship
$sqlCheck = "SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute([$user_id, $friend_id, $friend_id, $user_id]);
if ($stmtCheck->rowCount() == 0) {
    echo json_encode(['error' => 'Not friends']);
    exit;
}

$sql = "SELECT id, sender_id, receiver_id, message, timestamp 
        FROM friend_messages
        WHERE (sender_id = :user_id AND receiver_id = :friend_id)
           OR (sender_id = :friend_id AND receiver_id = :user_id)
        ORDER BY timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':friend_id', $friend_id);
$stmt->execute();

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
