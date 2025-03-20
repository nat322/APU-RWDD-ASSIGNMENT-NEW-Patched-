<?php
session_start();
header("Content-Type: application/json");

// Check if team_id is provided in the request
if (!isset($_POST['team_id'])) {
    echo json_encode(["error" => "No team ID provided."]);
    exit;
}

// Set the selected team ID in the session
$_SESSION['team_id'] = $_POST['team_id'];
echo json_encode(["success" => true]);
?>
