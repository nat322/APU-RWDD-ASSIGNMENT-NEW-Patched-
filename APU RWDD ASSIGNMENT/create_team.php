<?php 
session_start();
require 'db.php';

header('Content-Type: application/json'); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if (!isset($_POST['team_name']) || empty(trim($_POST['team_name']))) {
    echo json_encode(["error" => "Team name is required"]);
    exit;
}

$team_name = htmlspecialchars($_POST['team_name']);
$invite_code = strtoupper(substr(md5(uniqid()), 0, 6));
$user_id = $_SESSION['user_id']; // Store session user_id to prevent conflicts

try {
    $conn->beginTransaction(); // Start transaction to prevent errors

    // Insert new team
    $query = "INSERT INTO teams (team_name, team_code) VALUES (:team_name, :team_code)";
    $stmt = $conn->prepare($query);
    $stmt->execute(['team_name' => $team_name, 'team_code' => $invite_code]);

    $team_id = $conn->lastInsertId(); // Get last inserted team ID

    // Add only the creator to team_members
    $query = "INSERT INTO team_members (team_id, user_id) VALUES (:team_id, :user_id)";
    $stmt = $conn->prepare($query);
    $stmt->execute(['team_id' => $team_id, 'user_id' => $user_id]);

    $conn->commit(); // Commit transaction if everything is successful

    echo json_encode(["success" => true, "invite_code" => $invite_code, "team_id" => $team_id]);
} catch (PDOException $e) {
    $conn->rollBack(); // Rollback transaction if something fails
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
