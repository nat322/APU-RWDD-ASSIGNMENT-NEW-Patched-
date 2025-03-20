<?php
require 'db.php';
session_start();

$user_id = $_SESSION['user_id']; // Get logged-in user

$sql = "SELECT fr.id, u.name 
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.user_id
        WHERE fr.receiver_id = ? AND fr.status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($requests);
?>
