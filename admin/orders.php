<?php
session_start();
require '../vendor/autoload.php'; // Composer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../config/db.php';
include "./includes/header.php";

// ✅ Approve order
if (isset($_POST['approve_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT customer_name, customer_email, total, customer_address, delivery_address FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET `status`='Approved' WHERE id=?");
        $update->bind_param("i", $id);

        if ($update->execute()) {
            $address = !empty($order['delivery_address']) ? $order['delivery_address'] : $order['customer_address'];

            $message = "Dear {$order['customer_name']},<br><br>
            Your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>approved</b>.<br><br>
            Delivery Address: {$address}<br><br>
            Thank you for shopping with us.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order #$id Approved - Akuua Store", $message);
            $_SESSION['flash'] = "✅ Order #$id approved successfully.";
        } else {
            $_SESSION['flash'] = "❌ Failed to approve order #$id. DB error: " . $update->error;
        }
    }

    header("Location: orders.php");
    exit;
}

// ✅ Decline order
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT customer_name, customer_email, total FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET `status`='Declined' WHERE id=?");
        $update->bind_param("i", $id);

        if ($update->execute()) {
            $message = "Dear {$order['customer_name']},<br><br>
            Unfortunately, your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>declined</b>.<br><br>
            Please contact support for more details.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order #$id Declined - Akuua Store", $message);
            $_SESSION['flash'] = "⚠️ Order #$id declined successfully.";
        } else {
            $_SESSION['flash'] = "❌ Failed to decline order #$id. DB error: " . $update->error;
        }
    }

    header("Location: orders.php");
    exit;
}

// ✅ Delete rejected order
if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);
    $delete = $conn->prepare("DELETE FROM orders WHERE id=?");
    $delete->bind_param("i", $id);
    if ($delete->execute()) {
        $_SESSION['flash'] = "🗑️ Order #$id deleted successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to delete order #$id. DB error: " . $conn->error;
    }
    header("Location: orders.php");
    exit;
}

// ✅ Mail helper
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'molande.mau@gmail.com'; // your Gmail
        $mail->Password = 'uphx vfoc nzdz tmxc';   // Gmail App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('molande.mau@gmail.com', 'Akuua Store');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Mailer Error: {$mail->ErrorInfo}</div>";
    }
}

// ✅ Fetch orders grouped by status
$approvedOrders = $conn->query("SELECT * FROM orders WHERE status='Approved' ORDER BY id DESC");
$declinedOrders = $conn->query("SELECT * FROM orders WHERE status='Declined' ORDER BY id DESC");
$pendingOrders  = $conn->query("SELECT * FROM orders WHERE status NOT IN ('Approved','Declined') ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders</title>
  <link rel="icon" type="image/png" href="../assets/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Manage Orders</h2>

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- ✅ Approved Orders -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">Approved Orders</div>
    <div class="card-body">
      <?php if ($approvedOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Proof</th>
            <th>Delivery Address</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $approvedOrders->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['order_number']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
<td>
  <?php if (!empty($row['payment_proof'])): ?>
    <a href="/akuua/public/uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
      <img src="/akuua/public/uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:80px; height:auto; border:1px solid #ccc;">
    </a>
  <?php else: ?>
    <span class="text-muted">No proof</span>
  <?php endif; ?>
</td>
            <td><?= nl2br(htmlspecialchars(!empty($row['delivery_address']) ? $row['delivery_address'] : $row['customer_address'])) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><span class="badge bg-success">Approved</span></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No approved orders yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ❌ Declined Orders -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white">Declined Orders</div>
    <div class="card-body">
      <?php if ($declinedOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Proof</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $declinedOrders->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['order_number']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td>
              <?php if (!empty($row['payment_proof'])): ?>
                <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                  <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:80px; height:auto; border:1px solid #ccc;">
                </a>
              <?php else: ?>
                <span class="text-muted">No proof</span>
              <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td><span class="badge bg-danger">Declined</span></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this rejected order?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No declined orders yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ⏳ Pending Orders -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">Pending / In Progress Orders</div>
    <div class="card-body">
      <?php if ($pendingOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Proof</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $pendingOrders->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['order_number']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td>
              <?php if (!empty($row['payment_proof'])): ?>
                <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                  <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" style="max-width:80px; height:auto; border:1px solid #ccc;">
                </a>
              <?php else: ?>
                <span class="text-muted">No proof</span>
              <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td><span class="badge bg-warning"><?= htmlspecialchars($row['status']) ?></span></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve_order" class="btn btn-sm btn-success">Approve</button>
              </form>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="decline_order" class="btn btn-sm btn-danger">Decline</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No pending orders right now.</p>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include "./includes/footer.php"; ?>
</body>
</html>
