<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "User not logged in."]));
}

$user_id = $_SESSION['user_id'];
$friend_email = $_POST['friend_email'] ?? '';

if (!$friend_email) {
    die(json_encode(["error" => "Friend email is required."]));
}

try {
    // Get friend's user ID
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $friend_email, PDO::PARAM_STR);
    $stmt->execute();
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$friend) {
        die(json_encode(["error" => "User not found."]));
    }

    $friend_id = $friend['user_id'];

    // Prevent duplicate friend requests
    $stmt = $conn->prepare("SELECT * FROM friends WHERE user_id = :user_id AND friend_id = :friend_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':friend_id', $friend_id, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        die(json_encode(["error" => "Already friends."]));
    }

    // Insert friend request
    $stmt = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (:user_id, :friend_id)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':friend_id', $friend_id, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(["success" => "Friend request sent."]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
