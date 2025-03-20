<?php
require 'db.php';
session_start();

header('Content-Type: application/json');

$name = $_POST['name'] ?? null;
$user_id = $_POST['user_id'] ?? $_SESSION['user_id']; // Fallback to session user ID

if (!$name || !$user_id) {
    echo json_encode(['error' => 'Group name and user ID are required.']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO tdGroups (name, user_id) VALUES (:name, :user_id)");
    $stmt->execute([
        ':name' => $name,
        ':user_id' => $user_id
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
