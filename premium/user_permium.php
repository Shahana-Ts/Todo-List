<?php
session_start();
require_once "Db.php"; // adjust path to your DB connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// If upgrade button clicked
if (isset($_POST['upgrade'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_premium = 1 WHERE id = ?");
    $stmt->execute([$userId]);

    $_SESSION['is_premium'] = 1; // update session too
    $message = "🎉 Congratulations! You are now a Premium Member!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upgrade to Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary">Upgrade to Premium 🚀</h2>
        <p class="text-center">Unlock powerful features to boost your productivity:</p>
        <ul class="list-group mb-4">
            <li class="list-group-item">📝 Notes & Journal</li>
            <li class="list-group-item">⏳ Focus Mode (Pomodoro Timer)</li>
            <li class="list-group-item">📊 Analytics Dashboard</li>
            <li class="list-group-item">🎮 Gamification (XP, Levels, Badges)</li>
            <li class="list-group-item">🎨 Custom Themes</li>
        </ul>

        <?php if (isset($message)): ?>
            <div class="alert alert-success text-center">
                <?= $message ?>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-success">Go to Dashboard</a>
            </div>
        <?php elseif ($_SESSION['is_premium'] == 1): ?>
            <div class="alert alert-info text-center">
                ✅ You are already a Premium Member!
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        <?php else: ?>
            <form method="POST" class="text-center">
                <button type="submit" name="upgrade" class="btn btn-warning btn-lg">
                    Upgrade Now
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
