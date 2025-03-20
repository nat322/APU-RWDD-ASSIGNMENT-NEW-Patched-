<?php
require 'db.php';

session_start();
$user_id = $_SESSION['user_id'];

$query = $pdo->prepare("
    SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN event_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_events
    FROM events
    WHERE user_id = :user_id
");
$query->execute(['user_id' => $user_id]);
$data = $query->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
?>
