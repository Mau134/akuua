<?php
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
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        $conn->query("UPDATE orders SET status='Approved' WHERE id=$id");

        $message = "Dear {$order['customer_name']},<br><br>
        Your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>approved</b>.<br><br>
        Delivery Address: {$order['delivery_address']}<br><br>
        Thank you for shopping with us.<br><br>- Akuua Store Team";

        sendMail($order['customer_email'], "Order #$id Approved - Akuua Store", $message);
    }
}

// Decline order
if (isset($_POST['decline_order'])) {
    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare("SELECT customer_name, customer_email, total FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        $conn->query("UPDATE orders SET status='Declined' WHERE id=$id");

        $message = "Dear {$order['customer_name']},<br><br>
        Unfortunately, your order (ID: $id) with a total of MWK " . number_format($order['total'], 2) . " has been <b>declined</b>.<br><br>
        Please contact support for more details.<br><br>- Akuua Store Team";

        sendMail($order['customer_email'], "Order #$id Declined - Akuua Store", $message);
    }
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
        echo "<div class='alert alert-success'>Email sent to $to.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Mailer Error: {$mail->ErrorInfo}</div>";
    }
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
            <th>Payment</th>
            <th>Delivery Address</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td>
              <?= htmlspecialchars($row['customer_name']) ?><br>
              <small><?= htmlspecialchars($row['customer_email']) ?></small>
            </td>
            <td>MWK<?= number_format($row['total'], 2) ?></td>
            <td>
              <b><?= htmlspecialchars($row['payment_method']) ?></b><br>
              <?php if ($row['payment_proof']): ?>
                <a href="../uploads/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">View Proof</a>
              <?php else: ?>
                <span class="text-muted">No proof</span>
              <?php endif; ?>
            </td>
            <td><?= nl2br(htmlspecialchars($row['delivery_address'])) ?></td>
            <td>
              <span class="badge bg-<?php 
                echo $row['status']=='Approved' ? 'success' : 
                     ($row['status']=='In Progress' ? 'warning' : 
                     ($row['status']=='Declined' ? 'danger' : 'secondary')); 
              ?>">
                <?= $row['status'] ?>
              </span>
            </td>
            <td>
              <!-- Approve button -->
              <form method="post" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve_order" class="btn btn-sm btn-success">Approve</button>
              </form>

              <!-- Decline button -->
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
  </div>
</div>
<?php include "./includes/footer.php"; ?>
</body>
</html>
