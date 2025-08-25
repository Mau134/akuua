<?php
require_once "../config/db.php";
include "../includes/header.php";

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
