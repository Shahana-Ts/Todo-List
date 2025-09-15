<?php
session_start();
require_once 'Db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_premium = !empty($_SESSION['is_premium']) && $_SESSION['is_premium'] == 1;

// Get username + email
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Stats
$total_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id=$user_id")->fetch_row()[0];
$completed_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id=$user_id AND is_completed=1")->fetch_row()[0];
$today_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id=$user_id AND due_date=CURDATE()")->fetch_row()[0];
$habits_count = $conn->query("SELECT COUNT(*) FROM habits WHERE user_id=$user_id")->fetch_row()[0];

// Urgent tasks
$urgent_tasks = $conn->query("SELECT task_name, due_date FROM tasks 
    WHERE user_id=$user_id AND is_completed=0 AND due_date IS NOT NULL 
    ORDER BY due_date ASC LIMIT 3");

// Greeting
$hour = date("H");
if ($hour < 12) $greeting = "Good Morning";
elseif ($hour < 18) $greeting = "Good Afternoon";
else $greeting = "Good Evening";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QuickList | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background:#f8fafc; font-family: "Poppins", sans-serif; }
    .glass-card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
    .welcome-banner { background:linear-gradient(135deg,#4f46e5,#6366f1); color:white; border-radius:12px; padding:20px; }
    .stat-card { border-left:4px solid #6366f1; }
    .task-item { border-left:3px solid #6366f1; margin-bottom:10px; padding:10px; border-radius:6px; }
    .task-item.urgent { border-left-color:#f43f5e; }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container my-4">

    <?php if ($is_premium): ?>
    <!-- ================= PREMIUM DASHBOARD ================= -->
    <div class="welcome-banner mb-4">
      <h2><?= $greeting ?>, <?= htmlspecialchars($username) ?> 👑</h2>
      <p>Email: <?= htmlspecialchars($email) ?> | Thanks for being a Premium member!</p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-3"><div class="glass-card stat-card"><h6>Total Tasks</h6><h3><?= $total_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#10b981"><h6>Completed</h6><h3><?= $completed_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#f59e0b"><h6>Due Today</h6><h3><?= $today_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#0ea5e9"><h6>Habits</h6><h3><?= $habits_count ?></h3></div></div>
    </div>

    <div class="row g-4">
      <!-- Left -->
      <div class="col-lg-8">
        <div class="glass-card mb-4">
          <h5>Your Progress</h5>
          <div id="progressChart"></div>
        </div>

        <div class="glass-card">
          <h5>Premium Quick Actions</h5>
          <div class="row g-3 mt-2">
            <div class="col-md-3 col-6">
              <a href="index.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-plus-circle fs-2 text-primary"></i><br>Add Task
              </a>
            </div>
            <div class="col-md-3 col-6">
              <a href="habit/habitIndex.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-seedling fs-2 text-success"></i><br>Add Habit
              </a>
            </div>
            <div class="col-md-3 col-6">
              <a href="habit/focus.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-bullseye fs-2 text-danger"></i><br>Focus Mode
              </a>
            </div>
            <div class="col-md-3 col-6">
              <a href="habit/analytics.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-chart-pie fs-2 text-warning"></i><br>Analytics
              </a>
            </div>
          </div>
        </div>

        <!-- Extra Premium Feature: Habit Trends -->
        <div class="glass-card mt-4">
          <h5>📊 Habit Trends</h5>
          <div id="habitChart"></div>
        </div>
      </div>

      <!-- Right -->
      <div class="col-lg-4">
        <div class="glass-card mb-4">
          <h5>Urgent Tasks</h5>
          <?php if ($urgent_tasks->num_rows > 0): ?>
            <?php while($task = $urgent_tasks->fetch_assoc()): ?>
              <div class="task-item urgent">
                <?= htmlspecialchars($task['task_name']) ?>
                <small class="text-danger float-end"><?= date('M j', strtotime($task['due_date'])) ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-success">✅ No urgent tasks!</p>
          <?php endif; ?>
        </div>

        <div class="glass-card">
          <h5>💡 Productivity Tip</h5>
          <p>Break work into 25-min focus sessions with short breaks.</p>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- ================= FREE DASHBOARD ================= -->
    <div class="welcome-banner mb-4">
      <h2><?= $greeting ?>, <?= htmlspecialchars($username) ?> 👋</h2>
      <p>Email: <?= htmlspecialchars($email) ?> | Upgrade to unlock more!</p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-3"><div class="glass-card stat-card"><h6>Total Tasks</h6><h3><?= $total_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#10b981"><h6>Completed</h6><h3><?= $completed_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#f59e0b"><h6>Due Today</h6><h3><?= $today_tasks ?></h3></div></div>
      <div class="col-md-3"><div class="glass-card stat-card" style="border-left-color:#0ea5e9"><h6>Habits</h6><h3><?= $habits_count ?></h3></div></div>
    </div>

    <div class="row g-4">
      <!-- Left -->
      <div class="col-lg-8">
        <div class="glass-card mb-4">
          <h5>Your Progress</h5>
          <div id="progressChart"></div>
        </div>

        <div class="glass-card">
          <h5>Quick Actions</h5>
          <div class="row g-3 mt-2">
            <div class="col-md-3 col-6">
              <a href="index.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-plus-circle fs-2 text-primary"></i><br>Add Task
              </a>
            </div>
            <div class="col-md-3 col-6">
              <a href="habit/habitIndex.php" class="text-decoration-none text-center d-block p-3 bg-light rounded">
                <i class="fas fa-seedling fs-2 text-success"></i><br>Add Habit
              </a>
            </div>
            <div class="col-md-3 col-6">
              <div class="text-center d-block p-3 bg-light rounded" data-bs-toggle="modal" data-bs-target="#upgradeModal">
                <i class="fas fa-lock fs-2 text-secondary"></i><br>Focus Mode
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="text-center d-block p-3 bg-light rounded" data-bs-toggle="modal" data-bs-target="#upgradeModal">
                <i class="fas fa-lock fs-2 text-secondary"></i><br>Analytics
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right -->
      <div class="col-lg-4">
        <div class="glass-card mb-4">
          <h5>Urgent Tasks</h5>
          <?php if ($urgent_tasks->num_rows > 0): ?>
            <?php while($task = $urgent_tasks->fetch_assoc()): ?>
              <div class="task-item urgent">
                <?= htmlspecialchars($task['task_name']) ?>
                <small class="text-danger float-end"><?= date('M j', strtotime($task['due_date'])) ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-success">✅ No urgent tasks!</p>
          <?php endif; ?>
        </div>

        <div class="glass-card">
          <h5>💡 Productivity Tip</h5>
          <p>If a task takes less than 2 minutes, do it immediately.</p>
        </div>
      </div>
    </div>

    <!-- Upgrade Modal -->
    <div class="modal fade" id="upgradeModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">🚀 Upgrade to Premium</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Unlock Focus Mode, Analytics, Habit Insights, and more powerful tools.</p>
            <a href="premium.php" class="btn btn-primary">Upgrade Now</a>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
<footer class="mt-5 py-4 bg-primary text-white text-center rounded-top">
  <div class="container">
    <div class="mb-2">
      <a href="https://github.com/" target="_blank" class="text-white me-3"><i class="fab fa-github"></i></a>
      <a href="mailto:support@quicklist.com" class="text-white me-3"><i class="fas fa-envelope"></i></a>
      <a href="profile.php" class="text-white"><i class="fas fa-user"></i></a>
    </div>
    <div>
      &copy; <?= date('Y') ?> QuickList &mdash; All rights reserved.
    </div>
  </div>
</footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    // Progress Chart
    var options = {
      series:[{name:"Completed",data:[4,5,4,6,7,5,8]},{name:"Pending",data:[2,3,2,1,2,3,1]}],
      chart:{type:"area",height:250,toolbar:{show:false}},
      colors:["#10b981","#6366f1"],dataLabels:{enabled:false},
      stroke:{curve:"smooth",width:2},xaxis:{categories:["Mon","Tue","Wed","Thu","Fri","Sat","Sun"]}
    };
    new ApexCharts(document.querySelector("#progressChart"), options).render();

    // Habit Chart (Premium Only)
    <?php if ($is_premium): ?>
    var habitOptions = {
      series: [{ name:"Habits", data:[2,3,1,4,2,5,3] }],
      chart: { type:"bar", height:250, toolbar:{show:false} },
      colors:["#0ea5e9"],
      plotOptions: { bar: { borderRadius: 6 } },
      xaxis: { categories:["Mon","Tue","Wed","Thu","Fri","Sat","Sun"] }
    };
    new ApexCharts(document.querySelector("#habitChart"), habitOptions).render();
    <?php endif; ?>
  </script>
</body>
</html>
