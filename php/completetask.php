<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
file_put_contents(__DIR__ . '/debug.log', print_r($_POST, true), FILE_APPEND);
include("../db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

$stmt = $conn->prepare("UPDATE tasks SET is_completed = 1, completed_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
$stmt->close();
$conn->close();
?>
