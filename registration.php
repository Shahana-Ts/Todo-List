<?php
include("db.php");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $success = "Registration successful! <a href='login.php' class='text-decoration-underline'>Login here</a>.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #007bff, #6f42c1);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .register-card {
      background: #1f1f2e;
      padding: 2rem;
      border-radius: 1rem;
      width: 100%;
      max-width: 450px;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.3);
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
  <div class="register-card animate__animated animate__fadeIn">
    <h3 class="text-center mb-4"><i class="fa-solid fa-user-plus"></i> Create Your Account</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger animate__animated animate__shakeX">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success animate__animated animate__fadeIn">
        <i class="fa-solid fa-circle-check"></i> <?= $success ?>
      </div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <input name="password" id="password" type="password" class="form-control" placeholder="Password" required>
        <span class="show-password" onclick="togglePassword()">
          <i class="fa-solid fa-eye" id="eyeIcon"></i>
        </span>
      </div>
      <button type="submit" class="btn btn-warning w-100">
        <i class="fa-solid fa-user-plus"></i> Register
      </button>
      <div class="text-center mt-3">
        <span>Already have an account?</span> <a href="login.php">Login</a>
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
