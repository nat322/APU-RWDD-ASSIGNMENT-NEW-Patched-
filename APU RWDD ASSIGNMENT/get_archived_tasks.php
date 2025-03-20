<?php
require 'db.php'; // Your PDO connection file
header('Content-Type: application/json');
session_start();

// ✅ User Authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // ✅ Prepare query to get all archived tasks for this user
    $stmt = $conn->prepare("
        SELECT 
            id,
            original_task_id,
            group_id,
            title,
            description,
            due_date,
            completed_date,
            priority,
            archive_reason,  -- ✅ Added archive reason
            overdue,         -- ✅ Added overdue (if your table includes it)
            archived_at
        FROM archived_tasks
        WHERE user_id = ?
        ORDER BY archived_at DESC
    ");

    $stmt->execute([$user_id]);

    $archived_tasks = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $archived_tasks[] = [
            'id' => $row['id'],
            'original_task_id' => $row['original_task_id'],
            'group_id' => $row['group_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'due_date' => $row['due_date'],
            'completed_date' => $row['completed_date'],
            'priority' => $row['priority'],
            'archive_reason' => $row['archive_reason'],  // ✅ Why it was archived
            'overdue' => $row['overdue'],                // ✅ 1 (true) or 0 (false)
            'archived_at' => $row['archived_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'archived_tasks' => $archived_tasks
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
