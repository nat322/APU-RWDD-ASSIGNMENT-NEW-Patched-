<?php
require 'db.php';

header('Content-Type: application/json');

$user_id = $_GET['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM tdGroups WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($groups);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
