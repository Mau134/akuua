<?php
include '../config/db.php';
include "./includes/header.php";
// Update order status
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

// Fetch orders
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Manage Orders</h2>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment Proof</th>
            <th>Status</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td>
              <?php if ($row['payment_proof']): ?>
                <a href="../uploads/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
              <?php else: ?>
                <span class="text-muted">No proof</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge bg-<?php 
                echo $row['status']=='Delivered' ? 'success' : 
                     ($row['status']=='In Progress' ? 'warning' : 'secondary'); 
              ?>">
                <?= $row['status'] ?>
              </span>
            </td>
            <td>
              <form method="post" class="d-flex gap-2">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <select name="status" class="form-select form-select-sm">
                  <option <?= $row['status']=='Order Received'?'selected':'' ?>>Order Received</option>
                  <option <?= $row['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                  <option <?= $row['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                </select>
                <button type="submit" name="update_status" class="btn btn-sm btn-success">Save</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include "./includes/footer.php"; ?>

</body>
</html>
