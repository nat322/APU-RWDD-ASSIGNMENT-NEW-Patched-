<?php
function archive_overdue_tasks(PDO $conn) {
    try {
        $conn->beginTransaction();

        // 1. Select overdue tasks (pending + not completed + due date passed)
        $stmt = $conn->prepare("
            SELECT * FROM tasks
            WHERE due_date < CURDATE()
              AND completed = 0
              AND status = 'pending'
        ");
        $stmt->execute();
        $overdueTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($overdueTasks)) {
            $conn->commit();
            echo "No overdue tasks to archive.<br>";
            return;
        }

        foreach ($overdueTasks as $task) {

            // 2. Insert into archived_tasks table
            $insertStmt = $conn->prepare("
                INSERT INTO archived_tasks (
                    original_task_id,
                    group_id,
                    user_id,
                    title,
                    description,
                    due_date,
                    completed,
                    completed_date,
                    priority,
                    archive_reason,
                    archived_by,
                    overdue
                ) VALUES (
                    :original_task_id,
                    :group_id,
                    :user_id,
                    :title,
                    :description,
                    :due_date,
                    :completed,
                    :completed_date,
                    :priority,
                    :archive_reason,
                    :archived_by,
                    :overdue
                )
            ");

            $insertStmt->execute([
                ':original_task_id' => $task['id'],
                ':group_id'         => $task['group_id'],
                ':user_id'          => $task['user_id'],
                ':title'            => $task['title'],
                ':description'      => $task['description'],
                ':due_date'         => $task['due_date'],
                ':completed'        => 0, // Not completed when overdue
                ':completed_date'   => $task['completed_date'], // Probably NULL
                ':priority'         => $task['priority'],
                ':archive_reason'   => 'overdue',               // ✅ Reason why
                ':archived_by'      => null,                    // Or system UUID
                ':overdue'          => 1                        // ✅ Flag explicitly
            ]);

            // 3. Delete or update the task from the tasks table
            $deleteStmt = $conn->prepare("DELETE FROM tasks WHERE id = :id");
            $deleteStmt->execute([':id' => $task['id']]);
        }

        $conn->commit();
        echo "Archived " . count($overdueTasks) . " overdue tasks.<br>";

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Failed to archive overdue tasks: " . $e->getMessage();
    }
}
?>
