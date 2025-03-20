<?php
require 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS archived_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT,
    title VARCHAR(255),
    description TEXT,
    due_date DATE,
    priority VARCHAR(50),
    group_id INT,
    user_id INT,
    completed_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Archive table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
