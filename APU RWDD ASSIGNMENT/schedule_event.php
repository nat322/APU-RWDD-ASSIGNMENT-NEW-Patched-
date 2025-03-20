<?php
session_start();
require 'db.php';

// Set response headers
header('Content-Type: application/json');

$response = ['success' => false];

// Handle event scheduling from AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    try {
        if ($_POST['action'] == 'schedule_event') {
            $team_id = isset($_POST['team_id']) && $_POST['team_id'] !== '' ? (int) $_POST['team_id'] : null; // Ensure numeric or NULL
            $username = trim($_POST['username'] ?? '');
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;
            $title = $_POST['title'] ?? 'Untitled Event';
            $description = $_POST['description'] ?? '';

            if (!$username || !$date) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }

            // 🔹 Look up `user_id` from `users` table (Corrected column name)
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE name = :username"); // `name` column used
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }

            $user_id = $user['user_id']; // Corrected to `user_id`

            // 🔹 Insert event into database
            $stmt = $conn->prepare("INSERT INTO events (user_id, team_id, title, description, event_date, event_time) 
                                    VALUES (:user_id, :team_id, :title, :description, :event_date, :event_time)");
            
            $stmt->bindParam(':user_id', $user_id);
            
            // ✅ Corrected `bindParam` for `team_id`
            if ($team_id !== null) {
                $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':team_id', null, PDO::PARAM_NULL); // Use bindValue for NULL
            }

            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':event_date', $date);
            $stmt->bindParam(':event_time', $time);

            $stmt->execute();

            $response = ['success' => true, 'message' => "Event scheduled for $username on $date"];
        }
    } catch (PDOException $e) {
        $response = ['success' => false, 'error' => $e->getMessage()];
    }
}

// Return JSON response
echo json_encode($response);
exit;
?>
