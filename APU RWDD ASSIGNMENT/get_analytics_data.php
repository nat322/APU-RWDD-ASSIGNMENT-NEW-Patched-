<?php
// get_analytics_data.php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode(['error' => 'No user ID']);
        exit;
    }

    $response = [];

    // 1️⃣ Total tasks completed -> COUNT from archived_tasks WHERE archive_reason = 'completed'
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM archived_tasks 
        WHERE user_id = ? AND archive_reason = 'completed'
    ");
    $stmt->execute([$user_id]);
    $response['total_tasks_completed'] = (int)$stmt->fetchColumn();

    // 2️⃣ Total time spent on all tasks (calculated from archived_tasks table)
    $stmt = $conn->prepare("
    SELECT COALESCE(SUM(TIMESTAMPDIFF(SECOND, created_at, completed_date)), 0) AS total_time
    FROM archived_tasks
    WHERE user_id = ? AND archive_reason = 'completed'
    ");
    $stmt->execute([$user_id]);
    $response['total_time_spent'] = (int)$stmt->fetchColumn();

    // 3️⃣ Most active day (tasks completed -> archived_tasks.completed_date)
    $stmt = $conn->prepare("
        SELECT DATE(completed_date) AS day, COUNT(*) AS completed_tasks
        FROM archived_tasks
        WHERE user_id = ? AND archive_reason = 'completed'
        GROUP BY day
        ORDER BY completed_tasks DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $most_active_day = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['most_active_day'] = $most_active_day['day'] ?? null;

    // 4️⃣ Tasks completed per day (trend) -> archived_tasks.completed_date
    $stmt = $conn->prepare("
        SELECT DATE(completed_date) AS date, COUNT(*) AS completed_tasks
        FROM archived_tasks
        WHERE user_id = ? AND archive_reason = 'completed'
        GROUP BY date
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id]);
    $response['tasks_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5️⃣ Time spent per task (top 10 by time) -> still from timer_history/tasks (ok as is)
    $stmt = $conn->prepare("
        SELECT t.title AS task_title, COALESCE(SUM(th.time_spent), 0) AS total_time
        FROM tasks t
        LEFT JOIN timer_history th ON th.task_id = t.id
        WHERE t.user_id = ?
        GROUP BY t.id
        ORDER BY total_time DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $response['time_per_task'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6️⃣ Time spent per day (trend) -> still fine as is
    $stmt = $conn->prepare("
        SELECT DATE(th.date_created) AS date, SUM(th.time_spent) AS total_time
        FROM timer_history th
        INNER JOIN tasks t ON t.id = th.task_id
        WHERE t.user_id = ?
        GROUP BY date
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id]);
    $response['time_spent_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7️⃣ Task completion breakdown by priority 
    $stmt = $conn->prepare("
        SELECT priority, COUNT(*) AS completed_tasks
        FROM archived_tasks
        WHERE user_id = ?
        GROUP BY priority
    ");
    $stmt->execute([$user_id]);
    $response['completed_tasks_by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8️⃣ Total overdue tasks (optional clarification)
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM archived_tasks
        WHERE user_id = ? AND overdue = 1
    ");
    $stmt->execute([$user_id]);
    $response['total_overdue_tasks'] = (int)$stmt->fetchColumn();

    // 9️⃣ Archived tasks breakdown (by archive_reason) -> no change
    $stmt = $conn->prepare("
        SELECT archive_reason, COUNT(*) AS count
        FROM archived_tasks
        WHERE user_id = ?
        GROUP BY archive_reason
    ");
    $stmt->execute([$user_id]);
    $response['archived_tasks_by_reason'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔟 Time spent on tasks in teams -> still fine as is
    $stmt = $conn->prepare("
        SELECT tm.team_id, te.team_name, COALESCE(SUM(th.time_spent), 0) AS total_time
        FROM team_members tm
        INNER JOIN teams te ON te.id = tm.team_id
        LEFT JOIN tasks t ON t.user_id = tm.user_id
        LEFT JOIN timer_history th ON th.task_id = t.id
        WHERE tm.user_id = ?
        GROUP BY tm.team_id
    ");
    $stmt->execute([$user_id]);
    $response['time_spent_per_team'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Final Response
    echo json_encode($response);
}
