<?php
session_start();
require_once "../config/db.php";

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Login success
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

// Redirect target
$redirect = $_GET['redirect'] ?? '/index.php';  // always points to root index
$add_id   = $_GET['add'] ?? null;

// If login came from Add-to-Cart, add the item
if ($add_id) {
    if (!isset($_SESSION['cart'][$add_id])) {
        $_SESSION['cart'][$add_id] = 1;
    } else {
        $_SESSION['cart'][$add_id]++;
    }
}

// Ensure absolute redirect
header("Location: " . $redirect);
exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Login - Akuua</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
      min-height: 100vh;
      font-family: 'Segoe UI', Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-container {
      max-width: 400px;
      width: 100%;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      padding: 36px 32px 28px 32px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      backdrop-filter: blur(2px);
    }
    .login-container h3 {
      font-weight: 600;
      color: #2d3a4b;
      margin-bottom: 28px;
    }
    .form-label {
      font-weight: 500;
      color: #34495e;
    }
    .form-control {
      border-radius: 8px;
      border: 1px solid #d1d5db;
      padding: 10px 12px;
      font-size: 1rem;
      background: #f8fafc;
      transition: border-color 0.2s;
    }
    .form-control:focus {
      border-color: #0d6efd;
      background: #fff;
      box-shadow: 0 0 0 2px rgba(13,110,253,0.08);
    }
    .btn-primary {
      border-radius: 8px;
      font-weight: 600;
      letter-spacing: 0.5px;
      background: linear-gradient(90deg, #0d6efd 60%, #4f8cff 100%);
      border: none;
      transition: background 0.2s;
    }
    .btn-primary:hover {
      background: linear-gradient(90deg, #2563eb 60%, #60a5fa 100%);
    }
    .alert-danger {
      border-radius: 8px;
      font-size: 0.97rem;
      margin-bottom: 18px;
    }
    .mt-3.text-center {
      font-size: 0.97rem;
      color: #6b7280;
    }
    .mt-3.text-center a {
      color: #0d6efd;
      text-decoration: none;
      font-weight: 500;
    }
    .mt-3.text-center a:hover {
      text-decoration: underline;
    }
    @media (max-width: 500px) {
      .login-container {
        padding: 20px 8px 16px 8px;
        border-radius: 10px;
      }
    }
  </style>
</head>
<body>
<div class="login-container" style="max-width:400px;margin:80px auto;padding:30px;background:#fff;border-radius:12px;">
  <h3 class="text-center mb-4">Log In</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Log In</button>
  </form>

  <div class="mt-3 text-center">
    Don’t have an account? <a href="register.php">Register</a>
  </div>
</div>
</body>
</html>
