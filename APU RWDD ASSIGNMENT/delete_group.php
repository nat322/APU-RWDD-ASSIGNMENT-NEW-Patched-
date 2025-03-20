<?php
require 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_POST['group_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$group_id) {
        echo json_encode(['success' => false, 'error' => 'Group ID is required.']);
        exit;
    }

    try {
        // Check if the group exists and belongs to the user
        $checkStmt = $conn->prepare("SELECT id FROM tdGroups WHERE id = :group_id AND user_id = :user_id");
        $checkStmt->execute([':group_id' => $group_id, ':user_id' => $user_id]);

        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'Group not found or permission denied.']);
            exit;
        }

        // Delete the group (tasks will be deleted automatically if ON DELETE CASCADE is set)
        $deleteGroup = $conn->prepare("DELETE FROM tdGroups WHERE id = :group_id");
        $deleteGroup->execute([':group_id' => $group_id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
