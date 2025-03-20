<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT DISTINCT u.user_id, u.name,
        CASE 
            WHEN a.last_active >= (NOW() - INTERVAL 5 MINUTE) THEN 'online'
            ELSE 'offline'
        END AS status
    FROM friends f
    JOIN users u ON u.user_id = (
        CASE 
            WHEN f.user_id = :user_id THEN f.friend_id
            ELSE f.user_id
        END
    )
    LEFT JOIN active_users a ON u.user_id = a.user_id
    WHERE (f.user_id = :user_id OR f.friend_id = :user_id)
";

$stmt = $conn->prepare($query);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
$stmt->execute();
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($friends);
?>
