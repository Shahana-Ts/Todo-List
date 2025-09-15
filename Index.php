<?php
session_start();
include("navbar.php");

// Use PDO for DB
require_once 'Db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'] ?? 1;

// -------------------- ADD TASK --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task_name = htmlspecialchars(trim($_POST['task_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $priority = $_POST['priority'] ?? 'medium';
    $category = $_POST['category'] ?? 'other';
    $due_date = !empty($_POST['due_date']) ? date('Y-m-d H:i:s', strtotime($_POST['due_date'])) : null;

    if (!empty($task_name)) {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, task_name, description, priority, category, due_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $task_name, $description, $priority, $category, $due_date]);
        $_SESSION['flash'] = "Task added successfully ✅";
        header("Location: Index.php");
        exit;
    }
}

// -------------------- UPDATE TASK --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $taskId     = (int)$_POST['task_id'];
    $task_name  = trim($_POST['task_name']);
    $description = trim($_POST['description']);
    $priority   = $_POST['priority'] ?? 'medium';
    $category   = $_POST['category'] ?? 'other';
    $due_date   = !empty($_POST['due_date']) ? date('Y-m-d H:i:s', strtotime($_POST['due_date'])) : null;

    if ($task_name) {
        $stmt = $pdo->prepare("UPDATE tasks 
                               SET task_name=?, description=?, priority=?, category=?, due_date=? 
                               WHERE id=? AND user_id=?");
        $stmt->execute([$task_name, $description, $priority, $category, $due_date, $taskId, $user_id]);
        $_SESSION['flash'] = "Task updated successfully ✨";
        header("Location: Index.php");
        exit;
    }
}

// -------------------- COMPLETE TASK --------------------
if (isset($_GET['complete'])) {
    $taskId = (int)$_GET['complete'];
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $user_id]);
    $_SESSION['flash'] = "Task marked as complete 🎉";
    header("Location: Index.php");
    exit;
}

// -------------------- DELETE TASK --------------------
if (isset($_GET['delete'])) {
    $taskId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $user_id]);
    $_SESSION['flash'] = "Task deleted 🗑️";
    header("Location: Index.php");
    exit;
}

// -------------------- FETCH TASKS --------------------
$stmt = $pdo->prepare("
    SELECT *, 
           CASE 
               WHEN due_date < NOW() AND is_completed = 0 THEN 'overdue'
               ELSE ''
           END AS status
    FROM tasks 
    WHERE user_id = ? 
    ORDER BY 
        FIELD(priority, 'high','medium','low'),
        due_date ASC,
        created_at DESC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_tasks = count($tasks);
$completed_tasks = count(array_filter($tasks, fn($t) => $t['is_completed']));
$pending_tasks = $total_tasks - $completed_tasks;
$overdue_tasks = count(array_filter($tasks, fn($t) => $t['status'] === 'overdue'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QuickList | Premium Task Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
  <!-- Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- AOS (Animate On Scroll) -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
      --card-bg: rgba(255, 255, 255, 0.9);
      --text-primary: #2d3748;
      --text-secondary: #718096;
      --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 6px 15px rgba(0, 0, 0, 0.07);
      --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .dark-mode {
      --card-bg: rgba(30, 41, 59, 0.9);
      --text-primary: #f7fafc;
      --text-secondary: #cbd5e0;
      --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.2);
      --shadow-md: 0 6px 15px rgba(0, 0, 0, 0.25);
      --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--text-primary);
      transition: background 0.3s ease, color 0.3s ease;
      min-height: 100vh;
    }

    .dark-mode body {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    .card {
      border: none;
      border-radius: 16px;
      backdrop-filter: blur(10px);
      background: var(--card-bg);
      box-shadow: var(--shadow-md);
      transition: all 0.3s ease-in-out;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 1.5rem;
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow-sm);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 1.5rem;
    }

    .task.card {
      border-left: 4px solid;
      margin-bottom: 1rem;
      overflow: hidden;
    }

    .priority-high { border-left-color: #e53e3e; }
    .priority-medium { border-left-color: #ed8936; }
    .priority-low { border-left-color: #38a169; }
    .completed { border-left-color: #4299e1; }
    .overdue { border-left-color: #9b2c2c; }

    .btn-primary {
      background: var(--primary-gradient);
      border: none;
      border-radius: 10px;
      padding: 0.6rem 1.5rem;
      font-weight: 500;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
      transform: translateY(-2px);
    }

    .navbar-brand {
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .progress {
      height: 10px;
      border-radius: 10px;
      background: #edf2f7;
      overflow: hidden;
    }

    .dark-mode .progress {
      background: #2d3748;
    }

    .progress-bar {
      border-radius: 10px;
      background: var(--success-gradient);
    }

    .form-control, .form-select {
      border-radius: 10px;
      padding: 0.75rem 1rem;
      border: 1px solid #e2e8f0;
      transition: all 0.2s;
    }

    .dark-mode .form-control, 
    .dark-mode .form-select {
      background: #2d3748;
      border-color: #4a5568;
      color: #f7fafc;
    }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      border-color: #667eea;
    }

    .badge {
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      font-weight: 500;
    }

    .modal-content {
      border-radius: 16px;
      border: none;
    }

    .dark-mode .modal-content {
      background: #2d3748;
    }

    #darkToggle {
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--card-bg);
      box-shadow: var(--shadow-sm);
    }

    .filter-btn {
      border-radius: 10px;
      padding: 0.5rem 1rem;
      margin: 0 0.25rem 0.5rem;
      font-size: 0.85rem;
    }

    .task-actions {
      opacity: 0;
      transition: opacity 0.2s;
    }

    .task.card:hover .task-actions {
      opacity: 1;
    }

    .category-tag {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-right: 0.5rem;
    }

    .category-work { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
    .category-personal { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .category-study { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .category-other { background: rgba(156, 163, 175, 0.1); color: #9ca3af; }

    .dark-mode .category-work { background: rgba(99, 102, 241, 0.2); }
    .dark-mode .category-personal { background: rgba(16, 185, 129, 0.2); }
    .dark-mode .category-study { background: rgba(245, 158, 11, 0.2); }
    .dark-mode .category-other { background: rgba(156, 163, 175, 0.2); }

    @media (max-width: 768px) {
      .task-actions {
        opacity: 1;
      }
      
      .stat-card {
        padding: 1rem;
      }
      
      .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>QuickList <span class="text-primary">Premium</span></h1>
    <button id="darkToggle" class="btn btn-outline-dark"><i class="fas fa-moon"></i></button>
  </div>

  <!-- Flash message -->
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
      <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stats Overview -->
  <div class="row mb-4" data-aos="fade-up">
    <div class="col-md-3 col-sm-6">
      <div class="stat-card">
        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
          <i class="fas fa-tasks"></i>
        </div>
        <div>
          <h3 class="fw-bold mb-0"><?= $total_tasks ?></h3>
          <p class="text-muted mb-0 small">Total Tasks</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="stat-card">
        <div class="stat-icon bg-success bg-opacity-10 text-success">
          <i class="fas fa-check-circle"></i>
        </div>
        <div>
          <h3 class="fw-bold mb-0"><?= $completed_tasks ?></h3>
          <p class="text-muted mb-0 small">Completed</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="stat-card">
        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
          <i class="fas fa-spinner"></i>
        </div>
        <div>
          <h3 class="fw-bold mb-0"><?= $pending_tasks ?></h3>
          <p class="text-muted mb-0 small">Pending</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="stat-card">
        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
          <i class="fas fa-exclamation-circle"></i>
        </div>
        <div>
          <h3 class="fw-bold mb-0"><?= $overdue_tasks ?></h3>
          <p class="text-muted mb-0 small">Overdue</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-4 mb-4" data-aos="fade-right">
      <!-- Add Task Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Task</h5>
        </div>
        <div class="card-body">
          <form method="POST" id="addTaskForm">
            <div class="mb-3">
              <label for="task_name" class="form-label">Task Title</label>
              <input type="text" class="form-control" id="task_name" name="task_name" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                  <option value="work">Work</option>
                  <option value="personal">Personal</option>
                  <option value="study">Study</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" id="priority" name="priority">
                  <option value="high">High</option>
                  <option value="medium" selected>Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label for="due_date" class="form-label">Due Date</label>
              <input type="datetime-local" class="form-control" id="due_date" name="due_date">
            </div>
            <button type="submit" name="add_task" class="btn btn-primary w-100">
              <i class="fas fa-plus me-1"></i> Add Task
            </button>
          </form>
        </div>
      </div>

      <!-- Progress Card -->
      <div class="card shadow-sm" data-aos="fade-right" data-aos-delay="100">
        <div class="card-header bg-white py-3">
          <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Progress Overview</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Task Completion</span>
              <span><?= $total_tasks ? round(($completed_tasks/$total_tasks)*100) : 0 ?>%</span>
            </div>
            <div class="progress">
              <div class="progress-bar" role="progressbar" 
                   style="width: <?= $total_tasks ? ($completed_tasks/$total_tasks)*100 : 0 ?>%;">
              </div>
            </div>
          </div>
          <div class="mt-4">
            <canvas id="taskChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8" data-aos="fade-left">
      <!-- Task List Card -->
      <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Task List</h5>
          <div class="d-flex">
            <input type="text" id="taskSearch" class="form-control form-control-sm me-2" placeholder="Search tasks...">
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-filter"></i>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item filter-option" href="#" data-filter="all">All Tasks</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="completed">Completed</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="pending">Pending</a></li>
                <li><a class="dropdown-item filter-option" href="#" data-filter="overdue">Overdue</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Task Filters -->
          <div class="d-flex flex-wrap mb-3">
            <button class="filter-btn btn btn-outline-primary active" data-priority="all">All</button>
            <button class="filter-btn btn btn-outline-danger" data-priority="high">High Priority</button>
            <button class="filter-btn btn btn-outline-warning" data-priority="medium">Medium Priority</button>
            <button class="filter-btn btn btn-outline-success" data-priority="low">Low Priority</button>
          </div>

          <!-- Task List -->
          <div class="task-list" id="taskList">
            <?php if (empty($tasks)): ?>
              <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No tasks yet. Add one to get started!</p>
              </div>
            <?php else: ?>
              <?php foreach ($tasks as $task): ?>
                <div class="task card mb-3 
                  <?= $task['is_completed'] ? 'completed' : '' ?> 
                  <?= $task['status'] === 'overdue' ? 'overdue' : '' ?> 
                  priority-<?= $task['priority'] ?>"
                >
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                          <?php if ($task['is_completed']): ?>
                            <i class="fas fa-check-circle text-success me-2"></i>
                          <?php elseif ($task['status'] === 'overdue'): ?>
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                          <?php else: ?>
                            <i class="fas fa-circle-notch text-primary me-2"></i>
                          <?php endif; ?>
                          
                          <h5 class="card-title mb-0 me-2">
                            <?= htmlspecialchars($task['task_name']) ?>
                          </h5>
                          
                          <span class="badge bg-<?= 
                            $task['priority'] === 'high' ? 'danger' : 
                            ($task['priority'] === 'medium' ? 'warning' : 'success')
                          ?> me-2">
                            <?= ucfirst($task['priority']) ?>
                          </span>
                          
                          <span class="category-tag category-<?= $task['category'] ?>">
                            <?= ucfirst($task['category']) ?>
                          </span>
                        </div>
                        
                        <?php if (!empty($task['description'])): ?>
                          <p class="card-text text-muted mb-2"><?= htmlspecialchars($task['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="task-meta text-muted small">
                          <?php if ($task['due_date']): ?>
                            <span class="me-3">
                              <i class="far fa-clock me-1"></i> 
                              Due: <?= date('M j, Y g:i A', strtotime($task['due_date'])) ?>
                            </span>
                          <?php endif; ?>
                          
                          <span>
                            <i class="far fa-calendar me-1"></i> 
                            Created: <?= date('M j, Y', strtotime($task['created_at'])) ?>
                          </span>
                        </div>
                      </div>
                      
                      <div class="task-actions ms-3">
                        <?php if (!$task['is_completed']): ?>
                          <a href="Index.php?complete=<?= $task['id'] ?>" class="btn btn-sm btn-success completeBtn" title="Mark Complete">
                            <i class="fas fa-check"></i>
                          </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-sm btn-warning editBtn" 
                                data-id="<?= $task['id'] ?>"
                                data-name="<?= htmlspecialchars($task['task_name']) ?>"
                                data-desc="<?= htmlspecialchars($task['description']) ?>"
                                data-priority="<?= $task['priority'] ?>"
                                data-category="<?= $task['category'] ?>"
                                data-due="<?= $task['due_date'] ?>"
                                title="Edit Task">
                          <i class="fas fa-edit"></i>
                        </button>
                        
                        <a href="Index.php?delete=<?= $task['id'] ?>" class="btn btn-sm btn-danger deleteBtn" title="Delete Task">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Task</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="task_id" id="editTaskId">
          <div class="mb-3">
            <label class="form-label">Task Title</label>
            <input type="text" class="form-control" id="editTaskName" name="task_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Priority</label>
              <select class="form-select" id="editPriority" name="priority">
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Category</label>
              <select class="form-select" id="editCategory" name="category">
                <option value="work">Work</option>
                <option value="personal">Personal</option>
                <option value="study">Study</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Due Date</label>
            <input type="text" class="form-control" id="editDueDate" name="due_date">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_task" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  // Initialize AOS
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
  });

  // Dark mode toggle
  const darkToggle = document.getElementById('darkToggle');
  const body = document.body;
  
  // Check for saved dark mode preference
  if (localStorage.getItem('darkMode') === 'enabled') {
    body.classList.add('dark-mode');
    darkToggle.innerHTML = '<i class="fas fa-sun"></i>';
  }
  
  darkToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    
    if (body.classList.contains('dark-mode')) {
      localStorage.setItem('darkMode', 'enabled');
      darkToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
      localStorage.setItem('darkMode', 'disabled');
      darkToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
  });

  // Flatpickr
  flatpickr("#due_date", { 
    enableTime: true, 
    dateFormat: "Y-m-d H:i", 
    minDate: "today",
    time_24hr: false
  });
  
  flatpickr("#editDueDate", { 
    enableTime: true, 
    dateFormat: "Y-m-d H:i", 
    minDate: "today",
    time_24hr: false
  });

  // Search filter
  document.getElementById('taskSearch').addEventListener('input', function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll('.task.card').forEach(card => {
      let text = card.innerText.toLowerCase();
      card.style.display = text.includes(val) ? '' : 'none';
    });
  });

  // Priority filter
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      // Remove active class from all buttons
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      
      // Add active class to clicked button
      this.classList.add('active');
      
      const priority = this.dataset.priority;
      
      document.querySelectorAll('.task.card').forEach(card => {
        if (priority === 'all') {
          card.style.display = '';
        } else {
          card.style.display = card.classList.contains(`priority-${priority}`) ? '' : 'none';
        }
      });
    });
  });

  // Status filter
  document.querySelectorAll('.filter-option').forEach(option => {
    option.addEventListener('click', function(e) {
      e.preventDefault();
      
      const filter = this.dataset.filter;
      
      document.querySelectorAll('.task.card').forEach(card => {
        if (filter === 'all') {
          card.style.display = '';
        } else if (filter === 'completed') {
          card.style.display = card.classList.contains('completed') ? '' : 'none';
        } else if (filter === 'pending') {
          card.style.display = !card.classList.contains('completed') ? '' : 'none';
        } else if (filter === 'overdue') {
          card.style.display = card.classList.contains('overdue') ? '' : 'none';
        }
      });
    });
  });

  // Populate Edit Modal
  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editTaskId').value = btn.dataset.id;
      document.getElementById('editTaskName').value = btn.dataset.name;
      document.getElementById('editDescription').value = btn.dataset.desc;
      document.getElementById('editPriority').value = btn.dataset.priority;
      document.getElementById('editCategory').value = btn.dataset.category;
      document.getElementById('editDueDate').value = btn.dataset.due ? btn.dataset.due.replace(' ', 'T') : "";
      new bootstrap.Modal(document.getElementById('editTaskModal')).show();
    });
  });

  // SweetAlert for Delete Confirmation
  document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const url = this.href;
      Swal.fire({
        title: 'Are you sure?',
        text: "This task will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) window.location.href = url;
      });
    });
  });

  // Task completion with animation
  document.querySelectorAll('.completeBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const url = this.href;
      
      // Animate the task card
      const card = this.closest('.task.card');
      card.style.transition = 'all 0.5s ease';
      card.style.opacity = '0';
      card.style.transform = 'translateX(100px)';
      
      setTimeout(() => {
        window.location.href = url;
      }, 500);
    });
  });

  // Task Chart
  const ctx = document.getElementById('taskChart').getContext('2d');
  const taskChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Completed', 'Pending', 'Overdue'],
      datasets: [{
        data: [<?= $completed_tasks ?>, <?= $pending_tasks ?>, <?= $overdue_tasks ?>],
        backgroundColor: [
          '#10b981',
          '#f59e0b',
          '#ef4444'
        ],
        borderWidth: 0,
        hoverOffset: 10
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 12
            },
            usePointStyle: true,
            padding: 20
          }
        }
      }
    }
  });
</script>
</body>
</html>