<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT teams.id AS team_id, teams.team_name FROM teams 
          JOIN team_members ON teams.id = team_members.team_id 
          WHERE team_members.user_id = :user_id";

$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($teams);
?>
