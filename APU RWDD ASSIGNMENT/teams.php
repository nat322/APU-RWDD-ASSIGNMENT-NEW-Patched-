<?php
session_start();
if (isset($_POST['team_id'])) {
    $_SESSION['team_id'] = $_POST['team_id']; // Store team_id in session
    echo json_encode(["success" => true]);
    exit;
}
echo json_encode(["error" => "No team ID provided."]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="notification.css">
    <style>
        :root {
            --primary-bg: #111827;
            --secondary-bg: #1e2837;
            --card-bg: #1f2937;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
            --border-color: #374151;
            --hover-bg: #2d3748;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
        }
        
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
        
        .page-content {
            padding: 20px;
            width: 100%;
        }
        
        .header {
            background-color: var(--secondary-bg);
            box-shadow: var(--box-shadow);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: var(--text-primary);
            font-size: 28px;
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        
        .card-header h3 {
            color: var(--text-primary);
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .main-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .input-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            background-color: var(--secondary-bg);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--accent-blue);
            color: var(--text-primary);
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-accent {
            background-color: var(--accent-purple);
            color: var(--text-primary);
        }
        
        .btn-accent:hover {
            background-color: #7c3aed;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .btn-outline:hover {
            background-color: var(--hover-bg);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--accent-green);
            color: var(--text-primary);
        }
        
        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
        }
        
        .result-message {
            padding: 10px;
            border-radius: var(--border-radius);
            margin-top: 10px;
            font-size: 14px;
            display: none;
        }
        
        .result-message.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.2);
            display: block;
        }
        
        .result-message.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: block;
        }
        
        .team-list {
            list-style: none;
        }
        
        .team-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background-color: var(--secondary-bg);
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            transition: var(--transition);
        }
        
        .team-item:hover {
            background-color: var(--hover-bg);
            transform: translateX(5px);
        }
        
        .team-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .team-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--accent-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            font-weight: bold;
        }
        
        .team-details h4 {
            font-size: 16px;
            margin-bottom: 3px;
            color: var(--text-primary);
        }
        
        .team-details p {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .team-actions {
            display: flex;
            gap: 8px;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 48px;
            color: var(--border-color);
            margin-bottom: 15px;
        }
        
        .empty-state h4 {
            font-size: 18px;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: rgba(59, 130, 246, 0.2);
            color: var(--accent-blue);
        }
        
        .badge-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: var(--accent-green);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 640px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--box-shadow);
        }
        
        .stat-title {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
        }
        
        .stat-value.blue {
            color: var(--accent-blue);
        }
        
        .stat-value.green {
            color: var(--accent-green);
        }
        
        .stat-value.purple {
            color: var(--accent-purple);
        }
        
        .pagesearch-bar {
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .pagesearch-bar input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            background-color: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: var(--text-primary);
            font-size: 16px;
        }
        
        .pagesearch-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .pagesearch-bar input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .schedule-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--secondary-bg);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .schedule-item:last-child {
            margin-bottom: 0;
        }
        
        .schedule-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        
        .schedule-dot.blue {
            background-color: var(--accent-blue);
        }
        
        .schedule-dot.green {
            background-color: var(--accent-green);
        }
        
        .schedule-dot.purple {
            background-color: var(--accent-purple);
        }
        
        .schedule-details h4 {
            font-size: 16px;
            margin-bottom: 3px;
        }
        
        .schedule-details p {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .team-members-list {
            margin-top: 15px;
            list-style: none;
        }
        
        .team-members-list li {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            margin-bottom: 5px;
            background-color: var(--secondary-bg);
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
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
</head>
<body>
    <div class="container">
        <?php include "sidebar.php"; ?>
        
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
            
            <div class="page-content">
                <div class="header">
                    <h1>Team Management</h1>
                    <div class="header-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-cog"></i>
                            Settings
                        </button>
                    </div>
                </div>
                
                <div class="pagesearch-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search teams...">
                </div>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-title">Team Count</div>
                        <div class="stat-value blue" id="team-count-display">0</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Active Members</div>
                        <div class="stat-value green">12/15</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Time Saved</div>
                        <div class="stat-value purple">2.5 hrs</div>
                    </div>
                </div>
                
                <div class="dashboard-container">
                    <div class="sidebar-panel">
                        <!-- Create Team Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Create a New Team</h3>
                            </div>
                            <div class="input-group">
                                <label for="team-name">Team Name</label>
                                <input type="text" id="team-name" placeholder="Enter Team Name">
                            </div>
                            <button class="btn btn-primary" onclick="createTeam()" style="width: 100%;">
                                <i class="fas fa-plus"></i>
                                Create Team
                            </button>
                            <div id="create-result" class="result-message"></div>
                        </div>
                        
                        <!-- Join Team Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Join a Team</h3>
                            </div>
                            <div class="input-group">
                                <label for="team-code">Invite Code</label>
                                <input type="text" id="team-code" placeholder="Enter Team Code">
                            </div>
                            <button class="btn btn-accent" onclick="joinTeam()" style="width: 100%;">
                                <i class="fas fa-sign-in-alt"></i>
                                Join Team
                            </button>
                            <div id="join-result" class="result-message"></div>
                        </div>
                        
                        <!-- Quick Actions Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <button class="btn btn-outline" style="width: 100%; margin-bottom: 10px;">
                                <i class="fas fa-video"></i>
                                Start Meeting
                            </button>
                            <button class="btn btn-outline" style="width: 100%; margin-bottom: 10px;">
                                <i class="fas fa-tasks"></i>
                                Create Task
                            </button>
                            <button class="btn btn-outline" style="width: 100%;">
                                <i class="fas fa-calendar"></i>
                                View Calendar
                            </button>
                        </div>
                    </div>
                    
                    <div class="main-panel">
                        <!-- Team List Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Your Teams</h3>
                                <div class="badge badge-primary">
                                    <span id="team-count">0</span> Teams
                                </div>
                            </div>
                            
                            <ul id="team-list" class="team-list"></ul>
                            
                            <div id="empty-teams" class="empty-state">
                                <i class="fas fa-users"></i>
                                <h4>No Teams Yet</h4>
                                <p>Create a new team or join an existing one to get started</p>
                                <button class="btn btn-primary" onclick="document.getElementById('team-name').focus()">
                                    <i class="fas fa-plus"></i>
                                    Create Your First Team
                                </button>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Team Members</h3>
                            </div>
                            <div id="team-members-container">
                                <p id="select-team-message">Select a team to view its members</p>
                                <ul id="team-members" class="team-members-list"></ul>
                            </div>
                        </div>
                        
                        <!-- Today's Schedule Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Today's Schedule</h3>
                            </div>
                            
                            <div class="schedule-item">
                                <div class="schedule-dot blue"></div>
                                <div class="schedule-details">
                                    <h4>Team Meeting</h4>
                                    <p>10:00 AM - 11:00 AM</p>
                                </div>
                            </div>
                            
                            <div class="schedule-item">
                                <div class="schedule-dot green"></div>
                                <div class="schedule-details">
                                    <h4>Focus Session</h4>
                                    <p>2:00 PM - 4:00 PM</p>
                                </div>
                            </div>
                            
                            <div class="schedule-item">
                                <div class="schedule-dot purple"></div>
                                <div class="schedule-details">
                                    <h4>Project Review</h4>
                                    <p>4:30 PM - 5:30 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store the currently selected team ID
        let currentSelectedTeam = null;
        
        function createTeam() {
            let teamName = document.getElementById("team-name").value;
            if (teamName.trim() === "") {
                showResult("create-result", "Please enter a team name", false);
                return;
            }

            // Show loading state
            document.getElementById("create-result").className = "result-message";
            document.getElementById("create-result").innerText = "Creating team...";
            document.getElementById("create-result").style.display = "block";

            fetch("create_team.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "team_name=" + encodeURIComponent(teamName)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult("create-result", "Team created successfully! Invite Code: " + data.invite_code, true);
                    document.getElementById("team-name").value = "";
                    fetchTeams(); // Refresh the team list
                } else {
                    showResult("create-result", "Error: " + data.error, false);
                }
            })
            .catch(error => {
                showResult("create-result", "Network error. Please try again later.", false);
                console.error("Error:", error);
            });
        }

        function joinTeam() {
            let teamCode = document.getElementById("team-code").value.trim();
            if (teamCode === "") {
                showResult("join-result", "Please enter a team code", false);
                return;
            }

            // Show loading state
            document.getElementById("join-result").className = "result-message";
            document.getElementById("join-result").innerText = "Joining team...";
            document.getElementById("join-result").style.display = "block";

            fetch("join_team.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "team_code=" + encodeURIComponent(teamCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult("join-result", "Joined team successfully!", true);
                    document.getElementById("team-code").value = "";
                    fetchTeams();
                } else {
                    showResult("join-result", "Error: " + data.error, false);
                }
            })
            .catch(error => {
                showResult("join-result", "Network error. Please try again later.", false);
                console.error("Error:", error);
            });
        }

        async function fetchTeams() {
            try {
                const response = await fetch("get_teams.php");
                const data = await response.json();

                const teamList = document.getElementById("team-list");
                const teamCount = document.getElementById("team-count");
                const teamCountDisplay = document.getElementById("team-count-display");
                const emptyTeams = document.getElementById("empty-teams");

                // Update team count
                teamCount.innerText = data.length;
                teamCountDisplay.innerText = data.length;

                // Show/hide empty state
                if (data.length === 0) {
                    emptyTeams.style.display = "flex";
                    teamList.style.display = "none";
                } else {
                    emptyTeams.style.display = "none";
                    teamList.style.display = "block";
                    teamList.innerHTML = ""; // Clear old data

                    data.forEach(team => {
                        const initials = getInitials(team.team_name);
                        const li = document.createElement("li");
                        li.className = "team-item";
                        li.dataset.teamId = team.team_id;
                        li.innerHTML = `
                            <div class="team-info">
                                <div class="team-avatar">${initials}</div>
                                <div class="team-details">
                                    <h4>${team.team_name}</h4>
                                    <p>Team ID: ${team.team_id}</p>
                                </div>
                            </div>
                            <div class="team-actions">
                                <button class="btn btn-outline" onclick="redirectToChat(${team.team_id})">
                                    <i class="fas fa-comments"></i> Chat
                                </button>
                                <button class="btn btn-success" onclick="selectTeam(${team.team_id})">
                                    <i class="fas fa-users"></i> View
                                </button>
                            </div>
                        `;
                        teamList.appendChild(li);

                        // Add click event to select the team
                        li.addEventListener('click', function(e) {
                            if (!e.target.closest('button')) {
                                selectTeam(team.team_id);
                            }
                        });
                    });
                }

                // Retrieve selected team ID from localStorage
                const storedTeamId = localStorage.getItem("selectedTeamId");
                if (storedTeamId && storedTeamId !== "null") {
                    currentSelectedTeam = parseInt(storedTeamId);
                    highlightSelectedTeam(currentSelectedTeam);
                    fetchTeamMembers(currentSelectedTeam);
                }

            } catch (error) {
                console.error("Error fetching teams:", error);
            }
        }

        function redirectToChat(teamId) {
            if (!teamId) {
                console.error("No team ID provided.");
                return;
            }

            // Store the selected team ID
            selectTeam(teamId);

            // Redirect to chatroom page
            window.location.href = "chat.php?team_id=" + encodeURIComponent(teamId);
        }

        function selectTeam(teamId) {
            if (!teamId) {
                console.error("No team ID provided.");
                return;
            }

            // Store selected team ID in localStorage
            localStorage.setItem("selectedTeamId", teamId);
            currentSelectedTeam = teamId; // Update global variable

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "set_team.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                try {
                    let response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log("Team successfully selected:", teamId);
                        fetchTeamMembers(teamId);
                        highlightSelectedTeam(teamId); // Highlight selected team in UI
                    } else {
                        console.error("Error selecting team:", response.error);
                    }
                } catch (error) {
                    console.error("Invalid JSON response:", xhr.responseText);
                }
            };

            xhr.onerror = function () {
                console.error("Request failed.");
            };

            xhr.send("team_id=" + encodeURIComponent(teamId));
        }

        function highlightSelectedTeam(teamId) {
            document.querySelectorAll('.team-item').forEach(item => {
                item.style.borderLeft = 'none';
                item.style.backgroundColor = ''; // Reset all items
            });

            const selectedTeam = document.querySelector(`.team-item[data-team-id="${teamId}"]`);
            if (selectedTeam) {
                selectedTeam.style.borderLeft = '3px solid var(--accent-blue)';
                selectedTeam.style.backgroundColor = 'var(--hover-bg)';
            }
        }
        
        function viewTeam(teamCode) {
            selectTeam(teamCode);
        }

        function fetchTeamMembers(teamId) {
            const membersContainer = document.getElementById("team-members-container");
            const membersList = document.getElementById("team-members");
            const selectMessage = document.getElementById("select-team-message");

            membersList.innerHTML = "<li>Loading members...</li>";
            selectMessage.style.display = "none";

            fetch("get_team_members.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "team_id=" + encodeURIComponent(teamId)
            })
            .then(response => response.json())
            .then(data => {
                membersList.innerHTML = "";

                if (data.error) {
                    membersList.innerHTML = `<li>${data.error}</li>`;
                    return;
                }

                data.forEach(member => {
                    let li = document.createElement("li");
                    li.textContent = member.name;
                    membersList.appendChild(li);
                });
            })
            .catch(error => {
                membersList.innerHTML = "<li>Error loading members</li>";
                console.error("Error:", error);
            });
        }

        function showResult(elementId, message, isSuccess) {
            let element = document.getElementById(elementId);
            element.innerText = message;
            element.className = isSuccess ? "result-message success" : "result-message error";
            element.style.display = "block";
            
            // Hide after 5 seconds
            setTimeout(() => {
                element.style.display = "none";
            }, 5000);
        }

        function getInitials(name) {
            if (!name) return "TM";
            return name.split(' ')
                .map(word => word.charAt(0))
                .join('')
                .substring(0, 2)
                .toUpperCase();
        }

        // Initialize team list when page loads
        document.addEventListener('DOMContentLoaded', function () {
            fetchTeams().then(() => {
                // Retrieve selected team ID from localStorage
                const storedTeamId = localStorage.getItem("selectedTeamId");

                // Only select the team if the user has previously chosen one
                if (storedTeamId && storedTeamId !== "null") {
                    console.log("Restoring previous team selection:", storedTeamId);
                    highlightSelectedTeam(storedTeamId); // Highlight in UI
                    fetchTeamMembers(storedTeamId); // Load members
                } else {
                    console.log("No team previously selected.");
                    document.getElementById("select-team-message").style.display = "block";
                    document.getElementById("team-members").innerHTML = "";
                }
            });
        });
    </script>
</body>
</html>