<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("User not logged in");
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE active_users SET last_active = NOW() WHERE user_id = :user_id");
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
?>
