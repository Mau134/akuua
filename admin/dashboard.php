<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include "./includes/header.php";
?>

<section class="bg-dark text-white py-5 mb-5">
  <div class="container text-center py-5">
    <h1 class="display-4 fw-bold">Admin Dashboard</h1>
    <p class="lead mb-4">Welcome <?= htmlspecialchars($_SESSION['admin_email']) ?>!</p>
  </div>
</section>

<div class="container mb-5">
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow p-4 text-center">
        <h4>Products</h4>
        <a href="products.php" class="btn btn-primary">Manage Products</a>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow p-4 text-center">
        <h4>Orders</h4>
        <a href="orders.php" class="btn btn-primary">Manage Orders</a>
      </div>
    </div>
  </div>
</div>
<a href="logout.php" class="btn btn-danger">Logout</a>
<?php include "./includes/footer.php"; ?>
