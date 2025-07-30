<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Classic To-Do Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Crimson+Text:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Crimson Text', serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      color: #2c3e50;
      line-height: 1.6;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .header {
      text-align: center;
      margin-bottom: 3rem;
      position: relative;
    }

    .header::after {
      content: '';
      position: absolute;
      bottom: -1rem;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: linear-gradient(90deg, #8b7355, #d4af37);
      border-radius: 2px;
    }

    .main-title {
      font-family: 'Playfair Display', serif;
      font-size: 3rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 0.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .subtitle {
      font-size: 1.2rem;
      color: #7f8c8d;
      font-style: italic;
    }

    .logout-btn {
      position: absolute;
      top: 0;
      right: 0;
      background: linear-gradient(135deg, #8b7355, #d4af37);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 25px;
      font-family: 'Crimson Text', serif;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.2);
      color: white;
    }

    .form-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 2.5rem;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
      margin-bottom: 3rem;
    }

    .form-title {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-control {
      width: 100%;
      padding: 1rem;
      border: 2px solid #e0e6ed;
      border-radius: 10px;
      font-family: 'Crimson Text', serif;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: rgba(255,255,255,0.8);
    }

    .form-control:focus {
      outline: none;
      border-color: #8b7355;
      box-shadow: 0 0 0 3px rgba(139, 115, 85, 0.1);
      background: white;
    }

    .form-control::placeholder {
      color: #95a5a6;
      font-style: italic;
    }

    .textarea-control {
      min-height: 100px;
      resize: vertical;
    }

    .btn-primary {
      background: linear-gradient(135deg, #8b7355, #d4af37);
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 25px;
      font-family: 'Crimson Text', serif;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .tasks-section {
      margin-bottom: 3rem;
    }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 1.5rem;
      text-align: center;
      position: relative;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -0.5rem;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 2px;
      background: linear-gradient(90deg, #8b7355, #d4af37);
    }

    .tasks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
    }

    .task-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .task-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #8b7355, #d4af37);
    }

    .task-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .task-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }

    .task-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: #2c3e50;
      margin: 0;
      flex: 1;
    }

    .task-date {
      font-size: 0.9rem;
      color: #7f8c8d;
      font-style: italic;
      margin-left: 1rem;
    }

    .task-description {
      color: #5d6d7e;
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    .task-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .task-category {
      background: rgba(139, 115, 85, 0.1);
      color: #8b7355;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-weight: 600;
    }

    .priority-high { 
      color: #e74c3c; 
      font-weight: bold;
      text-transform: uppercase;
    }
    .priority-medium { 
      color: #f39c12; 
      font-weight: bold;
      text-transform: uppercase;
    }
    .priority-low { 
      color: #27ae60; 
      font-weight: bold;
      text-transform: uppercase;
    }

    .task-actions {
      display: flex;
      gap: 0.75rem;
    }

    .btn-sm {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      border: none;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Crimson Text', serif;
    }

    .btn-success {
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      color: white;
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
    }

    .btn-danger {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    }

    .completed-task {
      opacity: 0.7;
      background: rgba(240, 240, 240, 0.95);
    }

    .completed-task .task-name,
    .completed-task .task-description {
      text-decoration: line-through;
      color: #95a5a6;
    }

    .completed-task::before {
      background: linear-gradient(90deg, #95a5a6, #7f8c8d);
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
      color: #7f8c8d;
      font-style: italic;
      font-size: 1.1rem;
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #bdc3c7;
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .main-title {
        font-size: 2rem;
      }
      
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .tasks-grid {
        grid-template-columns: 1fr;
      }
      
      .logout-btn {
        position: static;
        margin-top: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1 class="main-title">Classic To-Do Dashboard</h1>
      <p class="subtitle">Organize your tasks with timeless elegance</p>
      <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>

    <div class="form-card">
      <h2 class="form-title">Create New Task</h2>
      <form id="taskForm">
        <div class="form-row">
          <div class="form-group">
            <input type="text" class="form-control" name="task_name" placeholder="Enter task name..." required>
          </div>
          <div class="form-group">
            <input type="date" class="form-control" name="due_date">
          </div>
        </div>
        <div class="form-group">
          <textarea class="form-control textarea-control" name="description" placeholder="Describe your task..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <select class="form-control" name="priority">
              <option value="low">Low Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="high">High Priority</option>
            </select>
          </div>
          <div class="form-group">
            <select class="form-control" name="category">
              <option value="personal">Personal</option>
              <option value="work">Work</option>
              <option value="shopping">Shopping</option>
              <option value="health">Health</option>
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn-primary">
              <i class="fas fa-plus"></i> Add Task
            </button>
          </div>
        </div>
      </form>
    </div>

    <div class="tasks-section">
      <h2 class="section-title">Active Tasks</h2>
      <div id="tasksContainer" class="tasks-grid">
        <!-- Tasks will be loaded here -->
      </div>
    </div>

    <div class="tasks-section">
      <h2 class="section-title">Completed Tasks</h2>
      <div id="completedTasksContainer" class="tasks-grid">
        <!-- Completed tasks will be loaded here -->
      </div>
    </div>
  </div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    loadTasks();
    loadCompletedTasks();

    document.getElementById("taskForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch("php/saveTask.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then data => {
        if(data.status === 'success') {
          this.reset();
          loadTasks();
          loadCompletedTasks();
          showNotification('Task added successfully!', 'success');
        } else {
          showNotification("Error: " + data.message, 'error');
        }
      });
    });
  });

  function loadTasks() {
    fetch("php/fetchtask.php")
      .then(res => res.json())
      .then(tasks => {
        const container = document.getElementById("tasksContainer");
        container.innerHTML = "";

        // Only show tasks that are NOT completed
        const activeTasks = tasks.filter(task => task.is_completed == 0);

        if (activeTasks.length === 0) {
          container.innerHTML = `
            <div class="empty-state">
              <i class="fas fa-tasks"></i>
              <p>No active tasks yet. Create your first task above!</p>
            </div>
          `;
          return;
        }

        activeTasks.forEach(task => {
          const taskElement = document.createElement("div");
          taskElement.innerHTML = `
            <div class="task-card">
              <div class="task-header">
                <h3 class="task-name">${task.task_name}</h3>
                <span class="task-date">${task.due_date ? formatDate(task.due_date) : ''}</span>
              </div>
              <p class="task-description">${task.description || 'No description provided'}</p>
              <div class="task-meta">
                <span class="task-category"><i class="fas fa-tag"></i> ${task.category}</span>
                <span class="priority-${task.priority}">
                  <i class="fas fa-flag"></i> ${task.priority}
                </span>
              </div>
              <div class="task-actions">
                <button onclick="markComplete(${task.id})" class="btn-sm btn-success">
                  <i class="fas fa-check"></i> Complete
                </button>
                <button onclick="deleteTask(${task.id})" class="btn-sm btn-danger">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          `;
          container.appendChild(taskElement);
        });
      });
  }

  function loadCompletedTasks() {
    fetch("php/task_is_completed.php")
      .then(res => res.json())
      .then(tasks => {
        const container = document.getElementById("completedTasksContainer");
        container.innerHTML = "";
        
        if (tasks.length === 0) {
          container.innerHTML = `
            <div class="empty-state">
              <i class="fas fa-check-circle"></i>
              <p>No completed tasks yet.</p>
            </div>
          `;
          return;
        }
        
        tasks.forEach(task => {
          const taskElement = document.createElement("div");
          taskElement.innerHTML = `
            <div class="task-card completed-task">
              <div class="task-header">
                <h3 class="task-name">${task.task_name}</h3>
                <span class="task-date">${task.due_date ? formatDate(task.due_date) : ''}</span>
              </div>
              <p class="task-description">${task.description || 'No description provided'}</p>
              <div class="task-meta">
                <span class="task-category"><i class="fas fa-tag"></i> ${task.category}</span>
                <span class="priority-${task.priority}">
                  <i class="fas fa-flag"></i> ${task.priority}
                </span>
              </div>
              <div class="task-actions">
                <button onclick="deleteTask(${task.id})" class="btn-sm btn-danger">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          `;
          container.appendChild(taskElement);
        });
      });
  }

  function deleteTask(id) {
    if(confirm("Are you sure you want to delete this task?")) {
      fetch("php/deletetask.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          loadTasks();
          loadCompletedTasks();
          showNotification('Task deleted successfully!', 'success');
        }
      });
    }
  }

  function markComplete(id) {
    fetch("php/completetask.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        loadTasks();
        loadCompletedTasks();
        showNotification('Task completed! Well done!', 'success');
      }
    });
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  }

  function showNotification(message, type) {
    // Simple notification system
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 1rem 1.5rem;
      border-radius: 10px;
      color: white;
      font-weight: 600;
      z-index: 1000;
      animation: slideIn 0.3s ease;
      background: ${type === 'success' ? 'linear-gradient(135deg, #27ae60, #2ecc71)' : 'linear-gradient(135deg, #e74c3c, #c0392b)'};
    `;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Add CSS animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  `;
  document.head.appendChild(style);
</script>
</body>
</html>