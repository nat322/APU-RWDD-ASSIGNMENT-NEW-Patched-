<?php
require 'db.php';

// ✅ Check if a session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Fetch user details from database
try {
    $sql = "SELECT name, email FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    // Extract user details
    $name = $user['name'];
    $email = $user['email'];

    // Generate initials for avatar (e.g., "Yong Han" → "YH")
    $nameParts = explode(" ", $name);
    $initials = "";
    foreach ($nameParts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    // Fetch user's friends with online status
    $friends = [];
    $query = "
        SELECT u.user_id, u.name, 
               CASE 
                   WHEN a.user_id IS NOT NULL THEN 'active'
                   ELSE 'offline'
               END AS status
        FROM friends f
        JOIN users u ON u.user_id = f.friend_id
        LEFT JOIN active_users a ON u.user_id = a.user_id
        WHERE f.user_id = :user_id
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $friends[] = $row;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="logo-icon">
            <path d="M15.07 1.73l-1.79.89a1 1 0 10.89 1.79l1.79-.89a1 1 0 10-.89-1.79zM12 8a1 1 0 00-1 1v4a1 1 0 00.55.89l3 1.5a1 1 0 00.89-1.79L13 12.83V9a1 1 0 00-1-1zm8 4a8 8 0 11-8-8 8 8 0 018 8zm-2 0a6 6 0 10-6 6 6 6 0 006-6z"/>
        </svg>
        Time Leap
    </div>

    <!-- Toggle Button -->
    <div class="sidebar-header">
        <button id="toggle-sidebar-mode" class="toggle-button">Switch View</button>
    </div>

    <!-- Navigation View -->
    <div id="nav-view" class="sidebar-content">
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>
            <a class="nav-item active" href="main.php">
                <i>🏠</i>
                <span class="nav-item-label">Dashboard</span>
            </a>
            <a class="nav-item" href="calendar.php">
                <i>📅</i>
                <span class="nav-item-label">Calendar</span>
            </a>
            <a class="nav-item" href="todolist.php">
                <i>✅</i>
                <span class="nav-item-label">Tasks</span>
                <span class="nav-item-badge">4</span>
            </a>
            <a class="nav-item" href="timer.php">
                <i>⏱️</i>
                <span class="nav-item-label">Timer</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Workspace</div>
            <a class="nav-item" href="analytics.php">
                <i>📊</i>
                <span class="nav-item-label">Analytics</span>
            </a>
            <a class="nav-item" href="teams.php">
                <i>👥</i>
                <span class="nav-item-label">Team</span>
            </a>
            <a class="nav-item" href="friend_chat.php">
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-9 8.4 8.5 8.5 0 0 1-7.1-3.9L3 21l4-1a8.38 8.38 0 0 0 5 1.5 8.5 8.5 0 0 0 8.5-8.5c0-4.8-3.9-8.5-8.5-8.5s-8.5 3.7-8.5 8.5"/>
                    </svg>
                </i>
                <span class="nav-item-label">Chat</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">AI Tools</div>
            <a href="chatbot.php" class="nav-item">
                <i>🤖</i>
                <span class="nav-item-label">AI Assistant</span>
            </a>
            <a href="summarizer.php" class="nav-item">
                <i>📄</i>
                <span class="nav-item-label">Summarizer</span>
            </a>
        </div>
    </div>

    <!-- Friend List View -->
    <div id="friend-view" class="sidebar-content hidden">
        <div class="nav-section">
            <div class="nav-section-title">Friends</div>

            <!-- Friend Search Bar -->
            <div class="friends-search">
                <div class="friends-search-wrapper">
                    <input type="text" placeholder="Search friends..." id="friend-email">
                    <button id="add-friend-btn" class="add-friend-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Friend List -->
            <div id="friend-list">
                <div class="nav-item">Loading friends...</div>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <a href="profile.php" class="user-profile">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($email); ?></div>
            </div>
        </a>
    </div>
</div>
