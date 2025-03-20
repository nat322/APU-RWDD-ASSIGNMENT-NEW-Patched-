<?php
require 'db.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;

    if ($task_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $archive_reason = 'completed'; // or 'manual', etc.
        $archived_by = $_SESSION['user_id'];

        $insert = $conn->prepare("
            INSERT INTO archived_tasks (
                original_task_id,
                group_id,
                user_id,
                title,
                description,
                due_date,
                completed,
                completed_date,  
                priority,
                created_at,
                archive_reason,
                archived_by
            )
            SELECT
                id,
                group_id,
                user_id,
                title,
                description,
                due_date,
                completed,
                NOW(),            
                priority,
                created_at,
                ?, -- archive_reason
                ?  -- archived_by
            FROM tasks
            WHERE id = ? AND user_id = ?
        ");

        $insert->execute([$archive_reason, $archived_by, $task_id, $_SESSION['user_id']]);

        if ($insert->rowCount() === 0) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => 'Task not found or not owned by user']);
            exit;
        }

        $delete = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $delete->execute([$task_id, $_SESSION['user_id']]);

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
