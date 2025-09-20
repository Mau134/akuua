<?php
session_start();
require_once "../config/db.php";

$error = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Ensure this is a customer account
        if ($user['role'] !== 'customer') {
            $error = "This login page is for customers only!";
        } elseif (password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect back to cart if needed
            if (isset($_SESSION['redirect_to_cart'])) {
                unset($_SESSION['redirect_to_cart']);
                header("Location: cart.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No account found with that email.";
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
    }
    .login-container {
      max-width: 400px;
      margin: 80px auto;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .form-control {
      border-radius: 8px;
    }
    .btn-primary {
      border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h3 class="text-center mb-4"><i class="fas fa-user-circle me-2"></i>Customer Login</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>

  <div class="text-center mt-3">
    <small>Don’t have an account? <a href="register.php">Register</a></small>
  </div>
</div>

</body>
</html>
