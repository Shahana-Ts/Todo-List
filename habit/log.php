<?php
require_once '../Db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    if ($data['completed']) {
        $stmt = $pdo->prepare("INSERT INTO habit_logs (habit_id, log_date) VALUES (?, ?)
                              ON DUPLICATE KEY UPDATE completed = VALUES(completed)");
        $stmt->execute([$data['habit_id'], $data['date']]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM habit_logs WHERE habit_id = ? AND log_date = ?");
        $stmt->execute([$data['habit_id'], $data['date']]);
    }
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>