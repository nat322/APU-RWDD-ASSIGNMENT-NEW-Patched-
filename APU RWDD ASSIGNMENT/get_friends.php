<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch friends (Both Directions)
$sql = "SELECT u.user_id, u.name 
        FROM friends f
        JOIN users u ON (f.friend_id = u.user_id)
        WHERE f.user_id = ?
        UNION
        SELECT u.user_id, u.name 
        FROM friends f
        JOIN users u ON (f.user_id = u.user_id)
        WHERE f.friend_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($friends);
?>
