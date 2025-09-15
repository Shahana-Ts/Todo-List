<?php
session_start();
require_once __DIR__ . '/../Db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$currentMonth = date('Y-m');

// Add new habit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO habits (user_id, name, frequency, icon, target_days, description) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $user_id,
            $_POST['name'],
            $_POST['frequency'],
            $_POST['icon'] ?? 'seedling',
            $_POST['target_days'] ?? 30,
            $_POST['description'] ?? ''
        ]);

        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['flash'] = "Insert failed: " . print_r($errorInfo, true);
        } else {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Habit added successfully!'];
            header("Location: habitIndex.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error adding habit: ' . $e->getMessage()];
    }
}

// Delete habit
if (isset($_GET['delete'])) {
    try {
        $habit_id = $_GET['delete'];

        // First delete related logs
        $pdo->prepare("DELETE FROM habit_logs WHERE habit_id = ?")->execute([$habit_id]);

        // Then delete the habit
        $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
        $stmt->execute([$habit_id, $user_id]);

        $_SESSION['message'] = ['type' => 'success', 'text' => 'Habit deleted successfully!'];
        header("Location: habitIndex.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting habit: ' . $e->getMessage()];
    }
}

// Get user's habits
$stmt = $pdo->prepare("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$habits = $stmt->fetchAll();

// Helper: Calculate streak
function calculateStreak($pdo, $habitId) {
    $stmt = $pdo->prepare("SELECT log_date FROM habit_logs WHERE habit_id = ? ORDER BY log_date DESC");
    $stmt->execute([$habitId]);
    $logs = $stmt->fetchAll();

    if (empty($logs)) return 0;

    $streak = 0;
    $currentDate = new DateTime();
    $lastLogDate = new DateTime($logs['log_date']);

    $diff = $currentDate->diff($lastLogDate);
    if ($diff->days <= 1) {
        $streak = 1;
        $checkDate = clone $lastLogDate;

        foreach ($logs as $log) {
            $logDate = new DateTime($log['log_date']);
            if ($logDate->format('Y-m-d') == $checkDate->format('Y-m-d')) continue;
            $checkDate->modify('-1 day');
            if ($logDate->format('Y-m-d') == $checkDate->format('Y-m-d')) {
                $streak++;
            } else {
                break;
            }
        }
    }

    return $streak;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Habit Tracker | QuickList</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" /> <!-- Inter & Space Grotesk [18][16] -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" /> <!-- Bootstrap components base [12] -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />

    <style>
        :root{
            --primary-gradient: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            --success-gradient: linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);
            --warning-gradient: linear-gradient(135deg,#43e97b 0%,#38f9d7 100%);
            --danger-gradient: linear-gradient(135deg,#fa709a 0%,#fee140 100%);
            --glass-bg: rgba(255,255,255,0.25);
            --glass-border: rgba(255,255,255,0.18);
            --shadow: rgba(31,38,135,0.37);
            --text: #0f172a;
        }
        html,body{height:100%}
        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            background-attachment: fixed;
            color: var(--text);
        }
        body::before{
            content:"";
            position:fixed; inset:0;
            background-image:
              radial-gradient(circle at 20% 80%, rgba(120,119,198,.28) 0%, transparent 50%),
              radial-gradient(circle at 80% 20%, rgba(255,119,198,.28) 0%, transparent 50%),
              radial-gradient(circle at 40% 40%, rgba(120,219,255,.28) 0%, transparent 50%);
            pointer-events:none; z-index:-1;
        }
        .glass-card{
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border:1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 var(--shadow), inset 0 1px 0 rgba(255,255,255,.3);
            transition: transform .35s ease, box-shadow .35s ease;
            position:relative; overflow:hidden;
        }
        .glass-card::before{
            content:""; position:absolute; left:0; right:0; top:0; height:3px; background: var(--primary-gradient);
        }
        .glass-card:hover{ transform: translateY(-8px); box-shadow: 0 20px 40px rgba(31,38,135,.4), inset 0 1px 0 rgba(255,255,255,.4); }
        .premium-header{ text-align:center; margin: 3rem 0 2rem; }
        .premium-header h1{
            font-family: "Space Grotesk", Inter, sans-serif; font-weight:700; font-size:3rem; margin-bottom:.5rem;
            background: linear-gradient(135deg,#fff,#f8fafc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
            text-shadow: 0 4px 8px rgba(0,0,0,.12);
        }
        .premium-header .subtitle{ color: rgba(255,255,255,.9) }

        .habit-card{ border-radius:24px; padding:2rem }
        .habit-card.success::before{ background: var(--success-gradient) }
        .habit-card.warning::before{ background: var(--warning-gradient) }
        .habit-card.danger::before{ background: var(--danger-gradient) }

        .habit-icon{
            width:64px; height:64px; border-radius:20px; display:flex; align-items:center; justify-content:center;
            background: var(--primary-gradient); color:#fff; box-shadow: 0 8px 16px rgba(99,102,241,.35), inset 0 1px 0 rgba(255,255,255,.3);
            position:relative; overflow:hidden;
        }
        .habit-icon::after{
            content:""; position:absolute; inset:0; transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
            animation: shimmer 2s infinite linear;
        }
        @keyframes shimmer{ 0%{transform: translateX(-100%)} 100%{transform: translateX(100%)} }

        .habit-title{ font-family: "Space Grotesk", Inter, sans-serif; font-weight:600; font-size:1.35rem }
        .habit-description{ color: rgba(15,23,42,.7); font-size:.95rem }

        .badge-premium{
            background: var(--primary-gradient); color:#fff; border-radius:10px; padding:.35rem .6rem; font-weight:600; font-size:.8rem;
            box-shadow: 0 2px 8px rgba(99,102,241,.35);
        }

        .streak-display{
            text-align:center; padding:1.25rem; border-radius:16px; border:1px solid rgba(255,255,255,.25);
            background: rgba(255,255,255,.12); margin-bottom:1rem;
        }
        .streak-number{
            font-family:"Space Grotesk", Inter, sans-serif; font-weight:700; font-size:2.25rem;
            background: var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .streak-label{ color: rgba(15,23,42,.7); font-weight:600; letter-spacing:.3px; font-size:.85rem }

        .progress-premium{ height:8px; background: rgba(255,255,255,.22); border-radius:10px; overflow:hidden }
        .progress-bar-premium{
            height:100%; background: var(--success-gradient); border-radius:10px; position:relative;
        }
        .progress-bar-premium::before{
            content:""; position:absolute; inset:0; transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
            animation: shimmer 2s infinite linear;
        }

        .calendar-grid{ display:grid; grid-template-columns: repeat(7,1fr); gap:.5rem }
        .calendar-day{
            aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:12px;
            background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25);
            font-weight:600; color:#0f172a; cursor:pointer; transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .calendar-day:hover{ transform: scale(1.06); background: rgba(99,102,241,.18); border-color: rgba(99,102,241,.4) }
        .calendar-day.completed{ background: var(--success-gradient); color:#fff; box-shadow: 0 4px 12px rgba(16,185,129,.35) }

        .btn-premium{
            background: var(--primary-gradient); border:none; color:#fff; font-weight:700; border-radius:14px; padding:.9rem 1.2rem;
            box-shadow: 0 8px 22px rgba(99,102,241,.4); transition: transform .25s ease, box-shadow .25s ease;
        }
        .btn-premium:hover{ transform: translateY(-2px); box-shadow: 0 12px 28px rgba(99,102,241,.5) }
        .btn-success-premium{ background: var(--success-gradient) }

        .btn-float{
            position:fixed; bottom:2rem; right:2rem; width:70px; height:70px; border-radius:50%;
            display:flex; align-items:center; justify-content:center; border:none; background: var(--primary-gradient); color:#fff;
            box-shadow: 0 12px 32px rgba(99,102,241,.45); z-index:1000; animation: pulse 2s infinite; transition: transform .25s ease;
        }
        .btn-float:hover{ transform: scale(1.08) translateY(-4px) }
        @keyframes pulse { 0%{box-shadow:0 12px 32px rgba(99,102,241,.45),0 0 0 0 rgba(99,102,241,.45)} 70%{box-shadow:0 12px 32px rgba(99,102,241,.45),0 0 0 12px rgba(99,102,241,0)} 100%{box-shadow:0 12px 32px rgba(99,102,241,.45),0 0 0 0 rgba(99,102,241,0)} }

        .delete-btn{
            position:absolute; top:1rem; right:1rem; width:40px; height:40px; border-radius:12px; border:none; display:flex; align-items:center; justify-content:center;
            background: var(--danger-gradient); color:#fff; box-shadow:0 8px 18px rgba(244,63,94,.35); opacity:0; transition: opacity .25s ease, transform .25s ease;
        }
        .habit-card:hover .delete-btn{ opacity:1 }
        .delete-btn:hover{ transform: scale(1.06) }

        .modal-content{ background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border:1px solid var(--glass-border); border-radius:22px }
        .form-control,.form-select{ background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); color: #0f172a; border-radius:12px; padding:.85rem 1rem }
        .form-control:focus,.form-select:focus{ border-color: rgba(99,102,241,.5); box-shadow: 0 0 0 3px rgba(99,102,241,.15) }

        .icon-selector{ display:grid; grid-template-columns: repeat(5,1fr); gap:.6rem }
        .icon-option{
            aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:12px; cursor:pointer;
            background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); color:#0f172a; transition: transform .2s ease, background .2s ease;
        }
        .icon-option:hover,.icon-option.selected{ background: var(--primary-gradient); color:#fff; transform: scale(1.06) }

        @media (max-width: 768px){
            .premium-header h1{ font-size:2.4rem }
            .btn-float{ width:60px; height:60px; bottom:1rem; right:1rem }
            .icon-selector{ grid-template-columns: repeat(4,1fr) }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../navbar.php'; ?>

<div class="container py-5">
    <div class="premium-header">
        <h1><i class="fas fa-seedling me-2" style="color:#10b981"></i>Habit Tracker</h1>
        <p class="subtitle">Build consistency and track your daily progress</p>
    </div>

    <button class="btn-float" data-bs-toggle="modal" data-bs-target="#addHabitModal" title="Add New Habit">
        <i class="fas fa-plus"></i>
    </button>

    <div class="row g-4">
        <?php if (!empty($habits)): ?>
            <?php foreach ($habits as $i => $habit):
                $streak = calculateStreak($pdo, $habit['id']);
                $target = $habit['target_days'];
                $progress = min(100, ($streak / $target) * 100);
                $card_class = ($progress>=100)?'success':(($progress>=50)?'warning':'danger');

                $daysInMonth = date('t');
                $completedDates = [];
                $stmt = $pdo->prepare("SELECT log_date FROM habit_logs WHERE habit_id = ? AND log_date LIKE ?");
                $stmt->execute([$habit['id'], date('Y-m') . '%']);
                while ($log = $stmt->fetch()) { $completedDates[] = $log['log_date']; }

                $today = date('Y-m-d');
                $isTodayCompleted = in_array($today, $completedDates);
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="glass-card habit-card <?= $card_class ?> h-100" data-habit="<?= $habit['id'] ?>">
                    <button class="delete-btn" onclick="confirmDelete(<?= $habit['id'] ?>)" title="Delete Habit">
                        <i class="fas fa-trash"></i>
                    </button>

                    <div class="d-flex align-items-start mb-3">
                        <div class="habit-icon me-3">
                            <i class="fas fa-<?= htmlspecialchars($habit['icon'] ?? 'seedling') ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="habit-title mb-1"><?= htmlspecialchars($habit['name']) ?></h4>
                            <?php if (!empty($habit['description'])): ?>
                                <p class="habit-description mb-2"><?= htmlspecialchars($habit['description']) ?></p>
                            <?php endif; ?>
                            <span class="badge-premium"><?= ucfirst($habit['frequency']) ?></span>
                        </div>
                    </div>

                    <div class="streak-display">
                        <div class="streak-number"><?= $streak ?></div>
                        <div class="streak-label">Day Streak</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Progress</span>
                            <span class="small text-muted"><?= number_format($progress,1) ?>%</span>
                        </div>
                        <div class="progress-premium">
                            <div class="progress-bar-premium" style="width: <?= $progress ?>%"></div>
                        </div>
                        <div class="text-center mt-1">
                            <small class="text-muted">Target: <?= $target ?> days</small>
                        </div>
                    </div>

                    <button class="btn <?= $isTodayCompleted ? 'btn-success-premium' : 'btn-premium' ?> w-100 mb-3 track-today-btn"
                            onclick="trackHabitToday(<?= $habit['id'] ?>, this)">
                        <i class="fas fa-<?= $isTodayCompleted ? 'check' : 'plus' ?> me-2"></i>
                        <?= $isTodayCompleted ? 'Completed Today' : 'Track Today' ?>
                    </button>

                    <div class="calendar-grid mb-3">
                        <?php for ($day=1; $day<=$daysInMonth; $day++):
                            $date = date('Y-m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $isCompleted = in_array($date, $completedDates);
                        ?>
                        <div class="calendar-day <?= $isCompleted ? 'completed' : '' ?>"
                             data-date="<?= $date ?>" data-habit="<?= $habit['id'] ?>"
                             title="<?= $isCompleted ? 'Completed' : 'Not completed' ?> - <?= date('M j', strtotime($date)) ?>">
                            <?= $day ?>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-calendar-plus me-1"></i><?= date('M j, Y', strtotime($habit['created_at'])) ?></small>
                        <a href="editHabit.php?id=<?= $habit['id'] ?>" class="btn btn-sm" style="background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); color:#0f172a; border-radius:10px;">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="glass-card p-5 text-center">
                    <i class="fas fa-seedling text-white-50" style="font-size:3rem;"></i>
                    <h3 class="mt-3" style="font-family: 'Space Grotesk', Inter, sans-serif;">Start Your Journey</h3>
                    <p class="text-white-75">Create your first habit and begin building momentum.</p>
                    <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#addHabitModal">
                        <i class="fas fa-plus me-2"></i> Create Your First Habit
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Habit Modal -->
<div class="modal fade" id="addHabitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0">
      <div class="modal-header border-0">
        <h5 class="modal-title" style="font-family:'Space Grotesk',Inter,sans-serif;"><i class="fas fa-plus-circle me-2" style="color:#6366f1;"></i>Create New Habit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
      </div>
      <form method="POST" id="habitForm">
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label"><i class="fas fa-tag me-1"></i> Habit Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Drink 8 glasses of water" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-repeat me-1"></i> Frequency *</label>
                    <select name="frequency" class="form-select" required>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-target me-1"></i> Target Days *</label>
                    <input type="number" name="target_days" class="form-control" value="30" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fas fa-icons me-1"></i> Select Icon</label>
                    <div class="icon-selector">
                        <?php
                        $icons = ['seedling','dumbbell','book-open','running','bed','apple-alt','glass-whiskey','smile','moon','sun','heart','music','gamepad','coffee','bicycle'];
                        foreach ($icons as $idx => $icon):
                        ?>
                        <div class="icon-option <?= $idx===0 ? 'selected' : '' ?>" data-icon="<?= $icon ?>" title="<?= ucfirst(str_replace('-', ' ', $icon)) ?>">
                            <i class="fas fa-<?= $icon ?>"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="icon" id="selectedIcon" value="seedling">
                </div>
                <div class="col-12">
                    <label class="form-label"><i class="fas fa-align-left me-1"></i> Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Add a motivational description for your habit..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn" style="background: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); color:#0f172a; border-radius:12px;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-premium"><i class="fas fa-plus me-2"></i>Create Habit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Icon selection
document.querySelectorAll('.icon-option').forEach(icon=>{
    icon.addEventListener('click',function(){
        document.querySelectorAll('.icon-option').forEach(i=>i.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('selectedIcon').value = this.dataset.icon;
        if (navigator.vibrate) navigator.vibrate(30);
    });
});

// Track Today with optimistic UI
async function trackHabitToday(habitId, button) {
    const today = new Date().toISOString().split('T');
    const isCompleted = button.classList.contains('btn-success-premium');
    const action = isCompleted ? 'remove' : 'add';

    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

    try {
        const res = await fetch('trackHabit.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `habit_id=${habitId}&date=${today}&action=${action}`
        });
        const data = await res.json();

        if (data.success) {
            if (action === 'add') {
                button.classList.remove('btn-premium');
                button.classList.add('btn-success-premium');
                button.innerHTML = '<i class="fas fa-check me-2"></i>Completed Today';
            } else {
                button.classList.remove('btn-success-premium');
                button.classList.add('btn-premium');
                button.innerHTML = '<i class="fas fa-plus me-2"></i>Track Today';
            }

            const todayDay = new Date().getDate();
            document.querySelectorAll(`.calendar-day[data-habit="${habitId}"]`).forEach(day=>{
                if (day.textContent.trim()==todayDay) {
                    day.classList.toggle('completed');
                    if (action==='add'){ day.style.animation='pulse .6s ease-in-out'; setTimeout(()=>day.style.animation='',600); }
                }
            });

            await refreshStreakCount(habitId);
            showToast(data.message,'success');
        } else {
            button.innerHTML = originalHTML;
            showToast(data.message || 'Failed to track habit','warning');
        }
    } catch(e){
        console.error(e);
        button.innerHTML = originalHTML;
        showToast('Network error - please try again','warning');
    } finally{
        button.disabled = false;
    }
}

// Toggle specific day
document.querySelectorAll('.calendar-day').forEach(day=>{
    day.addEventListener('click', async function(){
        const habitId = this.dataset.habit;
        const date = this.dataset.date;
        const isCompleted = this.classList.contains('completed');

        const selectedDate = new Date(date);
        const today = new Date(); today.setHours(0,0,0,0);
        if (selectedDate > today) { showToast('Cannot track future dates','warning'); return; }

        const original = this.textContent;
        this.style.pointerEvents='none';
        this.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:.7rem;"></i>';

        try {
            const res = await fetch('trackHabit.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `habit_id=${habitId}&date=${date}&action=${isCompleted?'remove':'add'}`
            });
            const data = await res.json();

            if (data.success) {
                this.classList.toggle('completed');
                await refreshStreakCount(habitId);
                showToast(data.message,'success');
                if (!isCompleted){ this.style.animation='pulse .6s ease-in-out'; setTimeout(()=>this.style.animation='',600); }
            } else {
                showToast(data.message || 'Failed to track habit','warning');
            }
        } catch(e){
            console.error(e);
            showToast('Network error - please try again','warning');
        } finally{
            this.textContent = original;
            this.style.pointerEvents='auto';
        }
    });
});

// Refresh streak + progress
async function refreshStreakCount(habitId){
    try{
        const res = await fetch(`getStreak.php?habit_id=${habitId}`);
        const data = await res.json();
        if (!data.success) return;

        const card = document.querySelector(`.habit-card[data-habit="${habitId}"]`);
        const streakEl = card.querySelector('.streak-number');
        const current = parseInt(streakEl.textContent);
        if (current !== data.streak){
            streakEl.style.transform='scale(1.15)'; streakEl.style.transition='transform .25s ease';
            setTimeout(()=>{ streakEl.textContent = data.streak; streakEl.style.transform='scale(1)'; },150);
        }

        const targetText = card.querySelector('.text-muted:last-child')?.textContent || '';
        const match = targetText.match(/Target:\s*(\d+)\s*days/i);
        const targetDays = match ? parseInt(match[21]) : 30;
        const progress = Math.min(100, (data.streak/targetDays)*100);
        card.querySelector('.progress-bar-premium').style.width = `${progress}%`;

        card.classList.remove('danger','warning','success');
        if (progress>=100) card.classList.add('success');
        else if (progress>=50) card.classList.add('warning');
        else card.classList.add('danger');
    }catch(e){ console.error(e); }
}

// Toast
function showToast(message,type='info'){
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className='toast show align-items-center border-0 mb-2';
    toast.setAttribute('role','alert');
    toast.setAttribute('aria-live','assertive');
    toast.setAttribute('aria-atomic','true');

    const colorMap={success:'#10b981',warning:'#f59e0b',info:'#6366f1',error:'#f43f5e'};
    const iconMap={success:'check-circle',warning:'exclamation-triangle',info:'info-circle',error:'exclamation-circle'};

    toast.style.background = `linear-gradient(135deg, ${colorMap[type]||colorMap.info}, ${(colorMap[type]||colorMap.info)}dd)`;
    toast.style.color='#fff'; toast.style.borderRadius='12px'; toast.style.boxShadow=`0 8px 32px ${(colorMap[type]||colorMap.info)}44`;

    toast.innerHTML = `
      <div class="d-flex align-items-center p-3">
        <i class="fas fa-${iconMap[type]||iconMap.info} me-2"></i>
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    container.appendChild(toast);
    setTimeout(()=>{ toast.style.opacity='0'; toast.style.transform='translateX(100%)'; setTimeout(()=>toast.remove(),300); }, 3800);
}

// Delete confirm
function confirmDelete(habitId){
    if (confirm('Delete this habit and all tracking data?')) {
        window.location.href='?delete='+habitId;
    }
}

// Form loading state
document.getElementById('habitForm')?.addEventListener('submit', function(){
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
});
</script>
</body>
</html>
