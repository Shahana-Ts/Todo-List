<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $task = $_POST['task'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO tasks (task_name, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $task, $user_id);
    $stmt->execute();
}
?>
