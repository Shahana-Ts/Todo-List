<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$task_name = trim($_POST['task_name'] ?? '');
$due_date = $_POST['due_date'] ?? null;
$description = trim($_POST['description'] ?? '');
$priority = $_POST['priority'] ?? 'low';
$category = $_POST['category'] ?? 'personal';

if (empty($task_name)) {
    echo json_encode(["status" => "error", "message" => "Task name is required"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, due_date, description, priority, category) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $task_name, $due_date, $description, $priority, $category);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
$stmt->close();
$conn->close();
