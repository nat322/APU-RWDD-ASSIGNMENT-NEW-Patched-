<?php
require 'db.php';

header('Content-Type: application/json');

$user_id = $_GET['user_id']; // Fetch tasks by user

try {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY completed ASC, priority DESC, due_date ASC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tasks);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>