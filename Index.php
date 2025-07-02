<?php

session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern To-Do Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-50: #f0f9ff;
            --primary-100: #e0f2fe;
            --primary-500: #0ea5e9;
            --primary-600: #0284c7;
            --primary-700: #0369a1;
            --secondary-500: #8b5cf6;
            --secondary-600: #7c3aed;
            --success-500: #10b981;
            --warning-500: #f59e0b;
            --danger-500: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        [data-theme="dark"] {
            --gray-50: #1f2937;
            --gray-100: #374151;
            --gray-200: #4b5563;
            --gray-300: #6b7280;
            --gray-400: #9ca3af;
            --gray-500: #d1d5db;
            --gray-600: #e5e7eb;
            --gray-700: #f3f4f6;
            --gray-800: #f9fafb;
            --gray-900: #ffffff;
            --white: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--secondary-500) 100%);
            min-height: 100vh;
            color: var(--gray-800);
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            animation: slideDown 0.8s ease;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .header-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .header-content p {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .theme-toggle {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--danger-500), #dc2626);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stats-panel {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-100);
            animation: slideUp 0.8s ease 0.2s both;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-50), var(--primary-100));
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            border: 1px solid var(--primary-200);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-700);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .task-form {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--gray-100);
            animation: slideUp 0.8s ease 0.4s both;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-500);
            background: var(--white);
            box-shadow: 0 0 0 3px var(--primary-100);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .task-section {
            animation: slideUp 0.8s ease 0.6s both;
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .task-filters {
            display: flex;
            gap: 0.5rem;
            background: var(--white);
            padding: 0.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--gray-600);
        }

        .filter-btn.active {
            background: var(--primary-500);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            font-size: 1rem;
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .task-item {
            background: var(--white);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }

        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .task-item.completed {
            opacity: 0.7;
            background: var(--gray-50);
        }

        .task-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .task-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid var(--gray-300);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .task-checkbox.checked {
            background: var(--success-500);
            border-color: var(--success-500);
            color: white;
        }

        .task-title {
            flex: 1;
            font-weight: 600;
            color: var(--gray-900);
            font-size: 1.1rem;
        }

        .task-title.completed {
            text-decoration: line-through;
            color: var(--gray-500);
        }

        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high {
            background: var(--danger-500);
            color: white;
        }

        .priority-medium {
            background: var(--warning-500);
            color: white;
        }

        .priority-low {
            background: var(--success-500);
            color: white;
        }

        .task-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .task-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: var(--primary-100);
            color: var(--primary-600);
        }

        .btn-delete {
            background: var(--danger-100);
            color: var(--danger-600);
        }

        .btn-icon:hover {
            transform: scale(1.1);
        }

        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .fab:hover {
            transform: scale(1.1) rotate(45deg);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            animation: modalSlideIn 0.3s ease;
        }

        .notification {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: var(--white);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            z-index: 3000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            border-left: 4px solid var(--success-500);
        }

        .notification.error {
            border-left: 4px solid var(--danger-500);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <div class="profile-avatar">U</div>
                <div class="header-content">
                    <h1>Welcome back, User!</h1>
                    <p>Stay organized and productive today</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </header>

        <div class="dashboard-grid">
            <div class="stats-panel">
                <h3 style="margin-bottom: 1.5rem; color: var(--gray-900);">Today's Overview</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="totalTasks">0</div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="completedTasks">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="pendingTasks">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="overdueTasksCount">0</div>
                        <div class="stat-label">Overdue</div>
                    </div>
                </div>
                
                <div class="progress-section">
                    <h4 style="margin-bottom: 1rem; color: var(--gray-800);">Progress</h4>
                    <div style="background: var(--gray-200); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="progressBar" style="background: linear-gradient(90deg, var(--primary-500), var(--secondary-500)); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <p style="margin-top: 0.5rem; color: var(--gray-600); font-size: 0.875rem;" id="progressText">0% Complete</p>
                </div>
            </div>

            <div class="task-form">
                <h3 style="margin-bottom: 1.5rem; color: var(--gray-900);">Add New Task</h3>
                <form id="taskForm">
                    <div class="form-group">
                        <label class="form-label">Task Title</label>
                        <input type="text" id="taskInput" class="form-input" placeholder="What needs to be done?" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea id="taskDescription" class="form-input" rows="3" placeholder="Add details about your task..."></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select id="priorityInput" class="form-input">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" id="dueDateInput" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select id="categoryInput" class="form-input">
                            <option value="personal">Personal</option>
                            <option value="work">Work</option>
                            <option value="shopping">Shopping</option>
                            <option value="health">Health</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i>
                        Add Task
                    </button>
                </form>
            </div>
        </div>

        <div class="task-section">
            <div class="section-header">
                <h2 class="section-title">Your Tasks</h2>
            </div>

            <div class="task-filters">
                <button class="filter-btn active" data-filter="all">All Tasks</button>
                <button class="filter-btn" data-filter="pending">Pending</button>
                <button class="filter-btn" data-filter="completed">Completed</button>
                <button class="filter-btn" data-filter="overdue">Overdue</button>
            </div>

            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search tasks...">
            </div>

            <div class="task-list" id="taskList">
                <!-- Tasks will be rendered here -->
            </div>
        </div>
    </div>

    <button class="fab" onclick="focusTaskInput()">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Edit Task Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 1.5rem;">Edit Task</h3>
            <form id="editForm">
                <div class="form-group">
                    <label class="form-label">Task Title</label>
                    <input type="text" id="editTaskInput" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="editTaskDescription" class="form-input" rows="3"></textarea>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select id="editPriorityInput" class="form-input">
                            <option value="low">Low Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="date" id="editDueDateInput" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select id="editCategoryInput" class="form-input">
                        <option value="personal">Personal</option>
                        <option value="work">Work</option>
                        <option value="shopping">Shopping</option>
                        <option value="health">Health</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn" style="flex: 1; background: var(--gray-200); color: var(--gray-700);" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // App State
        let tasks = JSON.parse(localStorage.getItem('modernTasks') || '[]');
        let currentFilter = 'all';
        let editingTaskId = null;

        // Initialize app
        document.addEventListener('DOMContentLoaded', function() {
            renderTasks();
            updateStats();
            setMinDate();
        });

        // Set minimum date to today
        function setMinDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dueDateInput').min = today;
            document.getElementById('editDueDateInput').min = today;
        }

        // Task Form Handler
        document.getElementById('taskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const title = document.getElementById('taskInput').value.trim();
            const description = document.getElementById('taskDescription').value.trim();
            const priority = document.getElementById('priorityInput').value;
            const dueDate = document.getElementById('dueDateInput').value;
            const category = document.getElementById('categoryInput').value;

            if (!title) {
                showNotification('Please enter a task title', 'error');
                return;
            }

            const newTask = {
                id: Date.now(),
                title,
                description,
                priority,
                dueDate,
                category,
                completed: false,
                createdAt: new Date().toISOString()
            };

            tasks.push(newTask);
            saveTasks();
            renderTasks();
            updateStats();
            
            // Reset form
            this.reset();
            showNotification('Task added successfully!', 'success');
        });

        // Edit Form Handler
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const title = document.getElementById('editTaskInput').value.trim();
            const description = document.getElementById('editTaskDescription').value.trim();
            const priority = document.getElementById('editPriorityInput').value;
            const dueDate = document.getElementById('editDueDateInput').value;
            const category = document.getElementById('editCategoryInput').value;

            if (!title) {
                showNotification('Please enter a task title', 'error');
                return;
            }

            const taskIndex = tasks.findIndex(t => t.id === editingTaskId);
            if (taskIndex !== -1) {
                tasks[taskIndex] = {
                    ...tasks[taskIndex],
                    title,
                    description,
                    priority,
                    dueDate,
                    category
                };
                
                saveTasks();
                renderTasks();
                updateStats();
                closeEditModal();
                showNotification('Task updated successfully!', 'success');
            }
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            renderTasks();
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                renderTasks();
            });
        });

        // Save tasks to localStorage
        function saveTasks() {
            localStorage.setItem('modernTasks', JSON.stringify(tasks));
        }

        // Render tasks
        function renderTasks() {
            const taskList = document.getElementById('taskList');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filteredTasks = tasks.filter(task => {
                const matchesSearch = task.title.toLowerCase().includes(searchTerm) || 
                                    task.description.toLowerCase().includes(searchTerm);
                
                switch(currentFilter) {
                    case 'completed':
                        return matchesSearch && task.completed;
                    case 'pending':
                        return matchesSearch && !task.completed;
                    case 'overdue':
                        return matchesSearch && !task.completed && task.dueDate && new Date(task.dueDate) < new Date();
                    default:
                        return matchesSearch;
                }
            });

            // Sort by priority and due date
            filteredTasks.sort((a, b) => {
                const priorityOrder = { high: 3, medium: 2, low: 1 };
                if (priorityOrder[a.priority] !== priorityOrder[b.priority]) {
                    return priorityOrder[b.priority] - priorityOrder[a.priority];
                }
                if (a.dueDate && b.dueDate) {
                    return new Date(a.dueDate) - new Date(b.dueDate);
                }
                return new Date(b.createdAt) - new Date(a.createdAt);
            });

            if (filteredTasks.length === 0) {
                taskList.innerHTML = `
                    <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <div>No tasks found.</div>
                    </div>
                `;
                return;
            }