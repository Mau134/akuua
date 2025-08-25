<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $pass = $_POST['password'];
  $result = $conn->query("SELECT * FROM users WHERE email='$email'");
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($pass, $user['password_hash'])) {
      $_SESSION['admin'] = $user['id'];
      header("Location: dashboard.php");
      exit;
    }
  }
  $error = "Invalid login!";
}
include "../includes/header.php";
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow p-4">
      <h3 class="text-center mb-3">Admin Login</h3>
      <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <form method="post" action="process_login.php">
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
<?php include "../includes/footer.php"; ?>
