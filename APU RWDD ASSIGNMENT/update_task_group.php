<?php
require 'db.php';

header('Content-Type: application/json');

$task_id = $_POST['task_id'];
$new_group_id = $_POST['new_group_id'];

try {
    $stmt = $conn->prepare("UPDATE tasks SET group_id = :new_group_id WHERE id = :task_id");
    $stmt->bindParam(':new_group_id', $new_group_id);
    $stmt->bindParam(':task_id', $task_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>