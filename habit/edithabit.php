<?php
session_start();
require_once __DIR__ . '/../Db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$habit_id = $_GET['id'] ?? null;

// Fetch habit to edit
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
$stmt->execute([$habit_id, $user_id]);
$habit = $stmt->fetch();

if (!$habit) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Habit not found'];
    header("Location: habitIndex.php");
    exit();
}

// Update habit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE habits SET 
            name = ?, 
            description = ?, 
            frequency = ?, 
            icon = ?, 
            target_days = ?
            WHERE id = ? AND user_id = ?");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['frequency'],
            $_POST['icon'],
            $_POST['target_days'],
            $habit_id,
            $user_id
        ]);
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Habit updated successfully!'];
        header("Location: habitIndex.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error updating habit: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Habit | QuickList</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f9fafb 0%, #e0e7ff 100%);
            font-family: 'Poppins', sans-serif;
        }
        .glass-card {
            background: rgba(255,255,255,0.85);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(79,70,229,0.10);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(79,70,229,0.08);
        }
        .icon-selector {
            gap: 0.5rem;
        }
        .icon-option {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e0e7ff;
            cursor: pointer;
            font-size: 1.5rem;
            transition: box-shadow 0.2s, background 0.2s;
            border: 2px solid transparent;
        }
        .icon-option.selected, .icon-option:hover {
            background: linear-gradient(135deg, #10b981 0%, #4f46e5 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(16,185,129,0.15);
            border-color: #10b981;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4f46e5 0%, #10b981 100%);
            border: none;
        }
        .btn-outline-secondary {
            border-radius: 30px;
        }
        .form-label {
            font-weight: 500;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../navbar.php'; ?>

    <div class="container py-5">
        <div class="glass-card p-4 mx-auto animate__animated animate__fadeIn" style="max-width: 600px;">
            <h2 class="mb-4 text-center fw-bold text-success">
                <i class="fas fa-edit me-2"></i> Edit Habit
            </h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Habit Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($habit['name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($habit['description'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select">
                        <option value="daily" <?= $habit['frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="weekly" <?= $habit['frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        <option value="monthly" <?= $habit['frequency'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Target Days</label>
                    <input type="number" name="target_days" class="form-control" value="<?= $habit['target_days'] ?>" min="1">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Select Icon</label>
                    <div class="icon-selector d-flex flex-wrap">
                        <?php 
                        $icons = ['seedling', 'dumbbell', 'book', 'running', 'bed', 'apple-alt', 'glass-whiskey', 'smile', 'moon', 'sun'];
                        foreach ($icons as $icon): 
                        ?>
                            <div class="icon-option <?= $habit['icon'] === $icon ? 'selected' : '' ?>" 
                                 data-icon="<?= $icon ?>"
                                 onclick="document.getElementById('selectedIcon').value = '<?= $icon ?>'; 
                                          document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
                                          this.classList.add('selected');">
                                <i class="fas fa-<?= $icon ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="icon" id="selectedIcon" value="<?= $habit['icon'] ?>">
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="habitIndex.php" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Habit</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>