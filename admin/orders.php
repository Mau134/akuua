<?php
session_start();
require '../vendor/autoload.php'; // Composer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../config/db.php';
include "./includes/header.php";

// --- Approve order ---
if (isset($_POST['approve_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT order_number, customer_name, customer_email, total, customer_address FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET `status`='Approved' WHERE id=?");
        $update->bind_param("i", $id);

        if ($update->execute()) {
            $message = "Dear {$order['customer_name']},<br><br>
            Your order (Order No: {$order['order_number']}) with a total of MWK " . number_format($order['total'], 2) . " has been <b>approved</b>.<br><br>
            Delivery Address: {$order['customer_address']}<br><br>
            Thank you for shopping with us.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order {$order['order_number']} Approved - Akuua Store", $message);
            $_SESSION['flash'] = "✅ Order {$order['order_number']} approved successfully.";
        } else {
            $_SESSION['flash'] = "❌ Failed to approve order {$order['order_number']}. DB error: " . $update->error;
        }
    }
    header("Location: orders.php");
    exit;
}

// --- Decline order ---
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT order_number, customer_name, customer_email, total FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $update = $conn->prepare("UPDATE orders SET `status`='Declined' WHERE id=?");
        $update->bind_param("i", $id);

        if ($update->execute()) {
            $message = "Dear {$order['customer_name']},<br><br>
            Unfortunately, your order (Order No: {$order['order_number']}) with a total of MWK " . number_format($order['total'], 2) . " has been <b>declined</b>.<br><br>
            Please contact support for more details.<br><br>- Akuua Store Team";

            sendMail($order['customer_email'], "Order {$order['order_number']} Declined - Akuua Store", $message);
            $_SESSION['flash'] = "⚠️ Order {$order['order_number']} declined successfully.";
        } else {
            $_SESSION['flash'] = "❌ Failed to decline order {$order['order_number']}. DB error: " . $update->error;
        }
    }
    header("Location: orders.php");
    exit;
}

// --- Delete order ---
if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT order_number FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    $delete = $conn->prepare("DELETE FROM orders WHERE id=?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        $_SESSION['flash'] = "🗑 Order {$order['order_number']} deleted successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to delete order {$order['order_number']}. DB error: " . $conn->error;
    }
    header("Location: orders.php");
    exit;
}

// --- Mail Helper ---
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'molande.mau@gmail.com'; // Gmail
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

// --- Filters ---
$statusFilter = $_GET['status'] ?? 'All';
if ($statusFilter === 'Approved') {
    $orders = $conn->query("SELECT * FROM orders WHERE status='Approved' ORDER BY id DESC");
} elseif ($statusFilter === 'Declined') {
    $orders = $conn->query("SELECT * FROM orders WHERE status='Declined' ORDER BY id DESC");
} elseif ($statusFilter === 'Order Received') {
    $orders = $conn->query("SELECT * FROM orders WHERE status='Order Received' ORDER BY id DESC");
} else {
    $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .order-card { border:1px solid #ddd; border-radius:15px; box-shadow:0 4px 8px rgba(0,0,0,0.1); transition:0.3s; }
    .order-card:hover { transform: translateY(-5px); }
    .verified-badge { color:green; font-size:1.2rem; margin-left:6px; }
    .star-rating { color:goldenrod; font-size:1rem; margin-top:5px; }
    .proof-img { max-width:100px; border-radius:8px; border:1px solid #ccc; }
    .nav-pills .nav-link.active { background-color:#198754 !important; }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Manage Orders</h2>

  <!-- Flash -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filter Tabs -->
  <ul class="nav nav-pills justify-content-center mb-4">
    <li class="nav-item"><a class="nav-link <?= $statusFilter==='All'?'active':'' ?>" href="?status=All">All</a></li>
    <li class="nav-item"><a class="nav-link <?= $statusFilter==='Order Received'?'active':'' ?>" href="?status=Order+Received">Pending</a></li>
    <li class="nav-item"><a class="nav-link <?= $statusFilter==='Approved'?'active':'' ?>" href="?status=Approved">Approved</a></li>
    <li class="nav-item"><a class="nav-link <?= $statusFilter==='Declined'?'active':'' ?>" href="?status=Declined">Declined</a></li>
  </ul>

  <!-- Orders Grid -->
  <div class="row g-4">
    <?php if ($orders->num_rows > 0): ?>
      <?php while($row = $orders->fetch_assoc()): ?>
        <div class="col-md-3 col-sm-6">
          <div class="card order-card p-3 h-100">
            <h5>
              Order <?= htmlspecialchars($row['order_number']) ?>
              <span class="verified-badge">✔</span>
            </h5>
            <p><b>Name:</b> <?= htmlspecialchars($row['customer_name']) ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($row['customer_email']) ?></p>
            <p><b>Total:</b> MWK <?= number_format($row['total'], 2) ?></p>
            <p><b>Method:</b> <?= htmlspecialchars($row['payment_method']) ?></p>
            <p><b>Status:</b> <span class="badge bg-info"><?= htmlspecialchars($row['status']) ?></span></p>

            <div class="star-rating">★★★★★</div>

            <?php if (!empty($row['payment_proof'])): ?>
              <a href="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" target="_blank">
                <img src="../uploads/<?= htmlspecialchars($row['payment_proof']) ?>" class="proof-img" alt="Proof">
              </a>
            <?php else: ?>
              <p class="text-muted">No proof uploaded</p>
            <?php endif; ?>

            <!-- Actions -->
            <?php if ($row['status'] === 'Order Received'): ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve_order" class="btn btn-success btn-sm mt-2">Approve</button>
              </form>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="decline_order" class="btn btn-danger btn-sm mt-2">Decline</button>
              </form>
            <?php elseif ($row['status'] === 'Declined'): ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="delete_order" class="btn btn-outline-danger btn-sm mt-2" onclick="return confirm('Delete this rejected order?')">Delete</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center"><p class="text-muted">No orders found.</p></div>
    <?php endif; ?>
  </div>
</div>
<?php include "./includes/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
