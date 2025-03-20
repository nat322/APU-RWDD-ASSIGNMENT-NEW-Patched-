<?php
require 'db.php';

session_start();
$user_id = $_SESSION['user_id'];

$query = $pdo->prepare("
    SELECT
        COUNT(DISTINCT friend_id) as total_friends,
        (SELECT COUNT(*) FROM friend_requests WHERE receiver_id = :user_id AND status = 'pending') as pending_requests,
        (SELECT COUNT(*) FROM friend_messages WHERE sender_id = :user_id) as messages_sent
");
$query->execute(['user_id' => $user_id]);
$data = $query->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
?>
