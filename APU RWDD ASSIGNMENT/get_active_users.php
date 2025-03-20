<?php
require 'db.php';

$team_id = $_GET['team_id'] ?? null;
if (!$team_id) {
    echo json_encode(["error" => "No team ID provided."]);
    exit;
}

// Fetch users active within the last 2 minutes
$stmt = $conn->prepare("
    SELECT u.user_id, u.name 
    FROM users u
    JOIN active_users a ON u.user_id = a.user_id
    JOIN team_members tm ON u.user_id = tm.user_id
    WHERE tm.team_id = :team_id AND a.last_active > NOW() - INTERVAL 2 MINUTE
");
$stmt->bindParam(':team_id', $team_id);
$stmt->execute();
$activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($activeUsers);
?>
