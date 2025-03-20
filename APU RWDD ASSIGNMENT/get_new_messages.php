<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$team_id = $_GET['team_id'] ?? null;
$last_id = intval($_GET['last_id'] ?? 0);

if (!$team_id) {
    echo json_encode(["error" => "Team ID is required."]);
    exit;
}

// Verify user is a member of the team
$stmt = $conn->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = :team_id AND user_id = :user_id");
$stmt->bindParam(':team_id', $team_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();

if ($stmt->fetchColumn() == 0) {
    echo json_encode(["error" => "User is not a member of this team."]);
    exit;
}

// Fetch new messages since last_id
$query = "SELECT m.id, m.user_id, m.message, m.timestamp, u.name AS username 
          FROM messages m
          JOIN users u ON m.user_id = u.user_id
          WHERE m.team_id = :team_id AND m.id > :last_id
          ORDER BY m.timestamp ASC, m.id ASC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':team_id', $team_id);
$stmt->bindParam(':last_id', $last_id, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>