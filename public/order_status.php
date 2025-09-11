<?php
require_once "../config/db.php";
include "../includes/header1.php";

$status = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $orderNo = $_POST['order_no'];
  $result = $conn->query("SELECT status FROM orders WHERE order_number='$orderNo'");
  if ($result->num_rows > 0) {
    $status = $result->fetch_assoc()['status'];
  } else {
    $status = "not_found";
  }
}
?>
<style>
  body {
    position: relative;
    background: url("../assets/img/background1.jpg") center center fixed;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-color: #f8f9fa; /* fallback */
    color: #333;
    z-index: 0;
  }

  /* Overlay to dim the background */
  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7); /* white transparent overlay */
    z-index: -1;
  }
</style>
<h2 class="mb-4">Track Your Order</h2>
<div class="card shadow p-4">
  <form method="post" class="mb-3">
    <label class="form-label">Enter Order Number</label>
    <input type="text" name="order_no" class="form-control" required>
    <button type="submit" class="btn btn-success mt-3">Check Status</button>
  </form>

  <?php if ($status): ?>
    <?php if ($status === "not_found"): ?>
      <div class="alert alert-danger">Order not found.</div>
    <?php else: ?>
      <div class="alert alert-info">Your order status: <b><?= ucfirst($status) ?></b></div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>
