<?php
session_start();
require_once "../config/db.php";
include "../includes/header1.php";

// Show PHP errors for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// ✅ Auto-fill user info if logged in
$user_email = "";
$user_phone = "";
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = $conn->query("SELECT email, phone FROM users WHERE id=$uid");
    if ($res && $row = $res->fetch_assoc()) {
        $user_email = $row['email'];
        $user_phone = $row['phone'];
    }
}

// ✅ Calculate total
$total = 0;
foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
    $result = $conn->query("SELECT price FROM products WHERE id=$id");
    if ($row = $result->fetch_assoc()) {
        $total += $row['price'] * $qty;
    }
}

// ✅ Handle order submission
if (isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];
    $delivery_address = $_POST['delivery_address'];
    $proof = "";

    // Upload proof if provided
    if (!empty($_FILES['proof']['name'])) {
        $proof = time() . "_" . basename($_FILES['proof']['name']);
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (!move_uploaded_file($_FILES['proof']['tmp_name'], $uploadDir . $proof)) {
            die("❌ Failed to move uploaded file.");
        }
    }

    // Generate unique order number
    $order_number = 'ORD' . time();

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders 
        (order_number, customer_name, customer_email, customer_phone, total, payment_method, payment_proof, delivery_address, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Order Received')");
    $stmt->bind_param("ssssdsss",
        $order_number,
        $name,
        $email,
        $phone,
        $total,
        $payment_method,
        $proof,
        $delivery_address
    );

    if ($stmt->execute()) {
        // Confirmation email
        $subject = "Your Order Confirmation (#$order_number)";
        $message = "Hello $name,\n\nThank you for your purchase! Your order number is $order_number.\n\nDelivery Address: $delivery_address\nPhone: $phone";
        $headers = "From: no-reply@akuua.com";

        mail($email, $subject, $message, $headers);

        // Clear cart
        $_SESSION['cart'] = [];

        echo "<div class='alert alert-success text-center'>Order placed successfully! Your order number is <b>$order_number</b>. We’ll contact you soon.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}
?>
<style>
  body {
    background: #fff;
    color: #333;
  }
</style>

<div class="container py-5">
  <h2 class="mb-4">Checkout</h2>

  <!-- Payment Logos -->
  <section class="bg-light py-5 mb-5">
    <div class="container text-center">
      <h2 class="mb-4">We Accept</h2>
      <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
        <img src="../assets/img/tnm_logo.png" alt="Mpamba" class="payment-logo">
        <img src="../assets/img/airtel_logo.png" alt="Airtel Money" class="payment-logo">
        <img src="../assets/img/nationalbank_logo.png" alt="Mastercard" class="payment-logo">
      </div>
      <p class="mt-3 text-muted">Choose your preferred payment method at checkout</p>
    </div>
  </section>

  <style>
  .payment-logo {
      height: 60px;
      max-width: 150px;
      object-fit: contain;
  }
  @media (max-width: 576px) {
      .payment-logo {
          height: 45px;
          margin-bottom: 10px;
      }
  }
  </style>

  <!-- Checkout Form -->
  <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Phone Number</label>
      <input type="text" name="phone" class="form-control"
             value="<?= htmlspecialchars($user_phone) ?>"
             placeholder="e.g. 0991 234 567" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Delivery Address</label>
      <textarea name="delivery_address" class="form-control" rows="3" placeholder="e.g. Blantyre CTS / Lilongwe Speed Courier" required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Payment Method</label>
      <select name="payment_method" class="form-select" required onchange="showAccount(this.value)">
        <option value="">-- Select Payment Method --</option>
        <option value="Mpamba">Mpamba</option>
        <option value="Airtel Money">Airtel Money</option>
        <option value="Bank Transfer">Bank Transfer</option>
      </select>
    </div>

    <div id="account_details" class="alert alert-info" style="display:none;"></div>

    <div class="mb-3">
      <label class="form-label">Upload Payment Proof (Screenshot / Receipt)</label>
      <input type="file" name="proof" class="form-control">
    </div>

    <h4>Total: MWK <?= number_format($total,2) ?></h4>

    <button type="submit" name="place_order" class="btn btn-success btn-lg mt-3">Place Order</button>
  </form>
</div>

<script>
function showAccount(method) {
    let box = document.getElementById("account_details");
    if (method === "Mpamba") {
        box.style.display = "block";
        box.innerHTML = "<b>Mpamba Number:</b> 0897 391 415 (Akuua Store)";
    } else if (method === "Airtel Money") {
        box.style.display = "block";
        box.innerHTML = "<b>Airtel Money:</b> 0990 012 380 (Akuua Store)";
    } else if (method === "Bank Transfer") {
        box.style.display = "block";
        box.innerHTML = "<b>Bank Account:</b> National Bank - 1007448984 (Akuua Store)";
    } else {
        box.style.display = "none";
    }
}
</script>

<?php include "../includes/footer.php"; ?>
