<?php 
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    echo "User not logged in.";
    exit;
}

// Get team_id from GET parameter or session
if (isset($_GET['team_id'])) {
    $_SESSION['team_id'] = $_GET['team_id'];
} elseif (!isset($_SESSION['team_id'])) {
    // If no team ID is set, try fetching from localStorage via JavaScript
    echo "<script>
        let storedTeamId = localStorage.getItem('selectedTeamId');
        if (storedTeamId) {
            window.location.href = 'chat.php?team_id=' + storedTeamId;
        } else {
            document.write('Error: Team ID is missing!');
        }
    </script>";
    exit;
}

$team_id = $_SESSION['team_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch team details
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = :team_id");
$stmt->bindParam(':team_id', $team_id);
$stmt->execute();
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    echo "Error: Team not found!";
    exit;
}

// Fetch active users for this team
$activeUsersQuery = $conn->prepare("
    SELECT u.user_id, u.name 
    FROM users u 
    JOIN active_users a ON u.user_id = a.user_id
    JOIN team_members tm ON u.user_id = tm.user_id
    WHERE tm.team_id = :team_id
");
$activeUsersQuery->bindParam(':team_id', $team_id);
$activeUsersQuery->execute();
$activeUsers = $activeUsersQuery->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['team_name']); ?> - Chat Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1f2937;
            --secondary-color: #374151;
            --accent-color: #3b82f6;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --message-sent: #3b82f6;
            --message-received: #4b5563;
            --border-color: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--primary-color);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-container {
            display: flex;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Back button styles */
        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 100;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.2s;
        }

        .back-button:hover {
            background-color: #2563eb;
        }

        /* Toggle users button */
        .toggle-users-btn {
            position: absolute;
            top: 15px;
            left: 65px;
            z-index: 100;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.2s;
        }

        .toggle-users-btn:hover {
            background-color: #2563eb;
        }

        /* Active users panel */
        .users-panel {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 280px;
            background-color: var(--secondary-color);
            z-index: 90;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .users-panel.show {
            transform: translateX(0);
        }

        .team-header {
            padding: 15px;
            background-color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 50px;
        }

        .team-name {
            font-size: 18px;
            font-weight: 600;
        }

        .team-info-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.2s;
        }

        .team-info-btn:hover {
            color: var(--text-primary);
        }

        .active-users-container {
            padding: 15px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .active-users-title {
            font-size: 14px;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .active-users-list {
            list-style: none;
        }

        .active-user {
            display: flex;
            align-items: center;
            padding: 8px 0;
            transition: background-color 0.2s;
            border-radius: 6px;
            margin-bottom: 5px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background-color: var(--accent-color);
            border-radius: 50%;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .user-name {
            font-size: 15px;
        }

        .online-indicator {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            margin-left: auto;
        }

        /* Main chat area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            padding-top: 60px;
        }

        .chat-header {
            padding: 15px 20px;
            background-color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .chat-title {
            font-size: 18px;
            font-weight: 600;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 10px;
            word-break: break-word;
            position: relative;
        }

        .message-header {
            font-size: 13px;
            margin-bottom: 3px;
            font-weight: 600;
        }

        .message-content {
            font-size: 15px;
            line-height: 1.4;
        }

        .my-message {
            background-color: var(--message-sent);
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .other-message {
            background-color: var(--message-received);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .chat-input-container {
            padding: 15px;
            background-color: var(--secondary-color);
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 20px;
            background-color: var(--primary-color);
            color: var(--text-primary);
            font-size: 15px;
            outline: none;
        }

        .send-button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .send-button:hover {
            background-color: #2563eb;
        }

        /* Team info modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 200;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: var(--secondary-color);
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--text-primary);
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .team-detail {
            margin-bottom: 15px;
        }

        .detail-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            padding: 8px 12px;
            background-color: var(--primary-color);
            border-radius: 6px;
            word-break: break-all;
        }

        /* Overlay for when users panel is open on mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 80;
        }

        @media (max-width: 768px) {
            .users-panel {
                width: 80%;
            }
            
            .overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Back button -->
        <button class="back-button" onclick="clearTeamSelection(); goBack()">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <!-- Toggle users button -->
        <button class="toggle-users-btn" onclick="toggleUsersPanel()">
            <i class="fas fa-users"></i>
        </button>
        
        <!-- Overlay for mobile -->
        <div id="overlay" class="overlay" onclick="toggleUsersPanel()"></div>
        
        <!-- Active users panel -->
        <div id="usersPanel" class="users-panel">
            <div class="team-header">
                <div class="team-name"><?php echo htmlspecialchars($team['team_name']); ?></div>
                <button class="team-info-btn" onclick="openTeamInfoModal()">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
            <div class="active-users-container">
                <div class="active-users-title">Active Users</div>
                <div id="active-users-list" class="active-users-list">
                    <!-- Active users will be populated here -->
                </div>
            </div>
        </div>

        <!-- Main chat area -->
        <div class="chat-area">
            <div class="chat-header">
                <div class="chat-title"><?php echo htmlspecialchars($team['team_name']); ?> - Team Chat</div>
            </div>
            <div id="chat-box" class="chat-messages">
                <!-- Messages will be populated here -->
            </div>
            <div class="chat-input-container">
                <input type="text" id="message" class="chat-input" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="send-button" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Team Info Modal -->
    <div id="teamInfoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Team Information</div>
                <button class="close-modal" onclick="closeTeamInfoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="team-detail">
                    <div class="detail-label">Team Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($team['team_name']); ?></div>
                </div>
                <div class="team-detail">
                    <div class="detail-label">Team ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($team['id']); ?></div>
                </div>
                <div class="team-detail">
                    <div class="detail-label">Created On</div>
                    <div class="detail-value"><?php echo htmlspecialchars(date('F j, Y', strtotime($team['created_at']))); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const username = <?php echo json_encode($_SESSION['username']); ?>;
        const teamId = <?php echo json_encode($team_id); ?>;
        let chatSocket;
        let lastMessageId = 0;
        let isPolling = false;
        let pollingInterval;

        // DOM elements
        const chatBox = document.getElementById('chat-box');
        const messageInput = document.getElementById('message');
        const usersPanel = document.getElementById('usersPanel');
        const overlay = document.getElementById('overlay');
        const activeUsersList = document.getElementById('active-users-list');

        // Function to go back to previous page
        function goBack() {
            window.location.href = 'teams.php';
        }

        function clearTeamSelection() {
            localStorage.removeItem('selectedTeamId');
        }

        // Function to toggle users panel
        function toggleUsersPanel() {
            usersPanel.classList.toggle('show');
            overlay.classList.toggle('show');
            
            // Hide/show back button when users panel is shown/hidden
            const backButton = document.querySelector('.back-button');
            if (usersPanel.classList.contains('show')) {
                backButton.style.display = 'none';
            } else {
                backButton.style.display = 'flex';
            }
        }

        // Team info modal functions
        function openTeamInfoModal() {
            document.getElementById('teamInfoModal').style.display = 'block';
        }

        function closeTeamInfoModal() {
            document.getElementById('teamInfoModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('teamInfoModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };

        // AJAX function to send a message
        let isSending = false;

        function sendMessage() {
            if (isSending) return; // Prevent multiple submissions
            isSending = true;

            const message = messageInput.value.trim();
            if (!message) {
                isSending = false;
                return;
            }

            // Check if the message is a command
            if (message.startsWith('/')) {
                handleCommand(message);
                messageInput.value = ''; // Clear input after executing command
                isSending = false;
                return;
            }

            // Normal message sending
            const formData = new FormData();
            formData.append('team_id', teamId);
            formData.append('message', message);

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    fetchNewMessages();
                    updateActiveStatus();
                } else {
                    console.error('Error sending message:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                isSending = false; // Reset flag after request completes
            });
        }

        // Handle commands
        function handleCommand(command) {
            const regex = /^\/schedule\s+(?:"([^"]+)"|(\S+))\s+(\d{4}-\d{2}-\d{2})\s+"([^"]+)"\s+(\d{2}:\d{2})\s+"([^"]*)"$/;
            const match = command.match(regex);

            // Retrieve team_id from localStorage
            const teamId = localStorage.getItem("selectedTeamId");

            if (match) {
                const mentionedUser = match[1] || match[2];  // Supports quoted & unquoted usernames
                const eventDate = match[3];   // Extract date (YYYY-MM-DD)
                const eventTitle = match[4];  // Extract event title
                const eventTime = match[5];   // Extract event time (HH:MM)
                const eventDescription = match[6] || ''; // Extract event description (optional)

                if (!teamId) {
                    console.error('Error: teamId is not set.');
                    alert('Error: No team selected. Please select a team first.');
                    return;
                }

                // Send event details to schedule_event.php
                const formData = new FormData();
                formData.append('action', 'schedule_event');
                formData.append('team_id', teamId);
                formData.append('username', mentionedUser);
                formData.append('date', eventDate);
                formData.append('time', eventTime);
                formData.append('title', eventTitle);
                formData.append('description', eventDescription);

                fetch('schedule_event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Scheduled event for ${mentionedUser} on ${eventDate} at ${eventTime}: ${eventTitle} - ${eventDescription}`);
                    } else {
                        console.error('Error scheduling event:', data.error);
                        alert('Failed to schedule event. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error. Please check your connection.');
                });
            } else {
                alert('Invalid command. Use: /schedule "@username" YYYY-MM-DD "Event Title" HH:MM "Event Description"');
            }
        }

        // Function to fetch new messages via AJAX
        function fetchNewMessages() {
            if (isPolling) return;
            isPolling = true;

            fetch(`get_new_messages.php?team_id=${teamId}&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        data.forEach(message => {
                            if (parseInt(message.id) > lastMessageId) {
                                displayMessage({
                                    id: message.id,
                                    userId: message.user_id,
                                    username: message.username,
                                    message: message.message
                                });
                                lastMessageId = parseInt(message.id); // Update last message ID
                            }
                        });

                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                    isPolling = false;
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                    isPolling = false;
                });
        }

        // Function to update active status
        function updateActiveStatus() {
            fetch('update_active_status.php', {
                method: 'POST'
            })
            .catch(error => {
                console.error('Error updating active status:', error);
            });
        }

        // Function to fetch active users
        function fetchActiveUsers() {
            fetch(`get_active_users.php?team_id=${teamId}`)
                .then(response => response.json())
                .then(data => {
                    updateActiveUsers(data);
                })
                .catch(error => {
                    console.error('Error fetching active users:', error);
                });
        }

        // Display a message in the chat
        function displayMessage(data) {
            const existingMessages = document.querySelectorAll('.message');
            for (let msg of existingMessages) {
                if (msg.dataset.id === data.id) return; // Prevent duplicate messages
            }

            const isMyMessage = data.userId === userId;
            const messageClass = isMyMessage ? 'my-message' : 'other-message';

            const messageElement = document.createElement('div');
            messageElement.className = `message ${messageClass}`;
            messageElement.dataset.id = data.id; // Store message ID to prevent duplication

            const messageHeaderElement = document.createElement('div');
            messageHeaderElement.className = 'message-header';
            messageHeaderElement.textContent = isMyMessage ? 'You' : data.username;

            const messageContentElement = document.createElement('div');
            messageContentElement.className = 'message-content';
            messageContentElement.textContent = data.message;

            messageElement.appendChild(messageHeaderElement);
            messageElement.appendChild(messageContentElement);

            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Update active users list
        function updateActiveUsers(users) {
            activeUsersList.innerHTML = '';
            
            users.forEach(user => {
                const userElement = document.createElement('div');
                userElement.className = 'active-user';
                
                const avatarElement = document.createElement('div');
                avatarElement.className = 'user-avatar';
                avatarElement.textContent = user.name.charAt(0).toUpperCase();
                
                const nameElement = document.createElement('div');
                nameElement.className = 'user-name';
                nameElement.textContent = user.name;
                
                const indicatorElement = document.createElement('div');
                indicatorElement.className = 'online-indicator';
                
                userElement.appendChild(avatarElement);
                userElement.appendChild(nameElement);
                userElement.appendChild(indicatorElement);
                
                activeUsersList.appendChild(userElement);
            });
        }

        // Load initial messages
        function loadInitialMessages() {
            fetch(`get_messages.php?team_id=${teamId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(message => {
                        displayMessage({
                            userId: message.user_id,
                            username: message.username,
                            message: message.message
                        });
                        
                        // Track the latest message id
                        if (parseInt(message.id) > lastMessageId) {
                            lastMessageId = parseInt(message.id);
                        }
                    });
                    
                    // Scroll to bottom after loading messages
                    chatBox.scrollTop = chatBox.scrollHeight;
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }

        // Initialize active users
        function initializeActiveUsers() {
            const activeUsers = <?php echo json_encode($activeUsers); ?>;
            updateActiveUsers(activeUsers);
        }

        // Handler for page visibility change
        function handleVisibilityChange() {
            if (document.visibilityState === 'visible') {
                // When tab becomes visible, update status and fetch messages
                updateActiveStatus();
                fetchNewMessages();
                fetchActiveUsers();
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize active users from PHP data
            initializeActiveUsers();
            
            // Load initial messages
            loadInitialMessages();
            
            // Update active status when page loads
            updateActiveStatus();
            
            // Set up polling intervals
            pollingInterval = setInterval(() => {
                fetchNewMessages();
                updateActiveStatus();
            }, 3000); // Poll every 3 seconds
            
            // Set up active users polling
            setInterval(fetchActiveUsers, 10000); // Update active users every 10 seconds
            
            // Listen for page visibility changes
            document.addEventListener('visibilitychange', handleVisibilityChange);
            
            // Close the users panel when clicking the overlay (on mobile)
            overlay.addEventListener('click', toggleUsersPanel);
            
            // Add swipe detection for mobile to open/close the users panel
            let touchStartX = 0;
            let touchEndX = 0;
            
            document.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            document.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
            
            function handleSwipe() {
                const swipeThreshold = 100;
                
                // Swipe right to open users panel
                if (touchEndX - touchStartX > swipeThreshold && !usersPanel.classList.contains('show')) {
                    toggleUsersPanel();
                }
                
                // Swipe left to close users panel
                if (touchStartX - touchEndX > swipeThreshold && usersPanel.classList.contains('show')) {
                    toggleUsersPanel();
                }
            }
            
            // Add event listener for Enter key on message input
            messageInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    sendMessage();
                }
            });
        });

        // Clean up when page is unloaded
        window.addEventListener('beforeunload', () => {
            clearInterval(pollingInterval);
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        });
    </script>
</body>
</html>