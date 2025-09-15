<?php
session_start();
require_once "../Db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$is_premium = $_SESSION['is_premium'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upgrade to Premium</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4 shadow" style="max-width:400px;">
    <h2 class="text-center text-primary">Upgrade to Premium 🚀</h2>
    <p class="text-muted text-center">Unlock Notes, Focus Mode, Habits, and more</p>

    <?php if ($is_premium == 1): ?>
      <div class="alert alert-info">✅ You are already Premium!</div>
      <a href="../home.php" class="btn btn-primary w-100">Go to Dashboard</a>
    <?php else: ?>
      <h3 class="fw-bold text-center">Only ₹199 <small class="text-muted">/ lifetime</small></h3>
      <form action="payment.php" method="GET">
        <button type="submit" class="btn btn-warning w-100">Buy Now</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
