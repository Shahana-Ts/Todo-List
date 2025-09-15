<?php
session_start();
require_once "Db.php";

// Only admin can access (assume admin login sets $_SESSION['is_admin'])
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Handle Approve/Reject
if (isset($_GET['approve'])) {
    $paymentId = $_GET['approve'];

    // Update payment status
    $pdo->prepare("UPDATE payments SET status='approved' WHERE id=?")->execute([$paymentId]);

    // Upgrade user
    $stmt = $pdo->prepare("SELECT user_id FROM payments WHERE id=?");
    $stmt->execute([$paymentId]);
    $userId = $stmt->fetchColumn();

    $pdo->prepare("UPDATE users SET is_premium=1 WHERE id=?")->execute([$userId]);
}
if (isset($_GET['reject'])) {
    $paymentId = $_GET['reject'];
    $pdo->prepare("UPDATE payments SET status='rejected' WHERE id=?")->execute([$paymentId]);
}

// Fetch all payments
$stmt = $pdo->query("SELECT p.id, u.username, p.amount, p.status, p.payment_date 
                     FROM payments p JOIN users u ON p.user_id=u.id ORDER BY p.payment_date DESC");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Premium Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-4">💼 Admin - Premium Requests</h2>
    <table class="table table-bordered table-hover bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $pay): ?>
            <tr>
                <td><?= $pay['id'] ?></td>
                <td><?= $pay['username'] ?></td>
                <td><?= $pay['amount'] ?></td>
                <td><?= ucfirst($pay['status']) ?></td>
                <td><?= $pay['payment_date'] ?></td>
                <td>
                    <?php if ($pay['status'] == 'pending'): ?>
                        <a href="?approve=<?= $pay['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                        <a href="?reject=<?= $pay['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                    <?php else: ?>
                        <span class="badge bg-secondary">No Action</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
