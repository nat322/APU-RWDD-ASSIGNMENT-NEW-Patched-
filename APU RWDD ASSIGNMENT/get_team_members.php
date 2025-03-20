<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'], $_POST['team_id'])) {
    echo json_encode(["error" => "Unauthorized request."]);
    exit;
}

$team_id = $_POST['team_id'];

$query = "SELECT users.user_id, users.name FROM users 
          JOIN team_members ON users.user_id = team_members.user_id 
          WHERE team_members.team_id = :team_id";

$stmt = $conn->prepare($query);
$stmt->execute(['team_id' => $team_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($members);
?>
