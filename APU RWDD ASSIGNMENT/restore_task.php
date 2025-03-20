<?php
// restore_task.php
require 'db.php';

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
        // Restore the task from archive
        $stmt = $pdo->prepare("UPDATE tasks SET completed = 0, status = 'active', completed_date = NULL WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$task_id, $_SESSION['user_id']])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to execute query']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
