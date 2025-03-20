<?php
// analytics.php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user_id']; // Replace with actual user_id from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
    <style>
        :root {
            --primary: #1f2937;
            --primary-light: #374151;
            --primary-dark: #111827;
            --accent: #3b82f6;
            --accent-light: #60a5fa;
            --text-light: #f3f4f6;
            --text-dark: #1f2937;
            --bg-light: #f9fafb;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .analytics-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        header {
            background-color: var(--primary);
            color: var(--text-light);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background-color: var(--accent-light);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
        }

        .btn-outline:hover {
            background-color: var(--accent);
            color: white;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .card h2, .card h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card h2 {
            font-size: 1.25rem;
        }

        .card h3 {
            font-size: 1.1rem;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            padding: 1rem;
            background-color: var(--bg-light);
            border-radius: 0.5rem;
            text-align: center;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--primary-light);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }

        .insights {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: var(--primary-light);
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
        }

        .tag {
            display: inline-block;
            background-color: var(--accent-light);
            color: var(--text-light);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: var(--accent);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .icon {
            color: var(--accent);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .header-actions {
                margin-top: 1rem;
                width: 100%;
                justify-content: center;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 1rem;
            }

            .stat-grid {
                gap: 0.75rem;
            }

            .btn {
                padding: 0.4rem 0.75rem;
                font-size: 0.85rem;
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

            <div class="analytics-content">
                <header>
                    <div class="analytics-container">
                        <div class="header-content">
                            <div>
                                <h1 class="header-title">Productivity Analytics</h1>
                                <p class="header-subtitle">Track your performance and improve productivity</p>
                            </div>
                            <div class="header-actions">
                                <button class="btn btn-outline" onclick="fetchAnalytics()">
                                    <i class="fas fa-sync-alt"></i> Refresh Data
                                </button>
                                <a href="main.php" class="btn">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

            <div class="analytics-container">
                <div class="card">
                    <h2><i class="fas fa-chart-pie icon"></i> Summary</h2>
                    <div class="stat-grid">
                        <div class="stat-item">
                            <div class="stat-label">Tasks Completed</div>
                            <div class="stat-value" id="total_tasks_completed">0</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Total Time Spent</div>
                            <div class="stat-value"><span id="total_time_spent">0</span> hrs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Most Active Day</div>
                            <div class="stat-value" id="most_active_day">N/A</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Total Overdue</div>
                            <div class="stat-value" id="total_overdue_tasks">N/A</div>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="card">
                        <h3><i class="fas fa-tasks icon"></i> Tasks Completion per Day</h3>
                        <div class="chart-container" id="tasks-container">
                            <canvas id="tasksByDateChart"></canvas>
                        </div>
                        <div class="insights">
                            <span class="tag">Insight</span> Your task completion rate shows how consistently you complete tasks over time.
                        </div>
                    </div>
                    <div class="card">
                        <h3><i class="fas fa-clock icon"></i> Time Spent per Task</h3>
                        <div class="chart-container" id="time-container">
                            <canvas id="timePerTaskChart"></canvas>
                        </div>
                        <div class="insights">
                            <span class="tag">Insight</span> This chart reveals which tasks are taking up most of your time.
                        </div>
                    </div>
                    <div class="card">
                        <h3><i class="fas fa-calendar icon"></i> Priority Overview</h3>
                        <div class="chart-container" id="priority-container">
                            <canvas id="completedByPriorityChart"></canvas>
                        </div>
                        <div class="insights">
                            <span class="tag">Insight</span> This chart shows how priority is assigned to your tasks.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    const userId = '<?php echo $user_id; ?>';
    let tasksChart = null;
    let timeChart = null;
    let isLoading = true;

    function showLoading() {
        const chartContainers = document.querySelectorAll('.chart-container');
        chartContainers.forEach(container => {
            container.innerHTML = '<div class="loading"><div class="loading-spinner"></div></div>';
        });
    }

    // Update the resetCharts function
    function resetCharts() {
        // Define all chart containers and their corresponding canvas IDs
        const chartContainers = [
            { container: 'tasks-container', canvas: 'tasksByDateChart' },
            { container: 'time-container', canvas: 'timePerTaskChart' },
            { container: 'priority-container', canvas: 'completedByPriorityChart' },
        ];
        
        // Reset each canvas
        chartContainers.forEach(({ container, canvas }) => {
            const containerElement = document.getElementById(container);
            if (containerElement) {
                // Clear container and create new canvas
                containerElement.innerHTML = '';
                const newCanvas = document.createElement('canvas');
                newCanvas.id = canvas;
                containerElement.appendChild(newCanvas);
            } else {
                console.warn(`Container not found: #${container}`);
            }
        });
    }

    // Update the fetchAnalytics function to include proper error handling
    function fetchAnalytics() {
        showLoading();
        isLoading = true;

        fetch('get_analytics_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + encodeURIComponent(userId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            
            // Update summary stats
            document.getElementById('total_tasks_completed').innerText = data.total_tasks_completed || 0;
            document.getElementById('total_time_spent').innerText = ((data.total_time_spent || 0) / 3600).toFixed(2);
            document.getElementById('most_active_day').innerText = data.most_active_day || 'N/A';
            document.getElementById('total_overdue_tasks').innerText = data.total_overdue_tasks || 'N/A';

            // Reset and render charts
            resetCharts();
            renderTasksChart(data.tasks_by_date);
            renderTimeChart(data.time_per_task);
            renderPriorityChart(data.completed_tasks_by_priority);
            
            // Update insights
            updateInsights(data);
        })
        .catch(error => {
            console.error('Error fetching analytics:', error);
            alert('Error loading analytics data. Please try again.');
        })
        .finally(() => {
            isLoading = false;
        });
    }

    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        if (!obj) {
            console.error(`Element with ID '${id}' not found`);
            return;
        }
        
        const range = end - start;
        const minTimer = 50;
        let stepTime = Math.abs(Math.floor(duration / range));
        
        stepTime = Math.max(stepTime, minTimer);
        
        const startTime = new Date().getTime();
        const endTime = startTime + duration;
        let timer;
        
        function run() {
            const now = new Date().getTime();
            const remaining = Math.max((endTime - now) / duration, 0);
            const value = Math.round(end - (remaining * range));
            obj.innerHTML = value;
            if (value === end) {
                clearInterval(timer);
            }
        }
        
        timer = setInterval(run, stepTime);
        run();
    }

    function renderPriorityChart(priorityData) {
        const ctx = document.getElementById('completedByPriorityChart');
        if (!ctx) {
            console.error('Priority chart canvas not found');
            return;
        }
        
        if (!priorityData || !Array.isArray(priorityData) || priorityData.length === 0) {
            console.warn('No priority data available');
            return;
        }
        
        // Process data
        const labels = priorityData.map(item => {
            switch(item.priority) {
                case '1': return 'Low';
                case '2': return 'Medium';
                case '3': return 'High';
                default: return item.priority;
            }
        });
        const data = priorityData.map(item => parseInt(item.completed_tasks) || 0);
        const colors = ['#60a5fa', '#f59e0b', '#ef4444'];
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${value} tasks (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function renderTasksChart(tasksByDate) {
        const ctx = document.getElementById('tasksByDateChart');
        if (!ctx) {
            console.error('Tasks chart canvas not found');
            return;
        }

        if (!tasksByDate || !Array.isArray(tasksByDate) || tasksByDate.length === 0) {
            console.warn('No tasks data available');
            return;
        }

        // Process data
        const labels = tasksByDate.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const data = tasksByDate.map(item => parseInt(item.completed_tasks) || 0);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tasks Completed',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1f2937',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    function renderTimeChart(timePerTask) {
        const ctx = document.getElementById('timePerTaskChart');
        if (!ctx) {
            console.error('Time chart canvas not found');
            return;
        }

        if (!timePerTask || !Array.isArray(timePerTask) || timePerTask.length === 0) {
            console.warn('No time data available');
            return;
        }

        // Process data
        const labels = timePerTask.map(item => item.task_title);
        const data = timePerTask.map(item => (parseFloat(item.total_time) / 3600).toFixed(2));

        // Create gradient
        const ctx2d = ctx.getContext('2d');
        const gradient = ctx2d.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hours Spent',
                    data: data,
                    backgroundColor: gradient,
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${parseFloat(context.raw).toFixed(2)} hours`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function(value) {
                                const label = this.getLabelForValue(value);
                                return label.length > 15 ? label.substr(0, 15) + '...' : label;
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    }

    // Dynamic insights based on data
    function updateInsights(data) {
        if (!data) return;
        
        const taskInsightEl = document.querySelector('.charts-grid .card:nth-child(1) .insights');
        const timeInsightEl = document.querySelector('.charts-grid .card:nth-child(2) .insights');
        const priorityInsightEl = document.querySelector('.charts-grid .card:nth-child(3) .insights');
        const timeSpentInsightEl = document.querySelector('.charts-grid .card:nth-child(4) .insights');
        
        if (!taskInsightEl || !timeInsightEl || !priorityInsightEl || !timeSpentInsightEl) {
            console.warn('One or more insight elements not found');
            return;
        }
        
        // Task completion insights
        if (data.tasks_by_date && data.tasks_by_date.length > 1) {
            const lastTwoWeeks = data.tasks_by_date.slice(-2);
            const currentWeek = parseInt(lastTwoWeeks[1]?.completed_tasks) || 0;
            const prevWeek = parseInt(lastTwoWeeks[0]?.completed_tasks) || 0;
            
            if (currentWeek > prevWeek) {
                taskInsightEl.innerHTML = `<span class="tag">Progress</span> You completed ${currentWeek - prevWeek} more tasks this week compared to last week!`;
            } else if (currentWeek < prevWeek) {
                taskInsightEl.innerHTML = `<span class="tag">Insight</span> You completed ${prevWeek - currentWeek} fewer tasks this week compared to last week.`;
            } else {
                taskInsightEl.innerHTML = `<span class="tag">Insight</span> Your task completion rate has been consistent.`;
            }
        }
        
        // Time spent insights
        if (data.time_per_task && data.time_per_task.length > 0) {
            const sortedTasks = [...data.time_per_task].sort((a, b) => b.total_time - a.total_time);
            const topTask = sortedTasks[0];
            
            if (topTask) {
                const timeSpentHrs = (parseFloat(topTask.total_time) / 3600).toFixed(1);
                timeInsightEl.innerHTML = `<span class="tag">Time Focus</span> "${topTask.task_title}" is taking most of your time (${timeSpentHrs} hrs).`;
            }
        }
        
        // Priority insights
        if (data.completed_tasks_by_priority && data.completed_tasks_by_priority.length > 0) {
            const highPriorityTasks = data.completed_tasks_by_priority.find(item => item.priority === '3');
            const highPriorityCount = highPriorityTasks ? parseInt(highPriorityTasks.completed_tasks) : 0;
            
            if (highPriorityCount > 0) {
                priorityInsightEl.innerHTML = `<span class="tag">Priority</span> You've completed ${highPriorityCount} high-priority tasks. Great job focusing on important work!`;
            } else {
                priorityInsightEl.innerHTML = `<span class="tag">Insight</span> Consider focusing on high-priority tasks to increase productivity.`;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchAnalytics();
        
        // Refresh data every 5 minutes
        setInterval(fetchAnalytics, 300000);
    });
    
    // Handle window resize for responsive charts
    window.addEventListener('resize', function() {
        if (!isLoading && tasksChart && timeChart) {
            tasksChart.resize();
            timeChart.resize();
        }
    });
    </script>
</body>
</html>