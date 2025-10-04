<?php
session_start();
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../config/db.php';
include "./includes/header.php";

// ✅ Approve order
if (isset($_POST['approve_order'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $conn->query("UPDATE orders SET status='Approved' WHERE id=$id");
        $address = !empty($order['delivery_address']) ? $order['delivery_address'] : $order['customer_address'];
        $message = "
            Dear {$order['customer_name']},<br><br>
            Your order ({$order['order_number']}) with a total of MWK " . number_format($order['total'], 2) . " has been <b>approved</b>.<br><br>
            Delivery Address: {$address}<br><br>
            Thank you for shopping with us.<br><br>
            - Akuua Store Team
        ";
        sendMail($order['customer_email'], "Order #{$order['order_number']} Approved - Akuua Store", $message);
        $_SESSION['flash'] = "✅ Order #{$order['order_number']} approved successfully.";
    }
    header("Location: orders.php");
    exit;
}

// ✅ Decline order
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $conn->query("UPDATE orders SET status='Declined' WHERE id=$id");
        $message = "
            Dear {$order['customer_name']},<br><br>
            Unfortunately, your order ({$order['order_number']}) with a total of MWK " . number_format($order['total'], 2) . " has been <b>declined</b>.<br><br>
            Please contact support for more details.<br><br>
            - Akuua Store Team
        ";
        sendMail($order['customer_email'], "Order #{$order['order_number']} Declined - Akuua Store", $message);
        $_SESSION['flash'] = "⚠️ Order #{$order['order_number']} declined successfully.";
    }
    header("Location: orders.php");
    exit;
}

// ✅ Delete order
if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM orders WHERE id=$id");
    $_SESSION['flash'] = "🗑️ Order #$id deleted successfully.";
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
        $mail->Username = 'akuuastore@gmail.com';
        $mail->Password = 'rlny cmvy cahq nlbg'; // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('akuuastore@gmail.com', 'Akuua Store');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Mailer Error: {$mail->ErrorInfo}</div>";
    }
}

// ✅ Fetch orders
$approvedOrders = $conn->query("SELECT * FROM orders WHERE status='Approved' ORDER BY id DESC");
$declinedOrders = $conn->query("SELECT * FROM orders WHERE status='Declined' ORDER BY id DESC");
$pendingOrders  = $conn->query("SELECT * FROM orders WHERE status NOT IN ('Approved','Declined') ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.table-wrapper { overflow-x: auto; }
td img { max-width: 60px; border-radius: 6px; }
.items-table td { font-size: 14px; vertical-align: middle; }
.collapse-cell { background: #fafafa; }
.product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 8px; }
@media (max-width: 768px) { table { font-size: 13px; } }
</style>
</head>
<body class="bg-light">
<div class="container py-5">

<h2 class="mb-4">Manage Orders</h2>

<?php if (isset($_SESSION['flash'])): ?>
<div class="alert alert-info alert-dismissible fade show">
    <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
function renderOrders($orders, $status, $conn) {
    $colors = [
        'Approved' => 'success',
        'Declined' => 'danger',
        'Pending'  => 'warning'
    ];
?>
<div class="card shadow-sm mb-4">
  <div class="card-header bg-<?= $colors[$status] ?? 'secondary' ?> text-white">
    <?= ucfirst($status) ?> Orders
  </div>
  <div class="card-body table-wrapper">
    <?php if ($orders->num_rows > 0): ?>
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Proof</th>
          <th>Date</th>
          <th>Status</th>
          <?php if ($status !== 'Approved'): ?><th>Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $orders->fetch_assoc()): ?>
        <tr data-bs-toggle="collapse" data-bs-target="#items-<?= $row['id'] ?>" class="clickable">
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
              <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>">
              </a>
            <?php else: ?>
              <span class="text-muted">No proof</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td><span class="badge bg-<?= $colors[$status] ?? 'secondary' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
          <?php if ($status !== 'Approved'): ?>
          <td>
            <form method="post" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="approve_order" class="btn btn-sm btn-success">Approve</button>
            </form>
            <form method="post" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="decline_order" class="btn btn-sm btn-danger">Decline</button>
            </form>
            <?php if ($status === 'Declined'): ?>
            <form method="post" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>

        <!-- Collapsible Order Items -->
        <tr class="collapse collapse-cell" id="items-<?= $row['id'] ?>">
          <td colspan="8">
            <strong>Ordered Items:</strong>
            <?php
            $items = $conn->query("SELECT oi.*, p.name AS product_name, p.image AS product_image 
                                   FROM order_items oi 
                                   LEFT JOIN products p ON oi.product_id = p.id 
                                   WHERE oi.order_id = {$row['id']}");
            if ($items->num_rows > 0):
            ?>
            <table class="table table-sm table-bordered mt-2 items-table">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Image</th>
                  <th>Size</th>
                  <th>Color</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
              <?php while($it = $items->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($it['product_name'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($it['product_image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($it['product_image']) ?>" class="product-thumb">
                  <?php else: ?>
                    <span class="text-muted">No image</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($it['size'] ?: '-') ?></td>
                <td><?= htmlspecialchars($it['color'] ?: '-') ?></td>
                <td><?= (int)$it['quantity'] ?></td>
                <td>MWK<?= number_format($it['price'], 2) ?></td>
                <td>MWK<?= number_format($it['quantity'] * $it['price'], 2) ?></td>
              </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p class="text-muted mb-0">No items found for this order.</p>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p class="text-muted">No <?= strtolower($status) ?> orders yet.</p>
    <?php endif; ?>
  </div>
</div>
<?php } ?>

<?php
renderOrders($pendingOrders, 'Pending', $conn);
renderOrders($approvedOrders, 'Approved', $conn);
renderOrders($declinedOrders, 'Declined', $conn);
?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include "./includes/footer.php"; ?>
</body>
</html>
