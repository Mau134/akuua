<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once "../config/db.php";
include "./includes/header.php";
?>
<section class="bg-dark text-white py-5 mb-5" style="background: url('assets/hero-bg.jpg') center/cover no-repeat;">
  <div class="container text-center py-5">
    <h1 class="display-4 fw-bold">Admin Dashboard</h1>
    <p class="lead mb-4">Discover the best deals on fashion, accessories, and more.</p>
  </div>
</section>
<h2 class="mb-4">Admin Dashboard</h2>
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
<?php include "./includes/footer.php"; ?>
