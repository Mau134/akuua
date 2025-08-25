<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = trim($_POST['email']);
  $pass = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  // Check if passwords match
  if ($pass !== $confirm) {
    $error = "Passwords do not match!";
  } else {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Email already registered!";
    } else {
      // Insert new user
      $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
      $stmt->bind_param("ss", $email, $hashedPassword);

      if ($stmt->execute()) {
        $_SESSION['admin'] = $stmt->insert_id;
        header("Location: dashboard.php");
        exit;
      } else {
        $error = "Something went wrong. Please try again!";
      }
    }
    $stmt->close();
  }
}

include "../includes/header.php";
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow p-4">
      <h3 class="text-center mb-3">Sign Up</h3>
      <form action="process_signup.php" method="post">
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
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="customer">Customer</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submait" class="btn btn-success w-100">Sign Up</button>
      </form>
      <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>
</div>
<?php include "../includes/footer.php"; ?>
