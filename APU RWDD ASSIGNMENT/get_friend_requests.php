<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT fr.id, u.user_id, u.name, u.email FROM friend_requests fr 
                            JOIN users u ON fr.sender_id = u.user_id
                            WHERE fr.receiver_id = ? AND fr.status = 'pending'");
    $stmt->execute([$user_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($requests);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
