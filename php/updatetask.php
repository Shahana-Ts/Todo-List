<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, priority=?, due_date=?, category=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $description, $priority, $due_date, $category, $task_id);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
}
?>
