<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Chat - Time Leap</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="notification.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-primary);
            line-height: 1.6;
            padding: 0;
            min-height: 100vh;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
            background-color: #111827;
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        #chat-header {
            padding: 1rem 1.5rem;
            background-color: #1f2937;
            font-weight: 600;
            border-bottom: 1px solid #374151;
            display: flex;
            align-items: center;
            border-radius: 10px 10px 0 0;
            letter-spacing: 0.5px;
            position: relative;
        }

        #chat-header::before {
            content: "";
            width: 10px;
            height: 10px;
            background-color: #3b82f6;
            border-radius: 50%;
            margin-right: 12px;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #111827;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.03) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.03) 0%, transparent 20%);
            scrollbar-width: thin;
            scrollbar-color: #374151 #111827;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #111827;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background-color: #374151;
            border-radius: 6px;
        }

        .chat-messages .my-message {
            text-align: right;
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            padding: 0.75rem 1rem;
            margin: 0.75rem 0;
            border-radius: 1.2rem 1.2rem 0 1.2rem;
            max-width: 60%;
            margin-left: auto;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);
            position: relative;
            animation: slideFromRight 0.3s ease-out;
            word-wrap: break-word;
        }

        .chat-messages .their-message {
            text-align: left;
            background: linear-gradient(135deg, #374151, #1f2937);
            padding: 0.75rem 1rem;
            margin: 0.75rem 0;
            border-radius: 1.2rem 1.2rem 1.2rem 0;
            max-width: 60%;
            box-shadow: 0 2px 10px rgba(31, 41, 55, 0.3);
            position: relative;
            animation: slideFromLeft 0.3s ease-out;
            word-wrap: break-word;
        }

        @keyframes slideFromRight {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideFromLeft {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .chat-input-area {
            display: flex;
            padding: 1rem;
            background-color: #1f2937;
            border-top: 1px solid #374151;
            border-radius: 0 0 10px 10px;
        }

        #message-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            background-color: #111827;
            color: white;
            border: 1px solid #374151;
            border-radius: 1.5rem;
            transition: all 0.3s ease;
            outline: none;
            font-size: 0.95rem;
        }

        #message-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        #message-input::placeholder {
            color: #6b7280;
        }

        #send-button {
            padding: 0 1.5rem;
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            color: white;
            border: none;
            margin-left: 0.75rem;
            border-radius: 1.5rem;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #send-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        #send-button:active {
            transform: translateY(0);
        }

        .loader {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-left-color: #3b82f6;
            height: 24px;
            width: 24px;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 1.5rem auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty state styling */
        .empty-chat-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
            padding: 2rem;
            text-align: center;
        }

        .empty-chat-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .empty-chat-state h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .empty-chat-state p {
            max-width: 400px;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar Include -->
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Topbar -->
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

            <!-- Friend Chat Container -->
            <div class="chat-container">
                <div id="chat-header">Select a friend to chat</div>
                <div id="chat-messages" class="chat-messages">
                    <div class="empty-chat-state">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 11.5a8.38 8.38 0 0 1-9 8.4 8.5 8.5 0 0 1-7.1-3.9L3 21l4-1a8.38 8.38 0 0 0 5 1.5 8.5 8.5 0 0 0 8.5-8.5c0-4.8-3.9-8.5-8.5-8.5s-8.5 3.7-8.5 8.5"></path>
                        </svg>
                        <h3>Start a conversation</h3>
                        <p>Select a friend from the sidebar to begin chatting</p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="message-input" placeholder="Type a message...">
                    <button id="send-button">
                        Send
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 6px;">
                            <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div> <!-- End main-content -->
    </div> <!-- End container -->

    <!-- JS Scripts -->
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>

    <script>
        const currentUserId = "<?php echo $user_id; ?>";
        let activeFriendId = null;
        let lastMessageId = 0;
        let pollingInterval = null;

        // Open Chat with Friend
        function openFriendChat(friendId, friendName) {
            activeFriendId = friendId;
            lastMessageId = 0;

            document.getElementById('chat-header').innerText = `Chat with ${friendName}`;

            const chatBox = document.getElementById('chat-messages');
            chatBox.innerHTML = '<div class="loader"></div>';

            fetch(`get_friend_messages.php?friend_id=${friendId}`)
                .then(response => response.json())
                .then(messages => {
                    chatBox.innerHTML = '';

                    if (!messages.length) {
                        chatBox.innerHTML = '<p>No messages yet.</p>';
                    }

                    messages.forEach(msg => {
                        appendMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;
                });

            if (pollingInterval) clearInterval(pollingInterval);

            pollingInterval = setInterval(() => {
                getNewFriendMessages(friendId);
            }, 3000);
        }

        // Append Messages to Chat Box
        function appendMessage(msg) {
            const chatBox = document.getElementById('chat-messages');
            const msgElement = document.createElement('div');

            msgElement.className = msg.sender_id === currentUserId ? 'my-message' : 'their-message';
            msgElement.dataset.messageId = msg.id;
            msgElement.innerText = msg.message;

            chatBox.appendChild(msgElement);
        }

        // Get New Messages from Server
        function getNewFriendMessages(friendId) {
            fetch(`get_new_friend_messages.php?friend_id=${friendId}&last_message_id=${lastMessageId}`)
                .then(response => response.json())
                .then(messages => {
                    const chatBox = document.getElementById('chat-messages');

                    if (messages.length > 0) {
                        messages.forEach(msg => {
                            appendMessage(msg);
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });

                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                })
                .catch(error => {
                    console.error('Error fetching new messages:', error);
                });
        }

        // Send Message Handler
        const sendButton = document.getElementById('send-button');
        const messageInput = document.getElementById('message-input');

        sendButton.addEventListener('click', sendFriendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendFriendMessage();
        });

        function sendFriendMessage() {
            const message = messageInput.value.trim();

            if (!message || !activeFriendId) {
                alert('Please type a message and select a friend.');
                return;
            }

            fetch('send_friend_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `receiver_id=${activeFriendId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    getNewFriendMessages(activeFriendId); // Optionally fetch immediately
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
        }

        // Clear polling when leaving page
        window.addEventListener('beforeunload', () => {
            if (pollingInterval) clearInterval(pollingInterval);
        });
    </script>
</body>
</html>
