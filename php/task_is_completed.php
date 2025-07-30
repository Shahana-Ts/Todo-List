<?php
// filepath: c:\xampp\htdocs\Todo list\php\task_is_complete.php
session_start();
include("../db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id AND is_completed = 1 ORDER BY completed_at DESC");
$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
echo json_encode($tasks);
$conn->close();
?>