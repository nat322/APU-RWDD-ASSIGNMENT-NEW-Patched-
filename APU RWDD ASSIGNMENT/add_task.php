<?php
require 'db.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_POST['group_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $due_date = $_POST['due_date'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    $user_id = $_POST['user_id'] ?? $_SESSION['user_id']; // Fallback to session user_id

    if (!$group_id || !$title || !$user_id) {
        echo json_encode(['error' => 'Missing required fields.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO tasks (group_id, user_id, title, description, due_date, priority) 
                                VALUES (:group_id, :user_id, :title, :description, :due_date, :priority)");
        $stmt->execute([
            ':group_id' => $group_id,
            ':user_id' => $user_id,
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $due_date,
            ':priority' => $priority
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
