<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    // Destroy any session that exists and force logout
    session_destroy();
    header("Location: index.php"); // Redirect to login page
    exit;
}

echo "Logged-in user: " . ($_SESSION['username'] ?? 'Session not found');
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Leap - Dashboard</title>
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #111827;
            color: #fff;
            min-height: 100vh;
        }

        /* Dashboard Content */
        .dashboard {
            padding: 2rem;
        }

        .welcome {
            margin-bottom: 2rem;
        }

        .welcome h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome p {
            color: #9ca3af;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: #1f2937;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }

        .stat-card h3 {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.875rem;
            font-weight: bold;
        }

        .blue { color: #3b82f6; }
        .green { color: #10b981; }
        .purple { color: #8b5cf6; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background-color: #1f2937;
            padding: 1.5rem;
            border-radius: 0.5rem;
        }

        .action-card h3 {
            margin-bottom: 1rem;
        }

        .action-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background-color: #374151;
            border: none;
            border-radius: 0.375rem;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .action-button:hover {
            background-color: #4b5563;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background-color: #374151;
            border-radius: 0.375rem;
        }

        .schedule-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .schedule-info h4 {
            margin-bottom: 0.25rem;
        }

        .schedule-info p {
            font-size: 0.875rem;
            color: #9ca3af;
        }

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
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include "sidebar.php" ?>
        <!-- Main Content -->
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

            <div class="dashboard">
                <div class="welcome">
                    <h1>Welcome to Time Leap</h1>
                    <p id="current-date"></p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Today's Focus Score</h3>
                        <div class="value blue">85%</div>
                    </div>
                    <div class="stat-card">
                        <h3>Tasks Completed</h3>
                        <div class="value green">12/15</div>
                    </div>
                    <div class="stat-card">
                        <h3>Time Saved</h3>
                        <div class="value purple">2.5 hrs</div>
                    </div>
                </div>

                <div class="quick-actions">
                    <div class="action-card">
                        <h3>Quick Actions</h3>
                        <button class="action-button">
                            <span>Start Focus Session</span>
                            <span>⏰</span>
                        </button>
                        <button class="action-button">
                            <span>Create New Task</span>
                            <span>+</span>
                        </button>
                        <button class="action-button">
                            <span>View Calendar</span>
                            <span>📅</span>
                        </button>
                    </div>

                    <div class="action-card">
                        <h3>Today's Schedule</h3>
                        <div class="schedule-item">
                            <div class="schedule-dot" style="background-color: #3b82f6;"></div>
                            <div class="schedule-info">
                                <h4>Team Meeting</h4>
                                <p>10:00 AM - 11:00 AM</p>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-dot" style="background-color: #10b981;"></div>
                            <div class="schedule-info">
                                <h4>Focus Session</h4>
                                <p>2:00 PM - 4:00 PM</p>
                            </div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-dot" style="background-color: #8b5cf6;"></div>
                            <div class="schedule-info">
                                <h4>Project Review</h4>
                                <p>4:30 PM - 5:30 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const sidebar = document.querySelector('.sidebar'); // ✅ class selector
            const mainContent = document.querySelector('.main-content');
            const menuIcon = document.querySelector('.menu-icon'); // ✅ Added reference for menu icon
            let isOpen = false;
        
            const openSidebar = () => {
                isOpen = true;
                sidebar.classList.add('open');
                mainContent.style.marginLeft = '220px'; // ✅ Shift main content
            };
        
            const closeSidebar = () => {
                isOpen = false;
                sidebar.classList.remove('open');
                mainContent.style.marginLeft = '0'; // ✅ Reset to full width
            };
        
            const isMobileView = () => window.innerWidth <= 768;
        
            // ✅ Desktop: Sidebar opens when mouse is near the left side
            document.addEventListener('mousemove', (event) => {
                if (!isMobileView()) {
                    if (event.clientX < 50) {
                        openSidebar();
                    } else if (event.clientX > 250 && isOpen) {
                        closeSidebar();
                    }
                }
            });
        
            // ✅ Mobile: Sidebar opens when clicking menu button
            if (menuIcon) {
                menuIcon.addEventListener('click', () => {
                    if (sidebar.classList.contains('open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }
        
            // ✅ Mobile: Sidebar closes when clicking outside
            document.addEventListener('click', (event) => {
                if (isMobileView()) {
                    if (!event.target.closest('.sidebar') && !event.target.closest('.menu-icon')) {
                        closeSidebar();
                    }
                }
            });
        
            // ✅ Ensure sidebar updates when screen resizes
            const handleResize = () => {
                if (isMobileView()) {
                    closeSidebar(); // Hide sidebar on mobile by default
                } else {
                    closeSidebar(); // Reset sidebar state on desktop
                }
            };
        
            window.addEventListener('resize', handleResize);
            handleResize(); // Run once on load to apply correct state
        
            // ✅ Display current date in the welcome message
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                dateElement.textContent = new Date().toLocaleDateString('en-US', options);
            }
        });        
    </script>    
</body>
</html>