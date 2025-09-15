<?php
// db.php
$host = 'localhost';
$dbname = 'todo'; // Changed from 'todo' to 'todo_db'
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// mysqli connection (not typically needed if using PDO, but included per user prompt)
$conn = new mysqli("localhost", "root", "", "todo"); // Change to your DB info
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>