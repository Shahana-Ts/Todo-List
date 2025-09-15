<?php
$is_premium = $_SESSION['is_premium'] ?? 0;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="home.php">QuickList</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Tasks</a></li>
        <li class="nav-item"><a class="nav-link" href="habit/habitIndex.php">Habits</a></li>
        <?php if ($is_premium): ?>
          <li class="nav-item"><a class="nav-link" href="habit/focus.php">Focus</a></li>
          <li class="nav-item"><a class="nav-link" href="habit/analytics.php">Analytics</a></li>
          <li class="nav-item"><a class="nav-link" href="premium/notes.php">Journal</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link text-warning" href="premium/premium.php">Upgrade ⭐</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
