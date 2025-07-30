<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

$user_id = $_SESSION['user_id'];
$habit_name = $_POST['habit_name'] ?? '';
$frequency = $_POST['frequency'] ?? '';
$start_date = $_POST['start_date'] ?? '';

if (empty($habit_name) || empty($frequency) || empty($start_date)) {
  echo json_encode(["status" => "error", "message" => "All fields are required"]);
  exit();
}

$stmt = $conn->prepare("INSERT INTO habit (user_id, habit_name, frequency, start_date, is_active) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("isss", $user_id, $habit_name, $frequency, $start_date);

if ($stmt->execute()) {
  echo json_encode(["status" => "success"]);
} else {
  echo json_encode(["status" => "error", "message" => "DB error"]);
}
?>
