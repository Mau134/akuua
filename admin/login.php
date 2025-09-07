<?php
session_start();
require_once "../config/db.php";

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $pass  = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email' LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
//password verify and role check
if (password_verify($pass, $user['password']) && $user['role'] === 'admin') {
    $_SESSION['role'] = 'admin';
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_email'] = $user['email'];
    header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials or not an admin!";
        }
    } else {
        $error = "Invalid email!";
    }
}
include "./includes/header.php";
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow p-4">
      <h3 class="text-center mb-3">Admin Login</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Login</button>
      </form>
    </div>
  </div>
</div>
<?php include "./includes/footer.php"; ?>
