<?php
session_start();
require 'db.php';

if (!isset($_GET['team_id'])) {
    echo json_encode(["error" => "Team ID is required."]);
    exit;
}

$teamId = $_GET['team_id'];

try {
    // Fetch team details
    $stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$teamId]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        echo json_encode(["error" => "Team not found."]);
        exit;
    }

    // Fetch team members
    $stmt = $conn->prepare("SELECT users.user_id, users.name FROM team_members 
                            JOIN users ON team_members.user_id = users.user_id
                            WHERE team_members.team_id = ?");
    $stmt->execute([$teamId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send response
    echo json_encode([
        "team_id" => $team['id'],
        "team_name" => $team['team_name'],
        "team_code" => $team['team_code'],
        "created_at" => $team['created_at'],
        "members" => $members
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
