<?php
require 'db.php';

session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['time_spent'])) {
    $task_id = $_POST['task_id'];
    $time_spent = (int)$_POST['time_spent'];

    // Verify the task belongs to the current user
    $verify_query = "SELECT id FROM tasks WHERE id = :task_id AND user_id = :user_id";
    $stmt = $conn->prepare($verify_query);
    $stmt->execute(['task_id' => $task_id, 'user_id' => $user_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid task']);
        exit;
    }

    // Insert timer history
    $insert_query = "INSERT INTO timer_history (task_id, time_spent, date_created) VALUES (:task_id, :time_spent, NOW())";
    $stmt = $conn->prepare($insert_query);
    
    if ($stmt->execute(['task_id' => $task_id, 'time_spent' => $time_spent])) {
        $history_id = $conn->lastInsertId();

        // Retrieve inserted record
        $get_entry_query = "SELECT th.id, th.task_id, t.title as task_title, th.time_spent, th.date_created 
                            FROM timer_history th 
                            JOIN tasks t ON th.task_id = t.id 
                            WHERE th.id = :history_id";
        $stmt = $conn->prepare($get_entry_query);
        $stmt->execute(['history_id' => $history_id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        $entry['formatted_time'] = formatTimeSpent($entry['time_spent']);
        $entry['formatted_date'] = date('M d, Y - H:i', strtotime($entry['date_created']));

        echo json_encode(['success' => true, 'entry' => $entry]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving timer data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

// Format time spent helper function
function formatTimeSpent($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}
?>
