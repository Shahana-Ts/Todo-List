<?php
session_start();
require_once __DIR__ . '/../Db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $habit_id = $input['habit_id'] ?? null;
    $date = $input['date'] ?? null;
    $action = $input['action'] ?? null; // 'add' or 'remove'
    
    try {
        // Verify habit belongs to user
        $stmt = $pdo->prepare("SELECT id FROM habits WHERE id = ? AND user_id = ?");
        $stmt->execute([$habit_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Habit not found or access denied");
        }
        
        if ($action === 'add') {
            // Insert or ignore if already exists
            $stmt = $pdo->prepare("INSERT IGNORE INTO habit_logs (habit_id, log_date) VALUES (?, ?)");
            $stmt->execute([$habit_id, $date]);
            $message = "Habit tracked successfully for " . date('M j, Y', strtotime($date));
        } else {
            $stmt = $pdo->prepare("DELETE FROM habit_logs WHERE habit_id = ? AND log_date = ?");
            $stmt->execute([$habit_id, $date]);
            $message = "Habit untracked for " . date('M j, Y', strtotime($date));
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}