<?php
session_start();
require '../vendor/autoload.php'; // Composer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../config/db.php';
include "./includes/header.php";

// Approve order
if (isset($_POST['approve_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT customer_name, customer_email, total, delivery_address FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET status='Approved' WHERE id=?");
        $update->bind_param("i", $id);
        if ($update->execute()) {
            $message = "Dear {$order['customer_name']},<br><br>
            Your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>approved</b>.<br><br>
            Delivery Address: {$order['delivery_address']}<br><br>
            Thank you for shopping with us.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order #$id Approved - Akuua Store", $message);

            echo "<div class='alert alert-success'>Order #$id approved successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to approve order: {$conn->error}</div>";
        }
    }
}

// Decline order
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT customer_name, customer_email, total FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET status='Declined' WHERE id=?");
        $update->bind_param("i", $id);
        if ($update->execute()) {
            $message = "Dear {$order['customer_name']},<br><br>
            Unfortunately, your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>declined</b>.<br><br>
            Please contact support for more details.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order #$id Declined - Akuua Store", $message);

            echo "<div class='alert alert-warning'>Order #$id declined successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to decline order: {$conn->error}</div>";
        }
    }
}


// Delete rejected order
if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM orders WHERE id=$id");
    $_SESSION['flash'] = "🗑️ Order #$id has been deleted successfully.";
    header("Location: orders.php");
    exit;
}

// Mail helper
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'molande.mau@gmail.com'; // your Gmail
        $mail->Password = 'uphx vfoc nzdz tmxc';   // your Gmail App password
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

// Fetch orders grouped by status
$approvedOrders = $conn->query("SELECT * FROM orders WHERE status='Approved' ORDER BY id DESC");
$declinedOrders = $conn->query("SELECT * FROM orders WHERE status='Declined' ORDER BY id DESC");
$otherOrders    = $conn->query("SELECT * FROM orders WHERE status NOT IN ('Approved','Declined') ORDER BY id DESC");
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

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Approved Orders -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">Approved Orders</div>
    <div class="card-body">
      <?php if ($approvedOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Delivery Address</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $approvedOrders->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['delivery_address'])) ?></td>
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

  <!-- Declined Orders -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white">Declined Orders</div>
    <div class="card-body">
      <?php if ($declinedOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $declinedOrders->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
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

  <!-- Other Orders (Pending / In Progress etc.) -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">Pending / In Progress Orders</div>
    <div class="card-body">
      <?php if ($otherOrders->num_rows > 0): ?>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $otherOrders->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['customer_email']) ?></small></td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td><?= htmlspecialchars($row['payment_method']) ?></td>
            <td><span class="badge bg-warning"><?= $row['status'] ?></span></td>
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
