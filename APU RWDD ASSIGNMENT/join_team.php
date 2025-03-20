<?php
session_start();
require 'db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check session data
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in", "session" => $_SESSION]);
    exit;
}

// Debug: Check if POST data is received
if (!isset($_POST['team_code'])) {
    echo json_encode(["error" => "Missing team code", "post_data" => $_POST]);
    exit;
}

$team_code = strtoupper(trim($_POST['team_code']));

// Fetch team ID using team_code
$query = "SELECT id FROM teams WHERE team_code = :team_code";
$stmt = $conn->prepare($query);
$stmt->execute(['team_code' => $team_code]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    echo json_encode(["error" => "Invalid team code"]);
    exit;
}

// Check if user already in team (to prevent duplicates)
$query = "SELECT id FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    'team_id' => $team['id'],
    'user_id' => $_SESSION['user_id']
]);

if ($stmt->fetch()) {
    echo json_encode(["error" => "Already a member of this team"]);
    exit;
}

// Add user to team
$query = "INSERT INTO team_members (team_id, user_id) VALUES (:team_id, :user_id)";
$stmt = $conn->prepare($query);
$stmt->execute([
    'team_id' => $team['id'],
    'user_id' => $_SESSION['user_id']
]);

echo json_encode(["success" => true]);
?>
