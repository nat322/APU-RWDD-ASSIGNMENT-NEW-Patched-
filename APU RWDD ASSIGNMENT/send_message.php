<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "User not logged in."]);
    exit;
}

// Validate input
if (!isset($_POST['team_id']) || !isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(["success" => false, "error" => "Missing required parameters."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$team_id = $_POST['team_id'];
$message = trim($_POST['message']);

// Validate that user is a member of the team
$stmt = $conn->prepare("SELECT COUNT(*) FROM team_members WHERE team_id = :team_id AND user_id = :user_id");
$stmt->bindParam(':team_id', $team_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if ($stmt->fetchColumn() == 0) {
    echo json_encode(["success" => false, "error" => "User is not a member of this team."]);
    exit;
}

try {
    // Insert message
    $stmt = $conn->prepare("INSERT INTO messages (team_id, user_id, message) VALUES (:team_id, :user_id, :message)");
    $stmt->bindParam(':team_id', $team_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':message', $message);
    $stmt->execute();
    
    // Update active status
    $stmt = $conn->prepare("INSERT INTO active_users (user_id, last_active) VALUES (:user_id, NOW()) 
                            ON DUPLICATE KEY UPDATE last_active = NOW()");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    echo json_encode(["success" => true, "message_id" => $conn->lastInsertId()]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>