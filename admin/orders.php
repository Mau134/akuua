<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// ✅ Approve order
if (isset($_POST['approve_order'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE orders SET status='approved' WHERE id=$id");
}

// ✅ Decline order
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE orders SET status='declined' WHERE id=$id");
}

// ✅ Delete order
if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM orders WHERE id=$id");
}

// ✅ Fetch all orders
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table td, .table th {
      vertical-align: middle;
      white-space: nowrap;
    }
    .items-box {
      max-height: 120px;
      overflow-y: auto;
      font-size: 0.85rem;
    }
    .actions-col {
      min-width: 220px;
    }
  </style>
</head>
<body class="bg-light">
<div class="container my-5">
  <h2 class="mb-4">Orders Management</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Proof</th>
          <th>Delivery Address</th>
          <th>Date</th>
          <th>Status</th>
          <th>Items</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
          $color = match ($row['status']) {
              'pending' => 'warning',
              'approved' => 'success',
              'declined' => 'danger',
              default => 'secondary'
          };
          ?>
          <tr>
            <td><?= htmlspecialchars($row['order_number']) ?></td>
            <td>
              <?= htmlspecialchars($row['customer_name']) ?><br>
              <small><?= htmlspecialchars($row['customer_email']) ?></small><br>
              <small>📞 <?= htmlspecialchars($row['customer_phone']) ?></small>
            </td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td>
              <?php if (!empty($row['payment_proof'])): ?>
                <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
              <?php else: ?>
                <span class="text-muted">No proof</span>
              <?php endif; ?>
            </td>
            <td><?= nl2br(htmlspecialchars($row['delivery_address'])) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><span class="badge bg-<?= $color ?>"><?= ucfirst($row['status']) ?></span></td>
            <td>
              <div class="items-box">
                <?php
                $items = $conn->query("SELECT * FROM order_items WHERE order_id=" . intval($row['id']));
                while ($item = $items->fetch_assoc()):
                ?>
                  <div>- <?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</div>
                <?php endwhile; ?>
              </div>
            </td>
            <td class="text-center actions-col">
              <?php if ($row['status'] == 'pending'): ?>
                <div class="btn-group" role="group">
                  <form method="post" class="d-inline">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" name="approve_order" class="btn btn-success btn-sm">Approve</button>
                  </form>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="submit" name="decline_order" class="btn btn-danger btn-sm">Decline</button>
                  </form>
                </div>
              <?php elseif ($row['status'] == 'declined'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" name="delete_order" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this rejected order?')">Delete</button>
                </form>
              <?php elseif ($row['status'] == 'approved'): ?>
                <span class="badge bg-success">Approved</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
