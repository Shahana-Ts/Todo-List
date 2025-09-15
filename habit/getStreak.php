<?php
session_start();
require_once __DIR__ . '/../Db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

header('Content-Type: application/json');

if (isset($_GET['habit_id'])) {
    $habit_id = $_GET['habit_id'];
    
    try {
        // Verify the habit belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM habits WHERE id = ? AND user_id = ?");
        $stmt->execute([$habit_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Habit not found");
        }
        
        // Calculate streak
        $stmt = $pdo->prepare("SELECT log_date FROM habit_logs WHERE habit_id = ? ORDER BY log_date DESC");
        $stmt->execute([$habit_id]);
        $logs = $stmt->fetchAll();
        
        $streak = 0;
        $currentDate = new DateTime();
        $lastLogDate = !empty($logs) ? new DateTime($logs[0]['log_date']) : null;
        
        if ($lastLogDate) {
            $diff = $currentDate->diff($lastLogDate);
            if ($diff->days <= 1) {
                $streak = 1;
                $checkDate = clone $lastLogDate;
                
                foreach ($logs as $log) {
                    $logDate = new DateTime($log['log_date']);
                    if ($logDate->format('Y-m-d') == $checkDate->format('Y-m-d')) {
                        continue;
                    }
                    
                    $checkDate->modify('-1 day');
                    if ($logDate->format('Y-m-d') == $checkDate->format('Y-m-d')) {
                        $streak++;
                    } else {
                        break;
                    }
                }
            }
        }
        
        echo json_encode(['success' => true, 'streak' => $streak]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
?>