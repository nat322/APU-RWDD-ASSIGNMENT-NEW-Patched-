<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['team_id'])) {
    echo json_encode(["error" => "Team not selected."]);
    exit;
}

$team_id = $_SESSION['team_id'];

// Fetch messages and get username from users table
$query = "SELECT messages.*, users.name AS username 
          FROM messages 
          JOIN users ON messages.user_id = users.user_id
          WHERE team_id = :team_id ORDER BY timestamp ASC";

$stmt = $conn->prepare($query);
$stmt->execute(['team_id' => $team_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
