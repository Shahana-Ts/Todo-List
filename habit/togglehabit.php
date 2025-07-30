<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

$user_id = $_SESSION['user_id'];
$habit_id = $_POST['habit_id'] ?? null;
$date = $_POST['date'] ?? date("Y-m-d");

if (!$habit_id || !$date) {
  echo json_encode(["status" => "error", "message" => "Invalid data"]);
  exit();
}

// Check if already marked
$stmt = $conn->prepare("SELECT id FROM habit_tracker WHERE user_id = ? AND habit_id = ? AND date = ?");
$stmt->bind_param("iis", $user_id, $habit_id, $date);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  // Already marked, delete
  $stmt = $conn->prepare("DELETE FROM habit_tracker WHERE user_id = ? AND habit_id = ? AND date = ?");
  $stmt->bind_param("iis", $user_id, $habit_id, $date);
  $stmt->execute();
} else {
  // Not marked yet, insert
  $stmt = $conn->prepare("INSERT INTO habit_tracker (user_id, habit_id, date) VALUES (?, ?, ?)");
  $stmt->bind_param("iis", $user_id, $habit_id, $date);
  $stmt->execute();
}

echo json_encode(["status" => "success"]);
?>
