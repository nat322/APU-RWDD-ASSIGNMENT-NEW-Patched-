<?php
require 'db.php'; // Ensure this file correctly sets $conn
session_start();

// Ensure JSON response
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($task_id === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid Task ID']);
        exit;
    }

    try {
        // Check if task exists and belongs to the user
        $checkStmt = $conn->prepare("SELECT id FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $checkStmt->execute([':task_id' => $task_id, ':user_id' => $user_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'Task not found or permission denied.']);
            exit;
        }

        // Delete the task
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :task_id");
        $stmt->execute([':task_id' => $task_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Task deletion failed.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
