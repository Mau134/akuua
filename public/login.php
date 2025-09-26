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
        $redirect = $_GET['redirect'] ?? '../index.php';
        $add_id   = $_GET['add'] ?? null;

        // If login came from Add-to-Cart, add the item
        if ($add_id) {
            if (!isset($_SESSION['cart'][$add_id])) {
                $_SESSION['cart'][$add_id] = 1;
            } else {
                $_SESSION['cart'][$add_id]++;
            }
        }

        header("Location: $redirect");
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
