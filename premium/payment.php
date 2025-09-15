<?php
session_start();
require_once "../Db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$error = "";
$success = false;

if (isset($_POST['pay'])) {
    $card_number  = trim($_POST['card_number']);
    $card_name    = trim($_POST['card_name']);
    $expiry_month = trim($_POST['expiry_month']);
    $expiry_year  = trim($_POST['expiry_year']);
    $cvv          = trim($_POST['cvv']);

    // ✅ Check against the single global card in DB
    $stmt = $conn->prepare("
        SELECT * FROM cards 
        WHERE card_number=? AND card_name=? AND expiry_month=? AND expiry_year=? AND cvv=? 
        LIMIT 1
    ");
    $stmt->bind_param("sssss", $card_number, $card_name, $expiry_month, $expiry_year, $cvv);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ✅ Upgrade user to premium
        $conn->query("UPDATE users SET is_premium=1 WHERE id=$userId");
        $_SESSION['is_premium'] = 1;

        // ✅ Log purchase
        $amount = 199.00;
        $pstmt = $conn->prepare("INSERT INTO premium_purchases (user_id, amount) VALUES (?, ?)");
        $pstmt->bind_param("id", $userId, $amount);
        $pstmt->execute();

        $success = true;
    } else {
        $error = "❌ Invalid card details. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4 shadow" style="max-width:400px;">
    <h2 class="text-center text-success">Payment Details 💳</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">🎉 Payment successful! You are now Premium.</div>
      <a href="../home.php" class="btn btn-success w-100">Go to Dashboard</a>

    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label>Card Number</label>
          <input type="text" name="card_number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Card Holder</label>
          <input type="text" name="card_name" class="form-control" required>
        </div>
        <div class="row mb-3">
          <div class="col"><input type="text" name="expiry_month" class="form-control" placeholder="MM" required></div>
          <div class="col"><input type="text" name="expiry_year" class="form-control" placeholder="YYYY" required></div>
        </div>
        <div class="mb-3">
          <input type="password" name="cvv" class="form-control" placeholder="CVV" required>
        </div>
        <button type="submit" name="pay" class="btn btn-primary w-100">Pay ₹199</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
