<?php
require_once '../Db.php';

header('Content-Type: application/json');

$habitId = (int)$_GET['habit_id'];
$currentStreak = 0;

// Calculate current streak (simplified example)
$stmt = $pdo->prepare("SELECT log_date FROM habit_logs 
                      WHERE habit_id = ? 
                      ORDER BY log_date DESC");
$stmt->execute([$habitId]);

$today = new DateTime();
$yesterday = clone $today;
$yesterday->modify('-1 day');

while ($log = $stmt->fetch()) {
    $logDate = new DateTime($log['log_date']);
    
    if ($logDate->format('Y-m-d') === $today->format('Y-m-d')) {
        $currentStreak++;
        $today->modify('-1 day');
    } elseif ($logDate->format('Y-m-d') === $yesterday->format('Y-m-d')) {
        $currentStreak++;
        $yesterday->modify('-1 day');
    } else {
        break;
    }
}

echo json_encode(['current_streak' => $currentStreak]);
?>