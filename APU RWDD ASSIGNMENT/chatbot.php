<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    // Destroy any session that exists and force logout
    session_destroy();
    header("Location: index.php"); // Redirect to login page
    exit;
}
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary: #1f2937;
            --primary-dark: #111827;
            --primary-light: #374151;
            --accent: #10b981;
            --accent-dark: #059669;
            --text: #f9fafb;
            --text-secondary: #9ca3af;
            --border: #4b5563;
            --background: #111827;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            padding: 14px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .chatbox {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 20px;
        }
        
        .message-container {
            padding: 20px 0;
            border-bottom: 1px solid rgba(75, 85, 99, 0.3);
        }
        
        .message-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .message-container.user {
            background-color: rgba(31, 41, 55, 0.3);
        }
        
        .message-container.bot {
            background-color: transparent;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: var(--text);
        }
        
        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .avatar.user {
            background-color: #9ca3af;
            color: var(--primary-dark);
        }
        
        .avatar.bot {
            background-color: var(--accent);
            color: white;
        }
        
        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Input Container */
        .input-container {
            position: sticky;
            bottom: 0;
            background-color: var(--background);
            padding: 20px;
            border-top: 1px solid var(--border);
        }
        
        .input-wrapper {
            position: relative;
            border: 1px solid var(--border);
            border-radius: 10px;
            background-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        textarea {
            width: 100%;
            padding: 14px 50px 14px 16px;
            border: none;
            border-radius: 10px;
            resize: none;
            background-color: transparent;
            color: var(--text);
            font-size: 14px;
            outline: none;
            max-height: 200px;
            min-height: 54px;
            overflow-y: auto;
        }
        
        .send-button {
            position: absolute;
            right: 8px;
            bottom: 8px;
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 6px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .send-button:hover {
            background-color: var(--accent-dark);
        }
        
        .send-button svg {
            width: 18px;
            height: 18px;
        }
        
        .hint {
            text-align: center;
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        
        /* Typing Indicator */
        .typing {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 0;
        }
        
        .typing span {
            width: 6px;
            height: 6px;
            background-color: var(--text-secondary);
            border-radius: 50%;
            display: inline-block;
            animation: typingBounce 1.5s infinite ease-in-out;
        }
        
        .typing span:nth-child(1) {
            animation-delay: 0s;
        }
        
        .typing span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typingBounce {
            0%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-4px); }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                left: -220px; /* Initially hidden */
                width: 220px;
                height: 100vh;
                position: fixed;
                transition: left 0.3s ease-in-out;
            }
        
            .sidebar.open {
                left: 0; /* Sidebar slides into view */
            }
            
            .input-container {
                padding: 16px;
            }
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }
        
        /* Message Fade-in Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Messages should push content up rather than extending below viewport */
        .messages-wrapper {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div div class="container">
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

            <div class="header">
                <h1>Productivity Assistant</h1>
            </div>
            
            <div id="chatbox" class="chatbox">
                <div class="messages-wrapper">
                    <!-- Welcome message -->
                    <div class="message-container bot">
                        <div class="message-wrapper">
                            <div class="message-header">
                                <div class="avatar bot">PA</div>
                                Assistant
                            </div>
                            <div class="message">
                                <p>Hello! I'm your productivity assistant. How can I help you be more productive today?</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="input-container">
                <div class="input-wrapper">
                    <textarea id="userInput" placeholder="Message the Productivity Assistant..." rows="1" oninput="autoResize(this)"></textarea>
                    <button class="send-button" onclick="getAdvice()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="hint">Press Enter to send, Shift+Enter for a new line</div>
            </div>
        </div>
    </div>
    <script>
        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }
        
        // Handle Enter key
        document.getElementById('userInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                getAdvice();
            }
        });

        function scrollToBottom() {
            var chatbox = document.getElementById("chatbox");
            chatbox.scrollTop = chatbox.scrollHeight;
        }

        document.addEventListener("DOMContentLoaded", function() {
            scrollToBottom(); // Ensure chat starts at the bottom

            // Observe for new messages and scroll automatically
            const observer = new MutationObserver(scrollToBottom);
            observer.observe(document.getElementById("chatbox"), { childList: true, subtree: true });
        });
             
        async function getAdvice() {
            const userInput = document.getElementById('userInput').value.trim();
            if (!userInput) return;
            
            // Add user message to chat
            addMessage(userInput, 'user');
            
            // Clear input and reset height
            const textarea = document.getElementById('userInput');
            textarea.value = "";
            textarea.style.height = 'auto';
            
            // Show typing indicator
            const typingIndicator = addTypingIndicator();
            
            try {
                // API call would go here
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ input: userInput })
                });
                
                const data = await response.json();
                
                // Simulate thinking delay (1.5s to 2.5s)
                setTimeout(() => {
                    removeTypingIndicator(typingIndicator);
                    addMessage(data.advice || "I'm here to help you be more productive. Let me know what you need assistance with.", 'bot');
                }, Math.random() * 1000 + 1500);
            } catch (error) {
                // If API fails, show fallback response after delay
                setTimeout(() => {
                    removeTypingIndicator(typingIndicator);
                    addMessage("I'm here to help you be more productive. How can I assist you today?", 'bot');
                }, Math.random() * 1000 + 1500);
            }
        }
        
        function addMessage(text, sender) {
            const chatbox = document.getElementById('chatbox');
            const messagesWrapper = document.querySelector('.messages-wrapper') || chatbox;
            
            // Create message container
            const messageContainer = document.createElement('div');
            messageContainer.classList.add('message-container', sender);
            
            // Create message wrapper
            const messageWrapper = document.createElement('div');
            messageWrapper.classList.add('message-wrapper');
            
            // Create message header with avatar
            const messageHeader = document.createElement('div');
            messageHeader.classList.add('message-header');
            
            const avatar = document.createElement('div');
            avatar.classList.add('avatar', sender);
            avatar.textContent = sender === 'user' ? 'U' : 'PA';
            
            messageHeader.appendChild(avatar);
            messageHeader.appendChild(document.createTextNode(sender === 'user' ? 'You' : 'Assistant'));
            
            // Create message content
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');
            
            // Handle multi-line messages
            text.split('\n').forEach(line => {
                const p = document.createElement('p');
                p.textContent = line;
                messageDiv.appendChild(p);
            });
            
            // Assemble the message
            messageWrapper.appendChild(messageHeader);
            messageWrapper.appendChild(messageDiv);
            messageContainer.appendChild(messageWrapper);
            messagesWrapper.appendChild(messageContainer);
            
            // Scroll to the latest message
            scrollToBottom();
        }
        
        function addTypingIndicator() {
            const chatbox = document.getElementById('chatbox');
            const messagesWrapper = document.querySelector('.messages-wrapper') || chatbox;
            
            // Create container for typing indicator
            const messageContainer = document.createElement('div');
            messageContainer.classList.add('message-container', 'bot');
            messageContainer.id = 'typingIndicator';
            
            const messageWrapper = document.createElement('div');
            messageWrapper.classList.add('message-wrapper');
            
            // Create header with avatar
            const messageHeader = document.createElement('div');
            messageHeader.classList.add('message-header');
            
            const avatar = document.createElement('div');
            avatar.classList.add('avatar', 'bot');
            avatar.textContent = 'PA';
            
            messageHeader.appendChild(avatar);
            messageHeader.appendChild(document.createTextNode('Assistant'));
            
            // Create typing animation
            const typingDiv = document.createElement('div');
            typingDiv.classList.add('typing');
            
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('span');
                typingDiv.appendChild(dot);
            }
            
            // Assemble the indicator
            messageWrapper.appendChild(messageHeader);
            messageWrapper.appendChild(typingDiv);
            messageContainer.appendChild(messageWrapper);
            messagesWrapper.appendChild(messageContainer);
            
            // Scroll to show typing
            scrollToBottom();
            
            return messageContainer;
        }
        
        function removeTypingIndicator(typingDiv) {
            if (typingDiv) {
                typingDiv.remove();
            }
        }
        
        // Initialize textarea auto-resize
        window.onload = function() {
            const textarea = document.getElementById('userInput');
            textarea.focus();
            autoResize(textarea);
            
            // Initial scroll to bottom
            scrollToBottom();
        };
    </script>
</body>
</html>