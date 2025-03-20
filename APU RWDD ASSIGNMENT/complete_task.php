<?php
require 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_POST['task_id']) || !isset($_POST['completed'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$task_id = intval($_POST['task_id']);
$completed = intval($_POST['completed']);
$status = $completed ? 'completed' : 'pending';

try {
    // Update the task completion status
    $stmt = $conn->prepare("UPDATE tasks SET 
                          completed = :completed, 
                          status = :status,
                          completed_date = :completed_date 
                          WHERE id = :task_id AND user_id = :user_id");
    
    // If completing the task, set completed_date to current timestamp, otherwise set to NULL
    $completed_date = $completed ? date('Y-m-d H:i:s') : null;
    
    $stmt->bindParam(':completed', $completed, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':completed_date', $completed_date);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Task not found or not authorized']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>