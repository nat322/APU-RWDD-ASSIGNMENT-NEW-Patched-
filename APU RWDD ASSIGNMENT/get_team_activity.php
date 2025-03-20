<?php
require_once 'db.php';
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    // Get teams user is a member of
    $stmt = $pdo->prepare("
        SELECT t.id AS team_id, t.team_name
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($teams as $team) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS messages_count
            FROM messages
            WHERE team_id = :team_id
        ");
        $stmt->execute(['team_id' => $team['team_id']]);
        $messages_count = $stmt->fetch(PDO::FETCH_ASSOC)['messages_count'];

        $result[] = [
            'team_name' => $team['team_name'],
            'messages_count' => $messages_count
        ];
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
