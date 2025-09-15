<?php
session_start();
require_once 'Db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Task stats for charts
$total_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id=$user_id")->fetch_row()[0];
$completed_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id=$user_id AND is_completed=1")->fetch_row()[0];
$pending_tasks = $total_tasks - $completed_tasks;

// Priority distribution
$priority_data = $conn->query("SELECT priority, COUNT(*) as count FROM tasks WHERE user_id=$user_id GROUP BY priority");
$priorities = [];
$priority_counts = [];
while ($row = $priority_data->fetch_assoc()) {
    $priorities[] = ucfirst($row['priority']);
    $priority_counts[] = $row['count'];
}

// Habits count
$habit_count = $conn->query("SELECT COUNT(*) FROM habits WHERE user_id=$user_id")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics | QuickList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background:#f9fafb; font-family:'Inter',sans-serif; }
    .chart-card {
      background:white; border-radius:12px; padding:1.5rem;
      box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:2rem;
    }
    .page-header { margin-bottom:2rem; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
  <div class="page-header text-center mb-5">
    <h2><i class="fas fa-chart-pie text-primary"></i> Analytics Dashboard</h2>
    <p class="text-muted">Visualize your productivity trends</p>
  </div>

  <div class="row">
    <!-- Completion Status -->
    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3">Task Completion</h5>
        <canvas id="completionChart"></canvas>
      </div>
    </div>

    <!-- Priority Distribution -->
    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3">Task Priorities</h5>
        <canvas id="priorityChart"></canvas>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Habits Overview -->
    <div class="col-md-6">
      <div class="chart-card text-center">
        <h5 class="mb-3">Habits Tracked</h5>
        <i class="fas fa-seedling fa-3x text-success mb-3"></i>
        <h2><?= $habit_count ?></h2>
        <p class="text-muted">Total active habits</p>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-md-6">
      <div class="chart-card">
        <h5 class="mb-3">Summary</h5>
        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between">
            <span>Total Tasks</span> <strong><?= $total_tasks ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Completed Tasks</span> <strong><?= $completed_tasks ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Pending Tasks</span> <strong><?= $pending_tasks ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Active Habits</span> <strong><?= $habit_count ?></strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  // Task Completion Chart
  new Chart(document.getElementById('completionChart'), {
    type: 'doughnut',
    data: {
      labels: ['Completed', 'Pending'],
      datasets: [{
        data: [<?= $completed_tasks ?>, <?= $pending_tasks ?>],
        backgroundColor: ['#10b981', '#ef4444'],
      }]
    },
    options: { responsive:true, plugins:{legend:{position:'bottom'}} }
  });

  // Task Priority Chart
  new Chart(document.getElementById('priorityChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($priorities) ?>,
      datasets: [{
        label: 'Tasks',
        data: <?= json_encode($priority_counts) ?>,
        backgroundColor: ['#ef4444','#f59e0b','#10b981']
      }]
    },
    options: { responsive:true, scales:{y:{beginAtZero:true}} }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
