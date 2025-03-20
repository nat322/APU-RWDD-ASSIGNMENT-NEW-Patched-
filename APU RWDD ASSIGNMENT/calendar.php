<?php
// Include your database connection
session_start();
require 'db.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    // Optionally redirect or throw an error
    die("Unauthorized access.");
}

// Handle form submission to add/edit events
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] == 'add') {
                // INSERT new event
                $stmt = $conn->prepare("INSERT INTO events 
                    (user_id, team_id, title, description, event_date, event_time) 
                    VALUES (:user_id, :team_id, :title, :description, :event_date, :event_time)");

                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $team_id = isset($_POST['team_id']) ? $_POST['team_id'] : null; // Optional field
                $stmt->bindParam(':team_id', $team_id);

                $stmt->bindParam(':title', $_POST['title']);
                $stmt->bindParam(':description', $_POST['description']);
                $stmt->bindParam(':event_date', $_POST['event_date']);

                $event_time = !empty($_POST['event_time']) ? $_POST['event_time'] : null;
                $stmt->bindParam(':event_time', $event_time);

                $stmt->execute();
                $message = "Event added successfully!";

            } elseif ($_POST['action'] == 'edit' && isset($_POST['id'])) {
                // UPDATE existing event, and ensure it's only for this user
                $stmt = $conn->prepare("UPDATE events SET 
                        title = :title, description = :description, 
                        event_date = :event_date, event_time = :event_time 
                        WHERE id = :id AND user_id = :user_id");

                $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':title', $_POST['title']);
                $stmt->bindParam(':description', $_POST['description']);
                $stmt->bindParam(':event_date', $_POST['event_date']);

                $event_time = !empty($_POST['event_time']) ? $_POST['event_time'] : null;
                $stmt->bindParam(':event_time', $event_time);

                $stmt->execute();
                $message = "Event updated successfully!";

            } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
                // DELETE event, ensure user owns it
                $stmt = $conn->prepare("DELETE FROM events WHERE id = :id AND user_id = :user_id");
                $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();

                $message = "Event deleted successfully!";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Ensure valid month/year
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// Get events for the selected month
$startDate = "$year-$month-01";
$endDate = date('Y-m-t', strtotime($startDate));

try {
    $stmt = $conn->prepare("SELECT * FROM events 
                        WHERE user_id = :user_id 
                        AND event_date BETWEEN :startDate AND :endDate 
                        ORDER BY event_date, event_time");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert events to associative array for easy lookup
    $eventsByDate = [];
    foreach ($events as $event) {
        $date = $event['event_date'];
        if (!isset($eventsByDate[$date])) {
            $eventsByDate[$date] = [];
        }
        $eventsByDate[$date][] = $event;
    }
} catch (PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
    $eventsByDate = [];
}

// Get calendar details
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberOfDays = date('t', $firstDayOfMonth);
$firstDayOfWeek = date('w', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

// Function to get event for specific date
function getEvents($date, $eventsByDate) {
    return isset($eventsByDate[$date]) ? $eventsByDate[$date] : [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Events</title>
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
    <style>
        :root {
            --main-color: #1f2937;
            --text-light: #f9fafb;
            --highlight: #3b82f6;
            --border-color: #4b5563;
            --bg-light: #374151;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: var(--main-color);
            color: var(--text-light);
            margin: 0;
        }
        
        h1, h2, h3 {
            color: var(--text-light);
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .calendar-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .calendar-nav a {
            color: var(--text-light);
            text-decoration: none;
            padding: 8px 12px;
            background-color: var(--bg-light);
            border-radius: 4px;
            min-width: 60px;
            text-align: center;
        }

        .calendar-nav a:hover {
            background-color: var(--highlight);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: var(--bg-light);
            color: var(--text-light);
            padding: 10px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        td {
            height: 120px;
            vertical-align: top;
            padding: 5px;
            border: 1px solid var(--border-color);
            background-color: rgba(255, 255, 255, 0.05);
        }

        td.today {
            background-color: rgba(59, 130, 246,0.3);
            border: 2px solid var(--highlight);
        }
        
        td.empty {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .date-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .event {
            background-color: var(--highlight);
            color: var(--text-light);
            margin: 2px 0;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .event:hover {
            background-color: #2563eb;
        }
        
        .add-event {
            display: inline-block;
            background-color: var(--bg-light);
            color: var(--text-light);
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 11px;
            cursor: pointer;
            margin-left: 5px;
        }
        
        .add-event:hover {
            background-color: var(--highlight);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background-color: var(--main-color);
            border: 1px solid var(--border-color);
            width: 80%;
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 8px;
        }
        
        .close {
            color: var(--text-light);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--highlight);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
        }
        
        input[type="text"], textarea, input[type="date"], input[type="time"] {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--bg-light);
            color: var(--text-light);
        }
        
        button {
            background-color: var(--highlight);
            color: var(--text-light);
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #2563eb;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #10b981;
            color: white;
        }
        
        .alert-error {
            background-color: #ef4444;
            color: white;
        }

        /* Responsive adjustments for small screens */
        @media (max-width: 768px) {
            .calendar-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .calendar-nav {
                width: 100%;
                justify-content: space-between;
                margin-bottom: 15px;
            }
            
            .calendar-nav a {
                flex: 1;
                margin: 0;
                min-width: 0;
            }
        }

        /* Further adjustments for very small screens */
        @media (max-width: 480px) {
            .calendar-nav {
                gap: 5px;
            }
            
            .calendar-nav a {
                padding: 6px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="topbar">
                <div class="menu-icon">☰</div> <!-- Mobile menu toggle -->
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="Search Time Leap...">
                </div>
                <div class="notification">
                    <span id="notification-icon">🔔</span>
                    <span id="notification-dot" class="hidden"></span>
                    <div id="notification-dropdown" class="hidden">
                        <div class="dropdown-header">Friend Requests</div>
                        <ul id="notification-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Friend Request Overlay -->
            <div id="friend-request-overlay" class="hidden">
                <div class="overlay-content">
                    <h3>Friend Request</h3>
                    <p id="request-message"></p>
                    <button id="accept-request">Accept</button>
                    <button id="decline-request">Decline</button>
                    <button id="close-overlay">Close</button>
                </div>
            </div>
            
            <h1>Calendar Events</h1>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="?month=<?php echo $month-1; ?>&year=<?php echo $year; ?>">&lt; Prev</a>
                    <a href="?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>">Today</a>
                    <a href="?month=<?php echo $month+1; ?>&year=<?php echo $year; ?>">Next &gt;</a>
                </div>
                <h2><?php echo $monthName . ' ' . $year; ?></h2>
                <button onclick="showAddEventModal()">Add New Event</button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Calendar grid
                    $dayCounter = 1;
                    $calendar = "<tr>";
                    
                    // Fill empty cells for first week
                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                        $calendar .= "<td class='empty'></td>";
                    }
                    
                    // Fill days
                    while ($dayCounter <= $numberOfDays) {
                        if (($dayCounter + $firstDayOfWeek - 1) % 7 == 0 && $dayCounter != 1) {
                            $calendar .= "</tr><tr>";
                        }
                        
                        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $dayCounter);
                        $dayEvents = getEvents($currentDate, $eventsByDate);
                        
                        // Check if this is today's date
                        $isToday = ($year == date('Y') && $month == date('m') && $dayCounter == date('d'));
                        $todayClass = $isToday ? ' class="today"' : '';
                        
                        $calendar .= "<td$todayClass>";
                        $calendar .= "<div class='date-number'>" . $dayCounter . "<span class='add-event' onclick='showAddEventModal(\"$currentDate\")'>+</span></div>";
                        
                        // Display events for this day
                        foreach ($dayEvents as $event) {
                            $eventTime = $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : '';
                            $calendar .= "<div class='event' onclick='showEventDetails(" . json_encode($event) . ")'>" . 
                                        ($eventTime ? "$eventTime - " : "") . htmlspecialchars($event['title']) . 
                                        "</div>";
                        }
                        
                        $calendar .= "</td>";
                        $dayCounter++;
                    }               
                    // Fill empty cells for last week
                    $remainingDays = 7 - (($dayCounter - 1 + $firstDayOfWeek) % 7);
                    if ($remainingDays < 7) {
                        for ($i = 0; $i < $remainingDays; $i++) {
                            $calendar .= "<td class='empty'></td>";
                        }
                    }
                    
                    $calendar .= "</tr>";
                    echo $calendar;
                    ?>
                </tbody>
            </table>
        </div><!-- End of main-content -->
    </div><!-- End of container -->
    
    <!-- Add/Edit Event Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('eventModal')">&times;</span>
            <h3 id="modalTitle">Add New Event</h3>
            <form method="POST" action="">
                <input type="hidden" id="action" name="action" value="add">
                <input type="hidden" id="eventId" name="id" value="">
                
                <div class="form-group">
                    <label for="title">Event Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Date:</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Time (optional):</label>
                    <input type="time" id="event_time" name="event_time">
                </div>
                
                <button type="submit">Save Event</button>
                <button type="button" id="deleteBtn" style="background-color: #ef4444; display: none;" onclick="confirmDelete()">Delete Event</button>
            </form>
        </div>
    </div>
    
    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('eventDetailsModal')">&times;</span>
            <h3 id="detailsTitle"></h3>
            <div id="eventDetails">
                <p><strong>Date: </strong><span id="detailsDate"></span></p>
                <p><strong>Time: </strong><span id="detailsTime"></span></p>
                <p><strong>Description: </strong></p>
                <p id="detailsDescription"></p>
                <p><small>Last updated: <span id="detailsUpdated"></span></small></p>
            </div>
            <button onclick="editEvent()">Edit Event</button>
        </div>
    </div>
    <script>
        // Show add/edit event modal
        function showAddEventModal(date = null) {
            document.getElementById('modalTitle').innerText = 'Add New Event';
            document.getElementById('action').value = 'add';
            document.getElementById('eventId').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('event_date').value = date || '';
            document.getElementById('event_time').value = '';
            document.getElementById('deleteBtn').style.display = 'none';
            
            document.getElementById('eventModal').style.display = 'block';
        }
        
        // Show event details
        function showEventDetails(event) {
            document.getElementById('detailsTitle').innerText = event.title;
            
            // Format date for display
            const eventDate = new Date(event.event_date);
            const formattedDate = eventDate.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById('detailsDate').innerText = formattedDate;
            
            // Format time if present
            if (event.event_time) {
                const timeDisplay = new Date('2000-01-01T' + event.event_time).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                document.getElementById('detailsTime').innerText = timeDisplay;
            } else {
                document.getElementById('detailsTime').innerText = 'All day';
            }
            
            document.getElementById('detailsDescription').innerText = event.description || 'No description provided.';
            document.getElementById('detailsUpdated').innerText = new Date(event.updated_at).toLocaleString();
            
            // Store event data for editing
            window.currentEvent = event;
            
            document.getElementById('eventDetailsModal').style.display = 'block';
        }
        
        // Edit current event
        function editEvent() {
            if (!window.currentEvent) return;
            
            document.getElementById('modalTitle').innerText = 'Edit Event';
            document.getElementById('action').value = 'edit';
            document.getElementById('eventId').value = window.currentEvent.id;
            document.getElementById('title').value = window.currentEvent.title;
            document.getElementById('description').value = window.currentEvent.description || '';
            document.getElementById('event_date').value = window.currentEvent.event_date;
            document.getElementById('event_time').value = window.currentEvent.event_time || '';
            document.getElementById('deleteBtn').style.display = 'inline-block';
            
            closeModal('eventDetailsModal');
            document.getElementById('eventModal').style.display = 'block';
        }
        
        // Confirm delete event
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this event?')) {
                document.getElementById('action').value = 'delete';
                document.querySelector('#eventModal form').submit();
            }
        }
        
        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const eventModal = document.getElementById('eventModal');
            const detailsModal = document.getElementById('eventDetailsModal');
            
            if (event.target == eventModal) {
                eventModal.style.display = 'none';
            }
            
            if (event.target == detailsModal) {
                detailsModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>