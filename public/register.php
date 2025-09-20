<?php
session_start();
require_once "../config/db.php"; // adjust if path differs

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $message = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email is already registered!";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Default role = customer
            $role = "customer";

            $stmt = $conn->prepare("INSERT INTO user (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $username, $email, $hashed, $role);

            if ($stmt->execute()) {
                $_SESSION["success"] = "Registration successful! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Error: Could not register. Try again.";
            }
        }
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
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h3 class="text-center mb-4">Create Account</h3>

          <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form method="POST" action="">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
          </form>

          <div class="mt-3 text-center">
            Already have an account? <a href="login.php">Log In</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
<?php include "../includes/footer.php"; ?>
