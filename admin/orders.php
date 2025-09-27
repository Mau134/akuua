<?php
session_start();
require '../vendor/autoload.php'; // Composer autoload for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/db.php'; // Adjust path if needed

// ---------- Helper: Flash ----------
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = null;
function set_flash($msg) {
    $_SESSION['flash'] = $msg;
}

// ---------- Mail helper ----------
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // <<< REPLACE THESE with your real SMTP credentials or use environment variables >>>
        $mail->Username = 'molande.mau@gmail.com';
        $mail->Password = 'uphx vfoc nzdz tmxc';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('molande.mau@gmail.com', 'Akuua Store');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error server-side and return false (do not abort DB changes)
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}

// ---------- Process POST actions BEFORE any output ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize id helper
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if (isset($_POST['approve_order']) && $id > 0) {
        // Fetch order
        $stmt = $conn->prepare("SELECT customer_name, customer_email, total, delivery_address FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            // Update status using prepared statement
            $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $newstatus = 'Approved';
            $update->bind_param("si", $newstatus, $id);
            $ok = $update->execute();
            if ($ok && $update->affected_rows > 0) {
                set_flash("Order #{$id} approved.");
            } else {
                // Include DB error for debugging
                set_flash("Failed to approve order #{$id}. DB error: " . $conn->error);
            }
            $update->close();

            // Send email (best-effort, don't fail entire operation on mail error)
            $message = "Dear {$order['customer_name']},<br><br>"
                     . "Your order (ID: $id) with a total of MWK " . number_format($order['total'], 2)
                     . " has been <b>approved</b>.<br><br>Delivery Address: {$order['delivery_address']}<br><br>"
                     . "Thank you for shopping with us.<br><br>- Akuua Store Team";

            if (!sendMail($order['customer_email'], "Order #$id Approved - Akuua Store", $message)) {
                // optional: append warning to flash
                $_SESSION['flash'] .= " (Email failed to send.)";
            }
        } else {
            set_flash("Order #{$id} not found.");
        }

        header("Location: orders.php");
        exit;
    }

    if (isset($_POST['decline_order']) && $id > 0) {
        $stmt = $conn->prepare("SELECT customer_name, customer_email, total FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $newstatus = 'Declined';
            $update->bind_param("si", $newstatus, $id);
            $ok = $update->execute();
            if ($ok && $update->affected_rows > 0) {
                set_flash("Order #{$id} declined.");
            } else {
                set_flash("Failed to decline order #{$id}. DB error: " . $conn->error);
            }
            $update->close();

            $message = "Dear {$order['customer_name']},<br><br>"
                     . "Unfortunately, your order (ID: $id) with a total of MWK " . number_format($order['total'], 2)
                     . " has been <b>declined</b>.<br><br>Please contact support for more details.<br><br>- Akuua Store Team";

            if (!sendMail($order['customer_email'], "Order #$id Declined - Akuua Store", $message)) {
                $_SESSION['flash'] .= " (Email failed to send.)";
            }
        } else {
            set_flash("Order #{$id} not found.");
        }

        header("Location: orders.php");
        exit;
    }

    if (isset($_POST['delete_order']) && $id > 0) {
        // Only allow deleting if status is Declined (extra safety)
        $check = $conn->prepare("SELECT status FROM orders WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if ($row && $row['status'] === 'Declined') {
            $delete = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $delete->bind_param("i", $id);
            $ok = $delete->execute();
            if ($ok && $delete->affected_rows > 0) {
                set_flash("Declined order #{$id} deleted.");
            } else {
                set_flash("Failed to delete order #{$id}. DB error: " . $conn->error);
            }
            $delete->close();
        } else {
            set_flash("Order not deleted. Only orders with status 'Declined' can be removed.");
        }

        header("Location: orders.php");
        exit;
    }
}

// ---------- Page rendering ----------
include "./includes/header.php"; // include after processing so redirects work

// Fetch grouped orders
$approvedOrders = $conn->query("SELECT * FROM orders WHERE status = 'Approved' ORDER BY id DESC");
$declinedOrders = $conn->query("SELECT * FROM orders WHERE status = 'Declined' ORDER BY id DESC");
$otherOrders    = $conn->query("SELECT * FROM orders WHERE status NOT IN ('Approved','Declined') ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin - Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Manage Orders</h2>

  <!-- Flash -->
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Approved -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">Approved Orders</div>
    <div class="card-body">
      <?php if ($approvedOrders && $approvedOrders->num_rows > 0): ?>
      <div class="table-responsive">
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
      </div>
      <?php else: ?>
        <p class="text-muted">No approved orders yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Declined -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white">Declined Orders</div>
    <div class="card-body">
      <?php if ($declinedOrders && $declinedOrders->num_rows > 0): ?>
      <div class="table-responsive">
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
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this rejected order?');">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      </div>
      <?php else: ?>
        <p class="text-muted">No declined orders yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pending / Other -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">Pending / In Progress Orders</div>
    <div class="card-body">
      <?php if ($otherOrders && $otherOrders->num_rows > 0): ?>
      <div class="table-responsive">
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
      </div>
      <?php else: ?>
        <p class="text-muted">No pending orders right now.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include "./includes/footer.php"; ?>
</body>
</html>
