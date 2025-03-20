<?php
require 'db.php';

session_start();
$user_id = $_SESSION['user_id'];

include 'archive_overdue_tasks.php';

archive_overdue_tasks($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Task Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js"></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
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
        
        .pixel-container {
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
        
        .add-group-btn {
            background: var(--surface-2);
            color: var(--fg);
            border: 1px solid var(--surface-3);
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s var(--ease-out);
            z-index: 1;
        }
        
        .add-group-btn:hover {
            background: var(--surface-3);
        }
        
        .groups-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .group {
            background: var(--surface-1);
            border-radius: 0.5rem;
            border: 1px solid var(--surface-2);
            overflow: hidden;
            transition: border-color 200ms var(--ease-out);
            position: relative;
        }
        
        .group:hover {
            border-color: var(--fg);
            transition: border-color 800ms var(--ease-in-out);
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--surface-2);
            position: relative;
        }
        
        .group-header h3 {
            margin: 0;
            font-size: 1.2rem;
            flex-grow: 1;
        }
        
        .group-header button {
            background: transparent;
            color: var(--fg);
            border: none;
            font-size: 0.9rem;
            cursor: pointer;
            margin-left: 0.5rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
            z-index: 2; /* Add this line */
        }
        
        .group-header button:hover {
            background: var(--surface-3);
        }
        
        .delete-group {
            color: var(--accent-pink-light) !important;
        }
        
        .tasks {
            padding: 1rem;
            min-height: 100px;
        }
        
        .task {
            background: var(--surface-2);
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-blue);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .task:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .task h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
        }
        
        .task p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: var(--fg);
            opacity: 0.8;
        }
        
        .task-priority {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        
        .priority-low {
            background: rgba(14, 165, 233, 0.2);
            color: var (--accent-blue-light);
        }
        
        .priority-medium {
            background: rgba(234, 179, 8, 0.2);
            color: var(--accent-yellow-light);
        }
        
        .priority-high {
            background: rgba(225, 29, 72, 0.2);
            color: var(--accent-pink-light);
        }
        
        .task-actions {
            margin-top: 0.75rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .task-actions button {
            background: transparent;
            color: var(--fg);
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .task-actions button:hover {
            opacity: 1;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: var(--surface-1);
            margin: 10% auto;
            padding: 2rem;
            border: 1px solid var(--surface-3);
            border-radius: 0.5rem;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            color: var(--fg);
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--accent-pink-light);
        }
        
        .modal h2 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .modal input, .modal textarea, .modal select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            background: var(--surface-2);
            border: 1px solid var(--surface-3);
            border-radius: 0.25rem;
            color: var(--fg);
            font-family: var(--font-sans);
        }
        
        .modal textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal button {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        
        .modal button:hover {
            background: var(--accent-blue-light);
            color: var(--surface-1);
        }
        
        .gu-mirror {
            background: var(--surface-2) !important;
            border-radius: 0.375rem !important;
            padding: 1rem !important;
            border-left: 4px solid var(--accent-blue) !important;
            opacity: 0.8 !important;
        }
        
        .gu-hide {
            display: none !important;
        }
        
        .gu-unselectable {
            user-select: none !important;
        }
        
        .gu-transit {
            opacity: 0.4 !important;
            border: 2px dashed var(--accent-blue) !important;
        }

        /* task */
        .task-completed {
            opacity: 0.7;
            background: var(--surface-1);
            border-left-style: dashed !important;
        }

        .task-completed h4 {
            text-decoration: line-through;
        }

        .complete-btn {
            background: transparent;
            color: var(--fg);
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            opacity: 0.7;
            transition: opacity 0.2s, background-color 0.2s;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
        }

        .complete-btn:hover {
            opacity: 1;
            background: var(--surface-3);
        }

        .complete-btn.completed {
            color: var(--accent-blue-light);
            background: rgba(14, 165, 233, 0.1);
        }

        .complete-btn.completed:hover {
            background: rgba(14, 165, 233, 0.2);
        }

        /* Archive view styles */
        .archive-view {
            display: none;
            padding: var(--space);
            height: auto;
            min-height: 100vh;
            overflow-y: auto;
        }

        .archive-header {
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

        .archive-header h1 {
            font-size: 1.8rem;
            z-index: 1;
        }

        .archive-container {
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 2rem; /* Add some bottom padding for better scrolling */
        }

        .archive-date-section {
            margin-bottom: 2rem;
        }

        .archive-date {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid var(--surface-2);
            margin-bottom: 1rem;
            color: var(--fg);
        }

        .archived-task {
            opacity: 0.85;
            transition: opacity 0.2s ease-out;
        }

        .archived-task:hover {
            opacity: 1;
        }

        .restore-btn {
            background: rgba(14, 165, 233, 0.1);
            color: var(--accent-blue-light);
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
            margin-right: 0.5rem;
        }

        .restore-btn:hover {
            background: rgba(14, 165, 233, 0.2);
        }

        /* Archive button styles */
        .archive-button-container {
            position: relative;
            margin-left: 1rem;
            z-index: 1;
        }

        #archive-button {
            background: var(--surface-2);
            color: var(--fg);
            border: 1px solid var(--surface-3);
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s var(--ease-out);
            display: flex;
            align-items: center;
        }

        #archive-button:hover {
            background: var(--surface-3);
        }

        #archive-count {
            display: none;
            justify-content: center;
            align-items: center;
            width: 22px;
            height: 22px;
            background: var(--accent-blue);
            color: white;
            border-radius: 50%;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }

        .task-badges {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .task-badge {
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 12px;
            color: #fff;
            display: inline-block;
        }

        .overdue-badge {
            background-color: #e63946; /* Red */
        }

        .archive-reason-badge {
            background-color: #457b9d; /* Blue-gray */
        }

        .task-priority {
            margin-top: 8px;
            font-size: 14px;
        }

        .priority-low {
            color: var(--accent-blue);
        }

        .priority-medium {
            color: var(--accent-yellow);
        }

        .priority-high {
            color: var(--accent-pink);
        }

        /* Toast notification styles */
        #toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: var(--surface-1);
            color: var(--fg);
            border-left: 4px solid var(--accent-blue);
            border-radius: 4px;
            padding: 0.75rem 1rem;
            margin-top: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            max-width: 300px;
        }
        
        /* Animation for empty state */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--fg);
            opacity: 0.7;
        }
        
        @media (max-width: 768px) {
            .groups-container {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
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

            <div class="pixel-container">
                <div class="header">
                    <h1>Pixel Task Manager</h1>
                    <div style="display: flex; z-index: 1;">
                        <button class="add-group-btn" onclick="showAddGroupModal()">Add Group</button>
                        <div class="archive-button-container">
                            <button id="archive-button" onclick="toggleArchiveView()">
                                View Archive
                                <span id="archive-count">0</span>
                            </button>
                        </div>
                    </div>
                    <pixel-canvas data-gap="10" data-speed="25" data-colors="#e0f2fe, #7dd3fc, #0ea5e9"></pixel-canvas>
                </div>

                <div id="active-view">
                    <div id="groups-container" class="groups-container"></div>
                </div>
                <div id="archive-view" class="archive-view">
                    <div class="archive-header">
                        <h1>Task Archive</h1>
                        <pixel-canvas data-gap="10" data-speed="25" data-colors="#fecdd3, #fda4af, #e11d48"></pixel-canvas>
                    </div>
                    <div id="archive-container" class="archive-container"></div>
                </div>
            </div>

            <!-- Add Group Modal -->
            <div id="addGroupModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAddGroupModal()">&times;</span>
                    <h2>Add New Group</h2>
                    <input type="text" id="groupName" placeholder="Group Name">
                    <button onclick="addGroup()">Create Group</button>
                </div>
            </div>

            <!-- Add Task Modal -->
            <div id="addTaskModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAddTaskModal()">&times;</span>
                    <h2>Add New Task</h2>
                    <input type="hidden" id="taskGroupId">
                    <input type="text" id="taskTitle" placeholder="Task Title">
                    <textarea id="taskDescription" placeholder="Task Description"></textarea>
                    <input type="date" id="taskDueDate">
                    <select id="taskPriority">
                        <option value="low">Low Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="high">High Priority</option>
                    </select>
                    <button onclick="addTask()">Create Task</button>
                </div>
            </div>

            <!-- Custom Confirmation Dialog -->
            <div id="confirmDialog" class="modal">
                <div class="modal-content" style="max-width: 400px;">
                    <h2 id="confirmTitle">Confirm Action</h2>
                    <p id="confirmMessage">Are you sure you want to proceed?</p>
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem;">
                        <button id="cancelButton" style="background: var(--surface-2); color: var(--fg);">Cancel</button>
                        <button id="confirmButton" style="background: var(--accent-pink);">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PixelCanvas Component
        class Pixel {
            constructor(canvas, context, x, y, color, speed, delay) {
                this.width = canvas.width;
                this.height = canvas.height;
                this.ctx = context;
                this.x = x;
                this.y = y;
                this.color = color;
                this.speed = this.getRandomValue(0.1, 0.9) * speed;
                this.size = 0;
                this.sizeStep = Math.random() * 0.4;
                this.minSize = 0.5;
                this.maxSizeInteger = 2;
                this.maxSize = this.getRandomValue(this.minSize, this.maxSizeInteger);
                this.delay = delay;
                this.counter = 0;
                this.counterStep = Math.random() * 4 + (this.width + this.height) * 0.01;
                this.isIdle = false;
                this.isReverse = false;
                this.isShimmer = false;
            }

            getRandomValue(min, max) {
                return Math.random() * (max - min) + min;
            }

            draw() {
                const centerOffset = this.maxSizeInteger * 0.5 - this.size * 0.5;

                this.ctx.fillStyle = this.color;
                this.ctx.fillRect(
                    this.x + centerOffset,
                    this.y + centerOffset,
                    this.size,
                    this.size
                );
            }

            appear() {
                this.isIdle = false;

                if (this.counter <= this.delay) {
                    this.counter += this.counterStep;
                    return;
                }

                if (this.size >= this.maxSize) {
                    this.isShimmer = true;
                }

                if (this.isShimmer) {
                    this.shimmer();
                } else {
                    this.size += this.sizeStep;
                }

                this.draw();
            }

            disappear() {
                this.isShimmer = false;
                this.counter = 0;

                if (this.size <= 0) {
                    this.isIdle = true;
                    return;
                } else {
                    this.size -= 0.1;
                }

                this.draw();
            }

            shimmer() {
                if (this.size >= this.maxSize) {
                    this.isReverse = true;
                } else if (this.size <= this.minSize) {
                    this.isReverse = false;
                }

                if (this.isReverse) {
                    this.size -= this.speed;
                } else {
                    this.size += this.speed;
                }
            }
        }

        class PixelCanvas extends HTMLElement {
            static register(tag = "pixel-canvas") {
                if ("customElements" in window && !customElements.get(tag)) {
                    customElements.define(tag, this);
                }
            }
        

            static css = `
                :host {
                    display: grid;
                    inline-size: 100%;
                    block-size: 100%;
                    overflow: hidden;
                    position: absolute;
                    top: 0;
                    left: 0;
                    z-index: 0;
                }
            `;

            get colors() {
                return this.dataset.colors?.split(",") || ["#f8fafc", "#f1f5f9", "#cbd5e1"];
            }

            get gap() {
                const value = this.dataset.gap || 5;
                const min = 4;
                const max = 50;

                if (value <= min) {
                    return min;
                } else if (value >= max) {
                    return max;
                } else {
                    return parseInt(value);
                }
            }

            get speed() {
                const value = this.dataset.speed || 35;
                const min = 0;
                const max = 100;
                const throttle = 0.001;

                if (value <= min || this.reducedMotion) {
                    return min;
                } else if (value >= max) {
                    return max * throttle;
                } else {
                    return parseInt(value) * throttle;
                }
            }

            get noFocus() {
                return this.hasAttribute("data-no-focus");
            }

            connectedCallback() {
                const canvas = document.createElement("canvas");
                const sheet = new CSSStyleSheet();

                this._parent = this.parentNode;
                this.shadowroot = this.attachShadow({ mode: "open" });

                sheet.replaceSync(PixelCanvas.css);

                this.shadowroot.adoptedStyleSheets = [sheet];
                this.shadowroot.append(canvas);
                this.canvas = this.shadowroot.querySelector("canvas");
                this.ctx = this.canvas.getContext("2d");
                this.timeInterval = 1000 / 60;
                this.timePrevious = performance.now();
                this.reducedMotion = window.matchMedia(
                    "(prefers-reduced-motion: reduce)"
                ).matches;

                this.init();
                this.resizeObserver = new ResizeObserver(() => this.init());
                this.resizeObserver.observe(this);

                this._parent.addEventListener("mouseenter", this);
                this._parent.addEventListener("mouseleave", this);

                if (!this.noFocus) {
                    this._parent.addEventListener("focusin", this);
                    this._parent.addEventListener("focusout", this);
                }
                
                // Start animation on load for header
                if (this._parent.classList.contains('header')) {
                    this.handleAnimation("appear");
                }
            }

            disconnectedCallback() {
                this.resizeObserver.disconnect();
                this._parent.removeEventListener("mouseenter", this);
                this._parent.removeEventListener("mouseleave", this);

                if (!this.noFocus) {
                    this._parent.removeEventListener("focusin", this);
                    this._parent.removeEventListener("focusout", this);
                }

                delete this._parent;
            }

            handleEvent(event) {
                this[`on${event.type}`](event);
            }

            onmouseenter() {
                this.handleAnimation("appear");
            }

            onmouseleave() {
                if (!this._parent.classList.contains('header')) {
                    this.handleAnimation("disappear");
                }
            }

            onfocusin(e) {
                if (e.currentTarget.contains(e.relatedTarget)) return;
                this.handleAnimation("appear");
            }

            onfocusout(e) {
                if (e.currentTarget.contains(e.relatedTarget)) return;
                if (!this._parent.classList.contains('header')) {
                    this.handleAnimation("disappear");
                }
            }

            handleAnimation(name) {
                cancelAnimationFrame(this.animation);
                this.animation = this.animate(name);
            }

            init() {
                const rect = this.getBoundingClientRect();
                const width = Math.floor(rect.width);
                const height = Math.floor(rect.height);

                this.pixels = [];
                this.canvas.width = width;
                this.canvas.height = height;
                this.canvas.style.width = `${width}px`;
                this.canvas.style.height = `${height}px`;
                this.createPixels();
            }

            getDistanceToCanvasCenter(x, y) {
                const dx = x - this.canvas.width / 2;
                const dy = y - this.canvas.height / 2;
                const distance = Math.sqrt(dx * dx + dy * dy);

                return distance;
            }

            createPixels() {
                for (let x = 0; x < this.canvas.width; x += this.gap) {
                    for (let y = 0; y < this.canvas.height; y += this.gap) {
                        const color = this.colors[
                            Math.floor(Math.random() * this.colors.length)
                        ];
                        const delay = this.reducedMotion
                            ? 0
                            : this.getDistanceToCanvasCenter(x, y);

                        this.pixels.push(
                            new Pixel(this.canvas, this.ctx, x, y, color, this.speed, delay)
                        );
                    }
                }
            }

            animate(fnName) {
                this.animation = requestAnimationFrame(() => this.animate(fnName));

                const timeNow = performance.now();
                const timePassed = timeNow - this.timePrevious;

                if (timePassed < this.timeInterval) return;

                this.timePrevious = timeNow - (timePassed % this.timeInterval);

                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                for (let i = 0; i < this.pixels.length; i++) {
                    this.pixels[i][fnName]();
                }

                if (this.pixels.every((pixel) => pixel.isIdle)) {
                    cancelAnimationFrame(this.animation);
                }
            }
        }

        // To-Do List Application Logic
        document.addEventListener('DOMContentLoaded', function() {
            // Register the PixelCanvas component
            PixelCanvas.register();
            
            // Initialize the to-do list
            loadGroups();
        });

        // Define the confirmation dialog functionality
        function showConfirmDialog(title, message, onConfirm) {
            const confirmDialog = document.getElementById('confirmDialog');
            const confirmTitle = document.getElementById('confirmTitle');
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmButton = document.getElementById('confirmButton');
            const cancelButton = document.getElementById('cancelButton');
            
            // Set the dialog content
            confirmTitle.textContent = title;
            confirmMessage.textContent = message;
            
            // Show the dialog
            confirmDialog.style.display = 'block';
            
            // Remove any existing event listeners
            const newConfirmButton = confirmButton.cloneNode(true);
            const newCancelButton = cancelButton.cloneNode(true);
            confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
            cancelButton.parentNode.replaceChild(newCancelButton, cancelButton);
            
            // Add event listeners
            newConfirmButton.addEventListener('click', function() {
                confirmDialog.style.display = 'none';
                onConfirm();
            });
            
            newCancelButton.addEventListener('click', function() {
                confirmDialog.style.display = 'none';
            });
            
            // Close when clicking outside
            window.addEventListener('click', function closeOutside(event) {
                if (event.target === confirmDialog) {
                    confirmDialog.style.display = 'none';
                    window.removeEventListener('click', closeOutside);
                }
            });
            
            // Close with Escape key
            window.addEventListener('keydown', function escapeClose(event) {
                if (event.key === 'Escape') {
                    confirmDialog.style.display = 'none';
                    window.removeEventListener('keydown', escapeClose);
                }
            });
        }

        function loadGroups() {
            fetch('get_groups.php?user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(groups => {
                    const container = document.getElementById('groups-container');
                    container.innerHTML = ''; // Clear existing groups to prevent duplication

                    if (groups.length === 0) {
                        showEmptyState(container);
                        return;
                    }

                    groups.forEach((group, index) => {
                        // Choose different color schemes for different groups
                        const colorSchemes = [
                            "#e0f2fe, #7dd3fc, #0ea5e9", // Blue
                            "#fef08a, #fde047, #eab308", // Yellow
                            "#fecdd3, #fda4af, #e11d48"  // Pink
                        ];
                        
                        const colorScheme = colorSchemes[index % colorSchemes.length];
                        
                        const groupElement = document.createElement('div');
                        groupElement.classList.add('group');
                        groupElement.setAttribute('data-group-id', group.id);
                        groupElement.innerHTML = `
                            <div class="group-header">
                                <h3>${group.name}</h3>
                                <button onclick="showAddTaskModal(${group.id})">+ Task</button>
                                <button class="delete-group" onclick="deleteGroup(${group.id})">x</button>
                                <pixel-canvas data-gap="6" data-speed="30" data-colors="${colorScheme}" data-no-focus style="z-index:1;"></pixel-canvas>
                            </div>
                            <div class="tasks" id="group-${group.id}"></div>
                        `;
                        container.appendChild(groupElement);
                    });

                    loadTasks(); // Load tasks after adding groups
                    initializeDragAndDrop(); // Reinitialize drag-and-drop
                });
        }

        function showEmptyState(container) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No task groups yet</h3>
                    <p>Click "Add Group" to create your first task group</p>
                </div>
            `;
        }

        function loadTasks() {
            fetch('get_tasks.php?user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(tasks => {
                    // Clear existing tasks first
                    document.querySelectorAll('.tasks').forEach(container => {
                        container.innerHTML = '';
                    });
                    
                    tasks.forEach(task => {
                        const taskElement = document.createElement('div');
                        taskElement.classList.add('task');
                        taskElement.setAttribute('data-task-id', task.id);
                        
                        // Add completed class if task is completed
                        if (task.completed == 1 || task.status === 'completed') {
                            taskElement.classList.add('task-completed');
                        }
                        
                        // Style tasks based on priority
                        let priorityClass = '';
                        switch(task.priority) {
                            case 'low':
                                priorityClass = 'priority-low';
                                taskElement.style.borderLeftColor = 'var(--accent-blue)';
                                break;
                            case 'medium':
                                priorityClass = 'priority-medium';
                                taskElement.style.borderLeftColor = 'var(--accent-yellow)';
                                break;
                            case 'high':
                                priorityClass = 'priority-high';
                                taskElement.style.borderLeftColor = 'var(--accent-pink)';
                                break;
                        }
                        
                        taskElement.innerHTML = `
                            <h4>${task.title}</h4>
                            <p>${task.description}</p>
                            <p>Due: ${formatDate(task.due_date)}</p>
                            <div class="task-priority ${priorityClass}">${capitalizeFirstLetter(task.priority)} Priority</div>
                            <div class="task-actions">
                                <button onclick="completeTask(${task.id}, this)" class="complete-btn ${task.completed == 1 || task.status === 'completed' ? 'completed' : ''}">
                                    ${task.completed == 1 || task.status === 'completed' ? 'Completed' : 'Complete'}
                                </button>
                                <button onclick="deleteTask(${task.id})">Delete</button>
                            </div>
                        `;
                        
                        const groupContainer = document.getElementById(`group-${task.group_id}`);
                        if (groupContainer) {
                            groupContainer.appendChild(taskElement);
                        }
                    });

                    initializeDragAndDrop();
                });
        }

        function completeTask(taskId, buttonElement) {
            fetch('archive_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `task_id=${encodeURIComponent(taskId)}&completed=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate the task element before removal
                    const taskElement = buttonElement.closest('.task');
                    taskElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    taskElement.style.opacity = '0';
                    taskElement.style.transform = 'translateX(20px)';
                    
                    // Remove the task from the DOM after animation
                    setTimeout(() => {
                        taskElement.remove();
                        // Show message that task was archived
                        showToast('Task archived successfully');
                    }, 300);
                    
                    // Update archive count badge
                    updateArchiveCount();
                } else {
                    showAlert('Error archiving task: ' + data.error);
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                showAlert("Network error or server not responding.");
            });
        }

        function updateArchiveCount() {
            fetch('get_archive_count.php?user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const archiveCount = document.getElementById('archiveCount');
                    if (archiveCount) {
                        archiveCount.textContent = data.count;
                    }
                });
        }

        function toggleArchiveView() {
            const activeView = document.getElementById('active-view');
            const archiveView = document.getElementById('archive-view');
            const archiveButton = document.getElementById('archive-button');

            if (activeView.style.display !== 'none') {
                // Switch to archive view
                activeView.style.display = 'none';
                archiveView.style.display = 'block';
                archiveButton.textContent = 'Back to Tasks';
                
                // Remove the overflow hidden style
                document.body.style.overflow = 'auto'; // Change this line
                
                loadArchivedTasks();
            } else {
                // Switch to active view
                activeView.style.display = 'block';
                archiveView.style.display = 'none';
                archiveButton.textContent = 'View Archive';

                // Ensure scrolling is enabled
                document.body.style.overflow = 'auto';
                document.body.style.pointerEvents = 'auto';

                // Reset focus and clean overlays
                document.activeElement.blur();
                const overlays = document.querySelectorAll('.modal, .overlay, .backdrop');
                overlays.forEach(overlay => {
                    overlay.style.display = 'none';
                });

                loadGroups();

                setTimeout(() => {
                    initializeEventListeners();
                    document.getElementById('taskTitle')?.focus();
                }, 100);
            }
        }

        // Add this new function to re-initialize event listeners
        function initializeEventListeners() {
            const groupNameInput = document.getElementById('groupName');
            if (groupNameInput) {
                groupNameInput.addEventListener('keyup', function(event) {
                    if (event.key === 'Enter') {
                        addGroup();
                    }
                });
            }

            const taskTitleInput = document.getElementById('taskTitle');
            if (taskTitleInput) {
                taskTitleInput.addEventListener('keyup', function(event) {
                    if (event.key === 'Enter') {
                        addTask();
                    }
                });
            }
        }

        function loadArchivedTasks() {
            fetch('get_archived_tasks.php')
                .then(response => response.json())
                .then(data => {
                    const archiveContainer = document.getElementById('archive-container');
                    archiveContainer.innerHTML = ''; // Clear existing archived tasks

                    if (!data.success) {
                        archiveContainer.innerHTML = `
                            <div class="empty-state">
                                <h3>Error loading archived tasks</h3>
                                <p>${data.error || 'Please try again later.'}</p>
                            </div>
                        `;
                        return;
                    }

                    const tasks = data.archived_tasks;

                    if (tasks.length === 0) {
                        archiveContainer.innerHTML = `
                            <div class="empty-state">
                                <h3>No archived tasks</h3>
                                <p>Completed tasks will appear here</p>
                            </div>
                        `;
                        return;
                    }

                    // Group tasks by date completed or 'Overdue'
                    const tasksByDate = {};
                    tasks.forEach(task => {
                        let sectionKey = '';

                        if (task.overdue == 1) {
                            sectionKey = 'Overdue';
                        } else {
                            sectionKey = formatDate(task.completed_date);
                        }

                        if (!tasksByDate[sectionKey]) {
                            tasksByDate[sectionKey] = [];
                        }
                        tasksByDate[sectionKey].push(task);
                    });

                    // Create sections for each date (or 'Overdue')
                    Object.keys(tasksByDate)
                        .sort((a, b) => {
                            // Custom sorting: Overdue comes first, then by date descending
                            if (a === 'Overdue') return -1;
                            if (b === 'Overdue') return 1;
                            return new Date(b) - new Date(a);
                        })
                        .forEach(date => {
                            const dateSection = document.createElement('div');
                            dateSection.classList.add('archive-date-section');

                            // Custom label for overdue section
                            const displayDate = date === 'Overdue' ? 'Overdue Tasks' : date;

                            dateSection.innerHTML = `<h3 class="archive-date">${displayDate}</h3>`;

                            // Add tasks for this date
                            tasksByDate[date].forEach(task => {
                                const taskElement = document.createElement('div');
                                taskElement.classList.add('task', 'archived-task');
                                taskElement.setAttribute('data-task-id', task.id);

                                // Style tasks based on priority
                                let priorityClass = '';
                                let borderColor = '';
                                switch (task.priority) {
                                    case 'low':
                                        priorityClass = 'priority-low';
                                        borderColor = 'var(--accent-blue)';
                                        break;
                                    case 'medium':
                                        priorityClass = 'priority-medium';
                                        borderColor = 'var(--accent-yellow)';
                                        break;
                                    case 'high':
                                        priorityClass = 'priority-high';
                                        borderColor = 'var(--accent-pink)';
                                        break;
                                    default:
                                        borderColor = '#ccc';
                                }

                                taskElement.style.borderLeft = `5px solid ${borderColor}`;

                                let badgeHTML = '';

                                if (task.overdue == 1) {
                                    badgeHTML = `<div class="task-badge overdue-badge">Overdue</div>`;
                                } else {
                                    const archiveReason = task.archive_reason
                                        ? capitalizeFirstLetter(task.archive_reason)
                                        : 'Archived';
                                    badgeHTML = `
                                        <div class="task-badge archive-reason-badge">
                                            ${archiveReason}
                                        </div>
                                    `;
                                }

                                taskElement.innerHTML = `
                                    <h4>${task.title}</h4>
                                    <p>${task.description}</p>
                                    <p>Due: ${formatDate(task.due_date)}</p>

                                    <div class="task-priority ${priorityClass}">
                                        ${capitalizeFirstLetter(task.priority)} Priority
                                    </div>

                                    <div class="task-badges">
                                        ${badgeHTML}
                                    </div>

                                    <div class="task-actions">
                                        <button onclick="if(confirm('Restore this task?')) restoreTask(${task.id}, this)" class="restore-btn">
                                            Restore
                                        </button>
                                        <button onclick="if(confirm('Permanently delete this task?')) deleteArchivedTask(${task.id})">
                                            Delete
                                        </button>
                                    </div>
                                `;

                                dateSection.appendChild(taskElement);
                            });

                            archiveContainer.appendChild(dateSection);
                        });
                })
                .catch(error => {
                    console.error('Error fetching archived tasks:', error);
                    const archiveContainer = document.getElementById('archive-container');
                    archiveContainer.innerHTML = `
                        <div class="empty-state">
                            <h3>Error loading archived tasks</h3>
                            <p>Check your connection and try again.</p>
                        </div>
                    `;
                });
        }

        function restoreTask(taskId, buttonElement) {
            fetch('restore_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `task_id=${encodeURIComponent(taskId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate the task element before removal
                    const taskElement = buttonElement.closest('.task');
                    taskElement.style.transition = 'opacity 0.3s, transform 0.3s';
                    taskElement.style.opacity = '0';
                    taskElement.style.transform = 'translateX(20px)';
                    
                    // Remove the task from the DOM after animation
                    setTimeout(() => {
                        taskElement.remove();
                        
                        // If no more tasks in this date section, remove the section
                        const dateSection = buttonElement.closest('.archive-date-section');
                        if (dateSection && dateSection.querySelectorAll('.task').length === 0) {
                            dateSection.remove();
                        }
                        
                        // Show message that task was restored
                        showToast('Task restored successfully');
                    }, 300);
                    
                    // Update archive count badge
                    updateArchiveCount();
                } else {
                    showAlert('Error restoring task: ' + data.error);
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                showAlert("Network error or server not responding.");
            });
        }

        function deleteArchivedTask(taskId) {
            showConfirmDialog(
                "Delete Archived Task",
                "Are you sure you want to permanently delete this task?",
                function() {
                    fetch('delete_archived_task.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `task_id=${encodeURIComponent(taskId)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
                            if (taskElement) {
                                taskElement.style.transition = 'opacity 0.3s, transform 0.3s';
                                taskElement.style.opacity = '0';
                                taskElement.style.transform = 'scale(0.95)';
                                
                                setTimeout(() => {
                                    taskElement.remove();
                                    
                                    // If no more tasks in this date section, remove the section
                                    const dateSection = taskElement.closest('.archive-date-section');
                                    if (dateSection && dateSection.querySelectorAll('.task').length === 0) {
                                        dateSection.remove();
                                    }
                                    
                                    // If no more archived tasks at all, show empty state
                                    if (document.querySelectorAll('.archived-task').length === 0) {
                                        document.getElementById('archive-container').innerHTML = `
                                            <div class="empty-state">
                                                <h3>No archived tasks</h3>
                                                <p>Completed tasks will appear here</p>
                                            </div>
                                        `;
                                    }

                                }, 300);
                                
                                // Update archive count badge
                                updateArchiveCount();

                                document.activeElement.blur(); // Remove focus from the button
                            }
                        } else {
                            showAlert('Error deleting archived task: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        showAlert("Network error or server not responding.");
                    });
                }
            );
        }

        function showToast(message) {
            const toastContainer = document.getElementById('toast-container');
            
            if (!toastContainer) {
                // Create toast container if it doesn't exist
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.style.position = 'fixed';
                container.style.bottom = '20px';
                container.style.right = '20px';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = message;
            document.getElementById('toast-container').appendChild(toast);
            
            // Show toast with animation
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            }, 50);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        // Initialize the archive count when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Register the PixelCanvas component
            PixelCanvas.register();
            
            // Initialize the to-do list
            loadGroups();
            
            // Initialize archive count
            updateArchiveCount();
        });

        function formatDate(dateString) {
            if (!dateString) return 'No date set';
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function addGroup() {
            const name = document.getElementById('groupName').value.trim();
            if (!name) {
                showAlert('Please enter a group name');
                document.getElementById('groupName').focus();
                return;
            }
            
            fetch('add_group.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `name=${encodeURIComponent(name)}&user_id=<?php echo $user_id; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadGroups();
                    closeAddGroupModal();
                    document.getElementById('groupName').value = '';
                } else {
                    showAlert('Error adding group: ' + data.error);
                }
            });
        }

        function addTask() {
            const groupId = document.getElementById('taskGroupId').value;
            const title = document.getElementById('taskTitle').value.trim();
            const description = document.getElementById('taskDescription').value.trim();
            const dueDate = document.getElementById('taskDueDate').value;
            const priority = document.getElementById('taskPriority').value;

            // Validation checks
            if (!title) {
                showAlert("Please enter a task title.");
                document.getElementById('taskTitle').focus();
                return;
            }

            // Send data to server only if it passes validation
            fetch('add_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `group_id=${encodeURIComponent(groupId)}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}&due_date=${encodeURIComponent(dueDate)}&priority=${encodeURIComponent(priority)}&user_id=<?php echo $user_id; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTasks();  // Reload tasks
                    closeAddTaskModal();
                } else {
                    showAlert('Error adding task: ' + data.error);
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                showAlert("Network error or server not responding.");
            });
        }

        let drake = null; // Store Dragula instance globally

        function initializeDragAndDrop() {
            // Destroy previous instance to avoid duplicate bindings
            if (drake) {
                drake.destroy();
            }

            // Get all task containers again
            const containers = Array.from(document.querySelectorAll('.tasks'));

            // Reinitialize Dragula
            drake = dragula(containers).on('drop', function (el, target, source, sibling) {
                const taskId = el.getAttribute('data-task-id');
                const newGroupId = target.id.replace('group-', '');

                fetch('update_task_group.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `task_id=${encodeURIComponent(taskId)}&new_group_id=${encodeURIComponent(newGroupId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating task group: ' + data.error);
                        loadTasks(); // Reload to original position if error
                    }
                });
            });
        }

        function deleteTask(taskId) {
            showConfirmDialog(
                "Delete Task",
                "Are you sure you want to delete this task?",
                function() {
                    fetch('delete_task.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `task_id=${encodeURIComponent(taskId)}`
                    })
                    .then(response => response.text()) 
                    .then(text => {
                        try {
                            let data = JSON.parse(text); 
                            if (data.success) {
                                const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
                                if (taskElement) {
                                    taskElement.style.opacity = '0';
                                    setTimeout(() => {
                                        taskElement.remove();
                                    }, 300);
                                }
                            } else {
                                showAlert('Error deleting task: ' + data.error);
                            }
                        } catch (error) {
                            console.error("Invalid JSON response:", text);
                            showAlert("Unexpected server response. Check console for details.");
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        showAlert("Network error or server not responding.");
                    });
                }
            );
        }

        function deleteGroup(groupId) {
            showConfirmDialog(
                "Delete Group",
                "Are you sure you want to delete this group and all its tasks?",
                function() {
                    fetch('delete_group.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `group_id=${encodeURIComponent(groupId)}`
                    })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            let data = JSON.parse(text);
                            if (data.success) {
                                const groupElement = document.querySelector(`[data-group-id="${groupId}"]`);
                                if (groupElement) {
                                    groupElement.style.opacity = '0';
                                    groupElement.style.transform = 'scale(0.95)';
                                    groupElement.style.transition = 'opacity 0.3s, transform 0.3s';
                                    setTimeout(() => {
                                        groupElement.remove();
                                        // Check if there are any groups left
                                        if (document.querySelectorAll('.group').length === 0) {
                                            showEmptyState(document.getElementById('groups-container'));
                                        }
                                    }, 300);
                                }
                            } else {
                                showAlert('Error deleting group: ' + data.error);
                            }
                        } catch (error) {
                            console.error("Invalid JSON response:", text);
                            showAlert("Unexpected server response. Check console for details.");
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        showAlert("Network error or server not responding.");
                    });
                }
            );
        }

        // custom alert function to replace the window alert
        function showAlert(message) {
            showConfirmDialog(
                "Alert",
                message,
                function() {
                    // Just close the dialog
                }
            );
            // Hide the cancel button for alerts
            document.getElementById('cancelButton').style.display = 'none';
            // Rename the confirm button
            document.getElementById('confirmButton').textContent = 'OK';
        }

        function showAddGroupModal() {
            document.getElementById('addGroupModal').style.display = 'block';
            document.getElementById('groupName').focus();
        }

        function closeAddGroupModal() {
            document.getElementById('addGroupModal').style.display = 'none';
        }

        function showAddTaskModal(groupId) {
            // Reset the form first
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskDescription').value = '';
            
            // Set the group ID
            document.getElementById('taskGroupId').value = groupId;
            
            // Show the modal
            document.getElementById('addTaskModal').style.display = 'block';
            
            // Force focus with a small delay to ensure the DOM is ready
            setTimeout(() => {
                const titleInput = document.getElementById('taskTitle');
                titleInput.focus();
                
                // Try to force the input to be editable
                titleInput.readOnly = false;
                titleInput.disabled = false;
            }, 50);
        }

        function closeAddTaskModal() {
            document.getElementById('addTaskModal').style.display = 'none';
            
            // Reset form values
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskDescription').value = '';
            document.getElementById('taskDueDate').value = '';
            document.getElementById('taskPriority').value = 'low';
            
            // Reset any lingering focus
            document.activeElement.blur();
        }

        // Close modals when clicking outside of them
        window.onclick = function(event) {
            const addGroupModal = document.getElementById('addGroupModal');
            const addTaskModal = document.getElementById('addTaskModal');
            
            if (event.target === addGroupModal) {
                closeAddGroupModal();
            }
            
            if (event.target === addTaskModal) {
                closeAddTaskModal();
            }
        }

        // Handle Enter key in modals
        document.getElementById('groupName').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                addGroup();
            }
        });

        document.getElementById('taskTitle').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                addTask();
            }
        });
    </script>

    <div id="toast-container"></div>
</body>
</html>