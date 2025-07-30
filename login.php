<?php
session_start();
include("db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    body {
      background: linear-gradient(to right, #0d6efd, #6f42c1);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .login-card {
      background: #1e1e2e;
      padding: 2rem;
      border-radius: 1rem;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.5);
    }
    .show-password {
      cursor: pointer;
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
    }
    .form-control, .btn {
      border-radius: 0.5rem;
    }
    a {
      color: #ffc107;
    }
  </style>
</head>
<body>
  <div class="login-card animate__animated animate__fadeIn">
    <h3 class="text-center mb-4"><i class="fa-solid fa-user-lock"></i> Login to Your Account</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger animate__animated animate__shakeX">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input 
          name="email" 
          type="email" 
          class="form-control" 
          placeholder="Email" 
          required 
          autocomplete="off">
      </div>
      <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <input 
          name="password" 
          id="password" 
          type="password" 
          class="form-control" 
          placeholder="Password" 
          required 
          autocomplete="new-password">
        <span class="show-password" onclick="togglePassword()">
          <i class="fa-solid fa-eye" id="eyeIcon"></i>
        </span>
      </div>
      <button type="submit" class="btn btn-warning w-100">
        <i class="fa-solid fa-right-to-bracket"></i> Login
      </button>
      <div class="text-center mt-3">
        <a href="registration.php">Don't have an account? Register</a>
      </div>
    </form>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById('password');
      const icon = document.getElementById('eyeIcon');
      if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        pwd.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
