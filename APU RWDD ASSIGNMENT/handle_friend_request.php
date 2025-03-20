<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$request_id = $data['request_id'];
$action = $data['action'];

try {
    // ✅ Step 1: Get sender & receiver IDs
    $stmt = $conn->prepare("SELECT sender_id, receiver_id FROM friend_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['error' => 'Friend request not found']);
        exit;
    }

    $sender_id = $request['sender_id'];
    $receiver_id = $request['receiver_id'];

    if ($action === "accept") {
        // ✅ Step 2: Insert into `friends` table (Two-Way Relationship)
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);

        // ✅ Step 3: Update friend request status to "accepted"
        $stmt = $conn->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$request_id]);

    } elseif ($action === "decline") {
        // ✅ Step 4: Update status to "rejected"
        $stmt = $conn->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$request_id]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
