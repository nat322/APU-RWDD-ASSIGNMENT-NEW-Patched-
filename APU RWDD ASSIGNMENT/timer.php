<?php
require 'db.php';

session_start();
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Get all tasks for the current user
$tasks_query = "SELECT t.id, t.title, t.description, t.priority, t.due_date, t.completed, g.name as group_name 
                FROM tasks t 
                JOIN tdGroups g ON t.group_id = g.id 
                WHERE t.user_id = ?";
try {
    $stmt = $conn->prepare($tasks_query);
    $stmt->execute([$user_id]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

// Get timer history from the database
$history_query = "SELECT th.id, th.task_id, t.title as task_title, th.time_spent, th.date_created 
                  FROM timer_history th 
                  JOIN tasks t ON th.task_id = t.id 
                  WHERE t.user_id = ? 
                  ORDER BY th.date_created DESC LIMIT 10";
$stmt = $conn->prepare($history_query);
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Timer</title>
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <style>
        :root {
            --space: 1rem;
            --bg: #09090b;
            --fg: #e3e3e3;
            --surface-1: #101012;
            --surface-2: #27272a;
            --surface-3: #52525b;
            --accent-blue: #0ea5e9;
            --accent-blue-light: #e0f2fe;
            --accent-yellow: #eab308;
            --accent-yellow-light: #fef08a;
            --accent-pink: #e11d48;
            --accent-pink-light: #fecdd3;
            --ease-out: cubic-bezier(0.5, 1, 0.89, 1);
            --ease-in-out: cubic-bezier(0.45, 0, 0.55, 1);
            --font-sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: var(--font-sans);
            background: var(--bg);
            color: var(--fg);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex-grow: 1;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        
        .timer-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--surface-1);
            border-radius: 0.5rem;
            border: 1px solid var(--surface-2);
            position: relative;
            overflow: hidden;
        }
        
        .header h1 {
            font-size: 1.8rem;
            z-index: 1;
        }

        .task-selection {
            background: var(--surface-1);
            border-radius: 0.5rem;
            border: 1px solid var(--surface-2);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .task-selection h2 {
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .task-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
            border: 1px solid var(--surface-2);
            border-radius: 0.375rem;
        }

        .task-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--surface-2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-item:hover {
            background-color: var(--surface-2);
        }

        .task-item.selected {
            background-color: rgba(14, 165, 233, 0.2);
        }

        .task-item-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .task-item-group {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .task-priority {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        
        .priority-low {
            background: rgba(14, 165, 233, 0.2);
            color: var(--accent-blue-light);
        }
        
        .priority-medium {
            background: rgba(234, 179, 8, 0.2);
            color: var(--accent-yellow-light);
        }
        
        .priority-high {
            background: rgba(225, 29, 72, 0.2);
            color: var(--accent-pink-light);
        }

        .timer-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1rem;
        }

        .timer-input-group {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            background: var(--surface-2);
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        .timer-input {
            width: 4rem;
            text-align: center;
            font-size: 1.5rem;
            padding: 0.5rem;
            margin: 0 0.5rem;
            background: var(--surface-1);
            border: 1px solid var(--surface-3);
            border-radius: 0.25rem;
            color: var(--fg);
        }

        .timer-separator {
            font-size: 1.5rem;
            margin: 0 0.25rem;
        }

        .timer-type-selector {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .timer-type-button {
            padding: 0.6rem 1.2rem;
            background: var(--surface-2);
            color: var(--fg);
            border: 1px solid var(--surface-3);
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }

        .timer-type-button:first-child {
            border-radius: 0.25rem 0 0 0.25rem;
        }

        .timer-type-button:last-child {
            border-radius: 0 0.25rem 0.25rem 0;
        }

        .timer-type-button.active {
            background-color: var(--accent-blue);
            color: white;
        }

        .timer-group {
            height: 400px;
            position: relative;
            width: 400px;
            margin: 0 auto;
        }

        .timer {
            border-radius: 50%;
            height: 100px;
            overflow: hidden;
            position: absolute;
            width: 100px;
        }

        .timer:after {
            background: var(--bg);
            border-radius: 50%;
            content: "";
            display: block;
            height: 80px;
            left: 10px;
            position: absolute;
            width: 80px;
            top: 10px;
        }

        .timer .hand {
            float: left;
            height: 100%;
            overflow: hidden;
            position: relative;
            width: 50%;
        }

        .timer .hand span {
            border: 50px solid rgba(0, 255, 255, .4);
            border-bottom-color: transparent;
            border-left-color: transparent;
            border-radius: 50%;
            display: block;
            height: 0;
            position: absolute;
            right: 0;
            top: 0;
            transform: rotate(225deg);
            width: 0;
        }

        .timer .hand:first-child {
            transform: rotate(180deg);
        }

        .timer .hand:first-child span {
            animation-name: spin1;
            animation-timing-function: linear;
            animation-play-state: paused;
            animation-iteration-count: infinite;
        }

        .timer .hand:last-child span {
            animation-name: spin2;
            animation-timing-function: linear;
            animation-play-state: paused;
            animation-iteration-count: infinite;
        }

        .running .timer .hand span {
            animation-play-state: running;
        }
        
        .running .timer .hand:first-child span,
        .running .timer .hand:last-child span {
            animation-play-state: running;
        }

        .timer.hour {
            background: rgba(0, 0, 0, .3);
            height: 400px;
            left: 0;
            width: 400px;
            top: 0;
        }

        .timer.hour .hand span {
            border-top-color: rgba(255, 0, 255, .4);
            border-right-color: rgba(255, 0, 255, .4);
            border-width: 200px;
        }

        .timer.hour:after {
            height: 360px;
            left: 20px;
            width: 360px;
            top: 20px;
        }

        .timer.minute {
            background: rgba(0, 0, 0, .2);
            height: 350px;
            left: 25px;
            width: 350px;
            top: 25px;
        }

        .timer.minute .hand span {
            border-top-color: rgba(0, 255, 255, .4);
            border-right-color: rgba(0, 255, 255, .4);
            border-width: 175px;
        }

        .timer.minute:after {
            height: 310px;
            left: 20px;
            width: 310px;
            top: 20px;
        }

        .timer.second {
            background: rgba(0, 0, 0, .2);
            height: 300px;
            left: 50px;
            width: 300px;
            top: 50px;
        }

        .timer.second .hand span {
            border-top-color: rgba(255, 255, 255, .15);
            border-right-color: rgba(255, 255, 255, .15);
            border-width: 150px;
        }

        .timer.second:after {
            height: 296px;
            left: 2px;
            width: 296px;
            top: 2px;
        }

        .face {
            background: rgba(0, 0, 0, .1);
            border-radius: 50%;
            height: 296px;
            left: 52px;
            padding: 165px 40px 0;
            position: absolute;
            width: 296px;
            text-align: center;
            top: 52px;
        }

        .face h2 {
            font-weight: 300;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .face p {
            border-radius: 20px;
            font-size: 76px;
            font-weight: 400;
            position: absolute;
            top: 17px;
            width: 260px;
            left: 20px;
        }

        .timer-actions {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 1rem;
        }

        .timer-button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
            font-size: 1rem;
        }

        .start-button {
            background-color: var(--accent-blue);
            color: white;
        }

        .start-button:hover {
            background-color: #0c8bca;
        }

        .pause-button {
            background-color: var(--accent-yellow);
            color: var(--surface-1);
        }

        .pause-button:hover {
            background-color: #d4a304;
        }

        .reset-button {
            background-color: var(--accent-pink);
            color: white;
        }

        .reset-button:hover {
            background-color: #c21943;
        }

        .timer-history {
            background: var(--surface-1);
            border-radius: 0.5rem;
            border: 1px solid var(--surface-2);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .timer-history h2 {
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .history-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid var(--surface-2);
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-date {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .history-time {
            font-weight: bold;
        }

        .task-status {
            margin-left: 0.5rem;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
        }

        .status-completed {
            background-color: rgba(14, 165, 233, 0.2);
            color: var(--accent-blue-light);
        }

        .status-pending {
            background-color: rgba(234, 179, 8, 0.2);
            color: var(--accent-yellow-light);
        }

        .no-task-selected {
            text-align: center;
            padding: 2rem;
            background: var(--surface-2);
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .no-task-selected h2 {
            margin-bottom: 1rem;
        }

        .timer-display {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .timer-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .timer-task-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        /* Focus mode button styling */
        .focus-mode-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .hover-button {
            position: relative;
            width: 128px;
            cursor: pointer;
            overflow: hidden;
            border-radius: 9999px;
            border: 1px solid var(--surface-3);
            background-color: var(--surface-1);
            padding: 8px;
            text-align: center;
            font-weight: 600;
        }

        .hover-button .text {
            display: inline-block;
            transform: translateX(10px); /* Adjust this value to move text right */
            transition: all 0.3s;
            color: var(--fg);
            opacity: 0.9;
        }

        .hover-button:hover .text {
            transform: translateX(48px);
            opacity: 0;
        }

        .hover-button .hover-content {
            position: absolute;
            top: 0;
            z-index: 10;
            display: flex;
            height: 100%;
            width: 100%;
            transform: translateX(48px);
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: white;
            opacity: 0;
            transition: all 0.3s;
        }

        .hover-button:hover .hover-content {
            transform: translateX(-4px);
            opacity: 1;
        }

        .hover-button .background {
            position: absolute;
            left: 20%;  /* Change this to move the dot to the left */
            top: 40%;   /* Change this to move the dot down */
            height: 8px;
            width: 8px;
            transform: scale(1);
            border-radius: 8px;
            background-color: var(--accent-blue);
            transition: all 0.3s;
        }

        .hover-button:hover .background {
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            transform: scale(1.8);
        }

        .arrow-right {
            width: 16px;
            height: 16px;
            position: relative;
        }

        .arrow-right::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            border-right: 2px solid white;
            border-top: 2px solid white;
            transform: rotate(45deg);
            top: 4px;
            left: 2px;
        }

        /* Focus mode active state */
        .focus-toggle {
            box-shadow: 0 0 5px rgba(14, 165, 233, 0.2);
        }

        .focus-toggle:hover {
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.4);
        }

        .focus-toggle .text {
            text-shadow: 0 0 2px rgba(255, 255, 255, 0.3);
        }

        /* Focus mode active state with enhanced visibility */
        .focus-toggle.active .text {
            color: var(--accent-blue);
            opacity: 1;
        }

        .focus-toggle.active .hover-content span:first-child {
            content: 'Deactivate';
        }

        /* Focus mode styles */
        .focus-mode .container {
            background-color: rgba(0, 0, 0, 0.95);
        }

        .focus-mode .main-content {
            width: 100%;
            max-width: 100%;
        }

        .focus-mode .sidebar {
            display: none;
        }

        .focus-mode .timer-container {
            max-width: 700px;
        }

        .focus-mode .header, 
        .focus-mode .task-selection, 
        .focus-mode .timer-history {
            opacity: 0.3;
            transition: opacity 0.3s;
        }

        .focus-mode .header:hover, 
        .focus-mode .task-selection:hover, 
        .focus-mode .timer-history:hover {
            opacity: 1;
        }

        .focus-mode .timer-display {
            transform: scale(1.1);
            transition: transform 0.3s;
        }

        /* Pomodoro specific styling */
        .pomodoro-timer .timer.hour .hand span {
            animation-duration: 1500s; /* 25 minutes by default */
        }

        .pomodoro-timer .timer.minute .hand span {
            animation-duration: 1500s; /* 25 minutes by default */
        }

        .pomodoro-timer .timer.second .hand span {
            animation-duration: 60s; /* 60 seconds */
        }

        .pomodoro-setting {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 0.5rem;
            position: relative;
            min-width: 4rem;
        }

        .pomodoro-setting label {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            color: var(--fg);
        }

        .pomodoro-setting .timer-input {
            width: 3.5rem;
            text-align: center;
            font-size: 1.2rem;
            padding: 0.5rem;
            margin: 0;
            background: var(--surface-1);
            border: 1px solid var(--surface-3);
            border-radius: 0.25rem;
            color: var(--fg);
            position: relative;
            z-index: 5; /* Ensure inputs are above other elements */
        }

        .pomodoro-setting span {
            margin-top: 0.25rem;
            color: var(--fg);
            opacity: 0.8;
        }

        .pomodoro-settings {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: center;
            padding: 0.5rem;
        }

        .pomodoro-status {
            display: flex;
            justify-content: space-between;
            width: 80%;
            margin: 1rem auto;
            padding: 0.5rem 1rem;
            background: var(--surface-2);
            border-radius: 0.25rem;
            font-size: 0.9rem;
        }

        .pomodoro-phase {
            font-weight: bold;
        }

        /* Phase colors */
        .phase-work {
            color: var(--accent-blue-light);
        }

        .phase-short-break {
            color: var(--accent-yellow-light);
        }

        .phase-long-break {
            color: var(--accent-pink-light);
        }

        /* Animation keyframes */
        @keyframes spin1 {
            0% {
                transform: rotate(225deg);
            }
            50% {
                transform: rotate(225deg);
            }
            100% {
                transform: rotate(405deg);
            }
        }

        @keyframes spin2 {
            0% {
                transform: rotate(225deg);
            }
            50% {
                transform: rotate(405deg);
            }
            100% {
                transform: rotate(405deg);
            }
        }

        /* Timer mode specific styling */
        .countdown-timer .timer.hour .hand span {
            animation-duration: 3600s;
        }

        .countdown-timer .timer.minute .hand span {
            animation-duration: 60s;
        }

        .countdown-timer .timer.second .hand span {
            animation-duration: 1s;
        }

        .stopwatch-timer .timer.hour .hand span {
            animation-duration: 43200s; /* 12 hours */
        }

        .stopwatch-timer .timer.minute .hand span {
            animation-duration: 3600s; /* 60 minutes */
        }

        .stopwatch-timer .timer.second .hand span {
            animation-duration: 60s; /* 60 seconds */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="timer-container">
                <div class="header">
                    <h1>Task Timer</h1>
                </div>
                
                <div class="task-selection">
                    <h2>Select a Task</h2>
                    <div class="task-list">
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="task-item" data-task-id="<?php echo $task['id']; ?>" data-task-title="<?php echo htmlspecialchars($task['title']); ?>">
                                    <div>
                                        <div class="task-item-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="task-item-group"><?php echo htmlspecialchars($task['group_name']); ?></div>
                                    </div>
                                    <div>
                                        <span class="task-priority priority-<?php echo strtolower($task['priority']); ?>"><?php echo $task['priority']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-tasks">No tasks available. Create some tasks first.</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="timer-display">
                    <div class="timer-info">
                        <div id="selected-task-info" class="hidden">
                            <h3 class="timer-task-title">No task selected</h3>
                        </div>
                    </div>
                    
                    <div class="timer-type-selector">
                        <button class="timer-type-button active" data-type="stopwatch">Stopwatch</button>
                        <button class="timer-type-button" data-type="countdown">Countdown Timer</button>
                        <button class="timer-type-button" data-type="pomodoro">Pomodoro</button>
                    </div>

                    <div class="focus-mode-container">
                        <button class="hover-button focus-toggle">
                            <span class="text">Focus Mode</span>
                            <div class="hover-content">
                                <span>Activate</span>
                                <span class="arrow-right"></span>
                            </div>
                            <div class="background"></div>
                        </button>
                    </div>
                    
                    <div id="countdown-controls" class="timer-input-group" style="display:none;">
                        <input type="number" class="timer-input" id="hours" min="0" max="99" value="0">
                        <span class="timer-separator">:</span>
                        <input type="number" class="timer-input" id="minutes" min="0" max="59" value="25">
                        <span class="timer-separator">:</span>
                        <input type="number" class="timer-input" id="seconds" min="0" max="59" value="0">
                    </div>

                    <div id="pomodoro-controls" class="timer-input-group" style="display:none;">
                        <div class="pomodoro-settings">
                            <div class="pomodoro-setting">
                                <label>Work</label>
                                <input type="number" class="timer-input" id="pomodoro-work" min="1" max="60" value="25">
                                <span>min</span>
                            </div>
                            <div class="pomodoro-setting">
                                <label>Short Break</label>
                                <input type="number" class="timer-input" id="pomodoro-short-break" min="1" max="30" value="5">
                                <span>min</span>
                            </div>
                            <div class="pomodoro-setting">
                                <label>Long Break</label>
                                <input type="number" class="timer-input" id="pomodoro-long-break" min="1" max="30" value="15">
                                <span>min</span>
                            </div>
                            <div class="pomodoro-setting">
                                <label>Sessions until long break</label>
                                <input type="number" class="timer-input" id="pomodoro-sessions" min="1" max="10" value="4">
                            </div>
                        </div>
                    </div>
                    
                    <div class="timer-group stopwatch-timer" id="timer-display">
                        <div class="timer hour">
                            <div class="hand"><span></span></div>
                            <div class="hand"><span></span></div>
                        </div>
                        <div class="timer minute">
                            <div class="hand"><span></span></div>
                            <div class="hand"><span></span></div>
                        </div>
                        <div class="timer second">
                            <div class="hand"><span></span></div>
                            <div class="hand"><span></span></div>
                        </div>
                        <div class="face">
                            <h2 id="timer-mode-display">Stopwatch</h2>
                            <p id="timer-face">00:00:00</p>
                        </div>
                    </div>

                    <div id="pomodoro-status" class="pomodoro-status" style="display:none;">
                        <div class="pomodoro-phase">Current: <span id="current-phase">Work</span></div>
                        <div class="pomodoro-progress">Session <span id="current-session">1</span>/<span id="total-sessions">4</span></div>
                    </div>
                    
                    <div class="timer-actions">
                        <button class="timer-button start-button" id="start-timer">Start</button>
                        <button class="timer-button pause-button" id="pause-timer" disabled>Pause</button>
                        <button class="timer-button reset-button" id="reset-timer">Reset</button>
                    </div>
                </div>
                
                <div class="timer-history">
                    <h2>Recent Timers</h2>
                    <div class="history-list">
                        <?php if (count($history) > 0): ?>
                            <?php foreach ($history as $entry): ?>
                                <div class="history-item">
                                    <div>
                                        <div class="history-task"><?php echo htmlspecialchars($entry['task_title']); ?></div>
                                        <div class="history-date"><?php echo date('M d, Y - H:i', strtotime($entry['date_created'])); ?></div>
                                    </div>
                                    <div class="history-time"><?php echo formatTimeSpent($entry['time_spent']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-history">No timer history yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM elements
            const taskItems = document.querySelectorAll('.task-item');
            const timerTypeButtons = document.querySelectorAll('.timer-type-button');
            const countdownControls = document.getElementById('countdown-controls');
            const pomodoroControls = document.getElementById('pomodoro-controls');
            const timerDisplay = document.getElementById('timer-display');
            const timerFace = document.getElementById('timer-face');
            const timerModeDisplay = document.getElementById('timer-mode-display');
            const startButton = document.getElementById('start-timer');
            const pauseButton = document.getElementById('pause-timer');
            const resetButton = document.getElementById('reset-timer');
            const selectedTaskInfo = document.getElementById('selected-task-info');
            const focusToggle = document.querySelector('.focus-toggle');
            const pomodoroStatus = document.getElementById('pomodoro-status');
            const currentPhaseElement = document.getElementById('current-phase');
            const currentSessionElement = document.getElementById('current-session');
            const totalSessionsElement = document.getElementById('total-sessions');
            
            // Timer variables
            let selectedTaskId = null;
            let selectedTaskTitle = null;
            let timerMode = 'stopwatch'; // Default timer mode
            let timerRunning = false;
            let timerPaused = false;
            let countdownTime = 0;
            let startTime = 0;
            let elapsedTime = 0;
            let timerInterval = null;
            let focusModeActive = false;

            // Pomodoro variables
            let pomodoroPhase = 'work'; // 'work', 'short-break', 'long-break'
            let pomodoroSession = 1;
            let pomodoroCycle = 1;
            let pomodoroTotalSessions = 4;
            
            // Helper function for formatting time
            function formatTime(timeInSeconds) {
                const hours = Math.floor(timeInSeconds / 3600);
                const minutes = Math.floor((timeInSeconds % 3600) / 60);
                const seconds = timeInSeconds % 60;
                
                return [
                    hours.toString().padStart(2, '0'),
                    minutes.toString().padStart(2, '0'),
                    seconds.toString().padStart(2, '0')
                ].join(':');
            }
            
            // Task selection
            taskItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove selected class from all tasks
                    taskItems.forEach(task => task.classList.remove('selected'));
                    
                    // Add selected class to clicked task
                    this.classList.add('selected');
                    
                    // Set selected task info
                    selectedTaskId = this.dataset.taskId;
                    selectedTaskTitle = this.dataset.taskTitle;
                    
                    // Update displayed task info
                    selectedTaskInfo.innerHTML = `<h3 class="timer-task-title">${selectedTaskTitle}</h3>`;
                    selectedTaskInfo.classList.remove('hidden');
                    
                    // Enable start button if a task is selected
                    startButton.disabled = false;
                });
            });

            function updateAnimationDurations() {
                const hourHands = document.querySelectorAll('.timer.hour .hand span');
                const minuteHands = document.querySelectorAll('.timer.minute .hand span');
                const secondHands = document.querySelectorAll('.timer.second .hand span');
                
                let hourDuration, minuteDuration, secondDuration;
                
                if (timerMode === 'stopwatch') {
                    hourDuration = '43200s'; // 12 hours
                    minuteDuration = '3600s'; // 60 minutes
                    secondDuration = '60s';   // 60 seconds
                } else if (timerMode === 'countdown') {
                    hourDuration = '3600s';   // 1 hour
                    minuteDuration = '60s';   // 60 seconds
                    secondDuration = '1s';    // 1 second
                } else if (timerMode === 'pomodoro') {
                    // Get the work time from the input (converted to seconds)
                    const workMinutes = parseInt(document.getElementById('pomodoro-work').value) || 25;
                    const workSeconds = workMinutes * 60;
                    
                    hourDuration = `${workSeconds}s`;
                    minuteDuration = `${workSeconds}s`;
                    secondDuration = '60s';
                }
                
                // Apply durations to each hand
                hourHands.forEach(hand => {
                    hand.style.animationDuration = hourDuration;
                });
                
                minuteHands.forEach(hand => {
                    hand.style.animationDuration = minuteDuration;
                });
                
                secondHands.forEach(hand => {
                    hand.style.animationDuration = secondDuration;
                });
            }
            
            // Timer type selection
            timerTypeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    timerTypeButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Set timer mode
                    timerMode = this.dataset.type;
                    timerModeDisplay.textContent = timerMode.charAt(0).toUpperCase() + timerMode.slice(1);
                    
                    // Show/hide controls based on timer type
                    countdownControls.style.display = timerMode === 'countdown' ? 'flex' : 'none';
                    pomodoroControls.style.display = timerMode === 'pomodoro' ? 'flex' : 'none';
                    pomodoroStatus.style.display = timerMode === 'pomodoro' ? 'flex' : 'none';

                    // Reset timer
                    resetTimer();

                    // Update timer display classes
                    timerDisplay.classList.remove('stopwatch-timer', 'countdown-timer', 'pomodoro-timer');
                    timerDisplay.classList.add(`${timerMode}-timer`);
                    
                    // Update animation durations based on timer type
                    updateAnimationDurations();
                    
                    // Update pomodoro UI if selected
                    if (timerMode === 'pomodoro') {
                        updatePomodoroUI();
                    }
                });
            });

            // Focus mode toggle
            focusToggle.addEventListener('click', function() {
                focusModeActive = !focusModeActive;
                
                if (focusModeActive) {
                    document.body.classList.add('focus-mode');
                    this.classList.add('active');
                    this.querySelector('.hover-content span:first-child').textContent = 'Deactivate';
                } else {
                    document.body.classList.remove('focus-mode');
                    this.classList.remove('active');
                    this.querySelector('.hover-content span:first-child').textContent = 'Activate';
                }
            });

            function resetClockAnimations() {
                const hands = document.querySelectorAll('.timer .hand span');
                
                hands.forEach(hand => {
                    // Remove animation
                    hand.style.animation = 'none';
                    
                    // Force a reflow (this is very important)
                    void hand.offsetWidth;
                    
                    // Set the starting position
                    hand.style.transform = 'rotate(225deg)';
                });
            }
            
            // Start timer
            startButton.addEventListener('click', function() {
                if (!selectedTaskId) {
                    alert('Please select a task first');
                    return;
                }
                
                if (timerRunning && !timerPaused) {
                    return;
                }

                if (!timerPaused) {
                    // Reset animations before starting
                    resetClockAnimations();
                    
                    // Update animation durations based on current mode
                    updateAnimationDurations();
                    
                    // Slight delay to ensure animations start correctly
                    setTimeout(() => {
                        // Re-apply animations after reset
                        const hands = document.querySelectorAll('.timer .hand span');
                        hands.forEach(hand => {
                            hand.style.animation = '';
                            hand.style.animationPlayState = 'running';
                        });
                        timerDisplay.classList.add('running');
                    }, 50);
                }
                
                if (timerPaused) {
                    // Resume timer
                    timerPaused = false;
                    startTime = Date.now() - elapsedTime;

                    // Resume animations when resuming from pause
                    const hands = document.querySelectorAll('.timer .hand span');
                    hands.forEach(hand => {
                        hand.style.animationPlayState = 'running';
                    });
                } else {
                    // Start new timer
                    if (timerMode === 'countdown') {
                        const hours = parseInt(document.getElementById('hours').value) || 0;
                        const minutes = parseInt(document.getElementById('minutes').value) || 0;
                        const seconds = parseInt(document.getElementById('seconds').value) || 0;
                        
                        countdownTime = hours * 3600 + minutes * 60 + seconds;
                        
                        if (countdownTime <= 0) {
                            alert('Please set a valid time for countdown');
                            return;
                        }
                        
                        startTime = Date.now();
                        elapsedTime = 0;
                    } else if (timerMode === 'pomodoro') {
                        // Get settings from inputs
                        pomodoroSession = 1;
                        pomodoroPhase = 'work';
                        pomodoroTotalSessions = parseInt(document.getElementById('pomodoro-sessions').value) || 4;
                        
                        // Start first work session
                        startPomodoroPhase();
                    } else {
                        // Stopwatch mode
                        startTime = Date.now();
                        elapsedTime = 0;
                    }
                }
                
                // Update UI
                timerRunning = true;
                startButton.disabled = true;
                pauseButton.disabled = false;
                timerDisplay.classList.add('running');
                
                // Start timer interval
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                
                timerInterval = setInterval(updateTimer, 1000);
                updateTimer(); // Update immediately
            });
            
            // Pause timer
            pauseButton.addEventListener('click', function() {
                if (!timerRunning) {
                    return;
                }
                
                clearInterval(timerInterval);
                timerPaused = true;
                timerRunning = false;
                
                // Pause all clock hand animations
                const hands = document.querySelectorAll('.timer .hand span');
                hands.forEach(hand => {
                    hand.style.animationPlayState = 'paused';
                });
                
                // Update UI
                startButton.disabled = false;
                startButton.textContent = 'Resume';
                pauseButton.disabled = true;
                timerDisplay.classList.remove('running');
            });
            
            // Reset timer
            resetButton.addEventListener('click', function() {
                resetTimer();
            });
            
            function resetTimer() {
                clearInterval(timerInterval);
                timerRunning = false;
                timerPaused = false;
                elapsedTime = 0;
                
                // Reset pomodoro status if in pomodoro mode
                if (timerMode === 'pomodoro') {
                    pomodoroPhase = 'work';
                    pomodoroSession = 1;
                    updatePomodoroUI();
                }
                
                // Update UI
                timerFace.textContent = '00:00:00';
                startButton.disabled = false;
                startButton.textContent = 'Start';
                pauseButton.disabled = true;
                timerDisplay.classList.remove('running');
                
                // Reset clock hands animation and ensure they're stopped
                const hands = document.querySelectorAll('.timer .hand span');
                hands.forEach(hand => {
                    hand.style.animation = 'none';
                    hand.style.animationPlayState = 'paused';
                    // Force a reflow
                    void hand.offsetWidth;
                    hand.style.transform = 'rotate(225deg)';
                });
            }
            
            function updateTimer() {
                const now = Date.now();
                
                if (timerMode === 'countdown') {
                    elapsedTime = now - startTime;
                    const remainingSeconds = Math.max(0, countdownTime - Math.floor(elapsedTime / 1000));
                    
                    timerFace.textContent = formatTime(remainingSeconds);
                    
                    if (remainingSeconds <= 0) {
                        finishTimer();
                    }
                } else if (timerMode === 'pomodoro') {
                    elapsedTime = now - startTime;
                    const remainingSeconds = Math.max(0, countdownTime - Math.floor(elapsedTime / 1000));
                    
                    timerFace.textContent = formatTime(remainingSeconds);
                    
                    if (remainingSeconds <= 0) {
                        finishPomodoroPhase();
                    }
                } else {
                    // Stopwatch mode
                    elapsedTime = now - startTime;
                    const seconds = Math.floor(elapsedTime / 1000);
                    
                    timerFace.textContent = formatTime(seconds);
                }
            }
            
            function finishTimer() {
                clearInterval(timerInterval);
                timerRunning = false;
                timerPaused = false;
                
                // Update UI
                startButton.disabled = false;
                startButton.textContent = 'Start';
                pauseButton.disabled = true;
                timerDisplay.classList.remove('running');
                
                // For countdown, show alert
                if (timerMode === 'countdown') {
                    alert('Timer completed for task: ' + selectedTaskTitle);
                }
                
                // Save timer data to database using AJAX
                const timeSpent = Math.floor(elapsedTime / 1000);
                
                if (timeSpent > 0) {
                    saveTimerHistory(selectedTaskId, timeSpent);
                }
            }

            // Pomodoro-specific functions
            function startPomodoroPhase() {
                // Set the timer based on the current phase
                let minutes = 25; // Default work time
                
                if (pomodoroPhase === 'work') {
                    minutes = parseInt(document.getElementById('pomodoro-work').value) || 25;
                    currentPhaseElement.textContent = 'Work';
                    currentPhaseElement.className = 'phase-work';
                    timerDisplay.style.setProperty('--accent-color', 'var(--accent-blue)');
                } else if (pomodoroPhase === 'short-break') {
                    minutes = parseInt(document.getElementById('pomodoro-short-break').value) || 5;
                    currentPhaseElement.textContent = 'Short Break';
                    currentPhaseElement.className = 'phase-short-break';
                    timerDisplay.style.setProperty('--accent-color', 'var(--accent-yellow)');
                } else if (pomodoroPhase === 'long-break') {
                    minutes = parseInt(document.getElementById('pomodoro-long-break').value) || 15;
                    currentPhaseElement.textContent = 'Long Break';
                    currentPhaseElement.className = 'phase-long-break';
                    timerDisplay.style.setProperty('--accent-color', 'var(--accent-pink)');
                }
                
                // Set the countdown time
                countdownTime = minutes * 60;
                
                // Reset animations before starting
                resetClockAnimations();
                
                // Update the animation durations for the new phase
                const seconds = countdownTime;
                const hourHands = document.querySelectorAll('.timer.hour .hand span');
                const minuteHands = document.querySelectorAll('.timer.minute .hand span');
                const secondHands = document.querySelectorAll('.timer.second .hand span');
                
                // Set animation durations
                hourHands.forEach(hand => {
                    hand.style.animationDuration = `${seconds}s`;
                });
                
                minuteHands.forEach(hand => {
                    hand.style.animationDuration = `${seconds}s`;
                });
                
                secondHands.forEach(hand => {
                    hand.style.animationDuration = '60s';
                });
                
                // Update timer display
                startTime = Date.now();
                elapsedTime = 0;
                
                // Update pomodoro UI
                updatePomodoroUI();
                
                // Clear existing interval if any
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                
                // Start new timer interval
                timerInterval = setInterval(updateTimer, 1000);
                updateTimer(); // Update immediately
                
                // Ensure animations start running with a slight delay
                setTimeout(() => {
                    const hands = document.querySelectorAll('.timer .hand span');
                    hands.forEach(hand => {
                        hand.style.animation = '';
                        hand.style.animationPlayState = 'running';
                    });
                    timerDisplay.classList.add('running');
                }, 50);
                
                // Set timer state
                timerRunning = true;
                timerPaused = false;
                startButton.disabled = true;
                pauseButton.disabled = false;
            }

            function finishPomodoroPhase() {
                // Play notification sound
                playNotificationSound();
                
                // Save timer data to database if it was a work session
                if (pomodoroPhase === 'work') {
                    const timeSpent = Math.floor(elapsedTime / 1000);
                    saveTimerHistory(selectedTaskId, timeSpent);
                }
                
                // Clear the current interval
                clearInterval(timerInterval);
                
                // Move to the next phase
                if (pomodoroPhase === 'work') {
                    // After work, check if it's time for a long break
                    if (pomodoroSession % pomodoroTotalSessions === 0) {
                        pomodoroPhase = 'long-break';
                    } else {
                        pomodoroPhase = 'short-break';
                    }
                } else {
                    // After any break, go back to work
                    pomodoroPhase = 'work';
                    // Increment session count if starting a new work session
                    pomodoroSession++;
                }
                
                // Show notification
                showPomodoroNotification();
                
                // Start the next phase with a slight delay
                setTimeout(() => {
                    startPomodoroPhase();
                }, 100);
            }

            function updatePomodoroUI() {
                // Update session display
                currentSessionElement.textContent = pomodoroSession;
                totalSessionsElement.textContent = pomodoroTotalSessions;
                
                // Update phase display
                if (pomodoroPhase === 'work') {
                    currentPhaseElement.textContent = 'Work';
                    currentPhaseElement.className = 'phase-work';
                } else if (pomodoroPhase === 'short-break') {
                    currentPhaseElement.textContent = 'Short Break';
                    currentPhaseElement.className = 'phase-short-break';
                } else if (pomodoroPhase === 'long-break') {
                    currentPhaseElement.textContent = 'Long Break';
                    currentPhaseElement.className = 'phase-long-break';
                }
            }

            function playNotificationSound() {
                // Create an audio element with the new WAV file
                const audio = new Audio('mixkit-long-pop-2358.wav');

                // Play the sound
                audio.play().catch(error => {
                    console.error('Error playing notification sound:', error);
                });
            }

            function showPomodoroNotification() {
                // Check if browser supports notifications
                if (!("Notification" in window)) {
                    console.log("This browser does not support desktop notification");
                    return;
                }
                
                // Check notification permission
                if (Notification.permission === "granted") {
                    // Create notification
                    createPomodoroNotification();
                } else if (Notification.permission !== "denied") {
                    // Request permission
                    Notification.requestPermission().then(function (permission) {
                        if (permission === "granted") {
                            createPomodoroNotification();
                        }
                    });
                }
            }

            function createPomodoroNotification() {
                // Determine notification content based on current phase
                let title, body, icon;
                
                if (pomodoroPhase === 'work') {
                    title = "Work Session Started";
                    body = `Focus on your task: ${selectedTaskTitle || 'Selected task'}`;
                    icon = "work-icon.svg"; // Replace with your actual work icon
                } else if (pomodoroPhase === 'short-break') {
                    title = "Short Break Time";
                    body = "Take a quick break to refresh your mind!";
                    icon = "short-break-icon.svg"; // Replace with your actual break icon
                } else if (pomodoroPhase === 'long-break') {
                    title = "Long Break Time";
                    body = "You've earned a longer break. Rest well!";
                    icon = "long-break-icon.svg"; // Replace with your actual long break icon
                }
                
                // Create and show notification
                const notification = new Notification(title, {
                    body: body,
                    icon: icon
                });
                
                // Close notification after a few seconds
                setTimeout(() => {
                    notification.close();
                }, 5000);
                
                // Handle notification click
                notification.onclick = function() {
                    window.focus();
                    this.close();
                };
            }
            
            function saveTimerHistory(taskId, timeSpent) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'save_timer.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Refresh the history section or add the new entry dynamically
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // You could update the history list here without a page reload
                        }
                    }
                };
                
                xhr.send(`task_id=${taskId}&time_spent=${timeSpent}`);
            }
        });
    </script>
</body>
</html>

<?php
// Helper function to format time spent
function formatTimeSpent($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}
?>