<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
  echo json_encode([]);
  exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM habit WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$habits = [];
while ($row = $result->fetch_assoc()) {
  $habits[] = $row;
}

echo json_encode($habits);
?>
