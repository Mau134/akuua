<?php
require_once "../config/db.php";

// Handle Approve / Decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $orderId = intval($_POST['order_id']);
        $sql = "UPDATE orders SET status='approved' WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$orderId])) {
            echo "<div class='alert alert-success'>Order #$orderId approved successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to approve order #$orderId. DB error: " . $stmt->error . "</div>";
        }
    }

    if (isset($_POST['decline'])) {
        $orderId = intval($_POST['order_id']);
        $sql = "UPDATE orders SET status='declined' WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$orderId])) {
            echo "<div class='alert alert-warning'>Order #$orderId declined successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to decline order #$orderId. DB error: " . $stmt->error . "</div>";
        }
    }
}

// Fetch orders by status
$pendingOrders = $conn->query("SELECT * FROM orders WHERE status='pending' ORDER BY created_at DESC");
$approvedOrders = $conn->query("SELECT * FROM orders WHERE status='approved' ORDER BY created_at DESC");
$declinedOrders = $conn->query("SELECT * FROM orders WHERE status='declined' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders - Akuua</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
  <h1 class="mb-4">Orders Management</h1>

  <!-- Approved Orders -->
  <h2 class="text-success">✅ Approved Orders</h2>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-success">
        <tr>
          <th>ID</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Address</th>
          <th>Proof</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $approvedOrders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['order_number']) ?></td>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['customer_email']) ?></td>
          <td><?= htmlspecialchars($row['customer_phone']) ?></td>
          <td>$<?= htmlspecialchars($row['total']) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td>
            <?php if (!empty($row['delivery_address'])): ?>
              <?= nl2br(htmlspecialchars($row['delivery_address'])) ?>
            <?php else: ?>
              <?= nl2br(htmlspecialchars($row['customer_address'])) ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($row['payment_proof'])): ?>
              <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:100px; height:auto; border:1px solid #ccc;">
              </a>
            <?php else: ?>
              <span class="text-muted">No proof</span>
            <?php endif; ?>
          </td>
          <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Declined Orders -->
  <h2 class="text-danger">❌ Declined Orders</h2>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-danger">
        <tr>
          <th>ID</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Address</th>
          <th>Proof</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $declinedOrders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['order_number']) ?></td>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['customer_email']) ?></td>
          <td><?= htmlspecialchars($row['customer_phone']) ?></td>
          <td>$<?= htmlspecialchars($row['total']) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td>
            <?php if (!empty($row['delivery_address'])): ?>
              <?= nl2br(htmlspecialchars($row['delivery_address'])) ?>
            <?php else: ?>
              <?= nl2br(htmlspecialchars($row['customer_address'])) ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($row['payment_proof'])): ?>
              <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:100px; height:auto; border:1px solid #ccc;">
              </a>
            <?php else: ?>
              <span class="text-muted">No proof</span>
            <?php endif; ?>
          </td>
          <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pending Orders -->
  <h2 class="text-warning">⏳ Pending Orders</h2>
  <div class="table-responsive mb-5">
    <table class="table table-bordered table-striped">
      <thead class="table-warning">
        <tr>
          <th>ID</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Address</th>
          <th>Proof</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $pendingOrders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['order_number']) ?></td>
          <td><?= htmlspecialchars($row['customer_name']) ?></td>
          <td><?= htmlspecialchars($row['customer_email']) ?></td>
          <td><?= htmlspecialchars($row['customer_phone']) ?></td>
          <td>$<?= htmlspecialchars($row['total']) ?></td>
          <td><?= htmlspecialchars($row['payment_method']) ?></td>
          <td>
            <?php if (!empty($row['delivery_address'])): ?>
              <?= nl2br(htmlspecialchars($row['delivery_address'])) ?>
            <?php else: ?>
              <?= nl2br(htmlspecialchars($row['customer_address'])) ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($row['payment_proof'])): ?>
              <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:100px; height:auto; border:1px solid #ccc;">
              </a>
            <?php else: ?>
              <span class="text-muted">No proof</span>
            <?php endif; ?>
          </td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
            </form>
            <form method="POST" class="d-inline">
              <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
              <button type="submit" name="decline" class="btn btn-danger btn-sm">Decline</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
