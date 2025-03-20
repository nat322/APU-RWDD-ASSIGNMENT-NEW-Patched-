<?php
// get_archive_count.php
require 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $query = "SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND completed = 1 AND status = 'archived'";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['count' => $row['count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>