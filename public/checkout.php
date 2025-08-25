<?php
session_start();
require_once "../config/db.php";
include "../includes/header.php";

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
    $result = $conn->query("SELECT price FROM products WHERE id=$id");
    if ($row = $result->fetch_assoc()) {
        $total += $row['price'] * $qty;
    }
}

// Handle order submission
if (isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $payment_method = $_POST['payment_method'];
    $proof = "";

    // Upload proof if provided
    if (!empty($_FILES['proof']['name'])) {
        $proof = time() . "_" . basename($_FILES['proof']['name']);
        move_uploaded_file($_FILES['proof']['tmp_name'], "uploads/$proof");
    }

    // Generate unique order number
    $order_number = 'ORD' . time();

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_email, total, payment_method, payment_proof, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'Order Received')");
    $stmt->bind_param("sssdss", $order_number, $name, $email, $total, $payment_method, $proof);
    $stmt->execute();

    // Clear cart
    $_SESSION['cart'] = [];

    echo "<div class='alert alert-success text-center'>Order placed successfully! Your order number is <b>$order_number</b>. We’ll contact you soon.</div>";




    // Clear cart
    $_SESSION['cart'] = [];

    echo "<div class='alert alert-success text-center'>Order placed successfully! We’ll contact you soon.</div>";
}
?>

<div class="container py-5">
  <h2 class="mb-4">Checkout</h2>
<!-- Payment Options Banner -->
<section class="bg-light py-5 mb-5">
  <div class="container text-center">
    <h2 class="mb-4">We Accept</h2>
    <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
      <img src="../assets/img/tnm_logo.png" alt="Mpamba" class="payment-logo">
      <img src="../assets/img/airtel_logo.png" alt="Airtel Money" class="payment-logo">
      <img src="../assets/img/standardbank_logo.png" alt="Bank Transfer" class="payment-logo">
      <img src="../assets/img/visa_logo.png" alt="Visa" class="payment-logo">
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



  <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Payment Method</label>
      <select name="payment_method" class="form-select" required>
        <option value="Mpamba">Mpamba</option>
        <option value="Airtel Money">Airtel Money</option>
        <option value="Bank Transfer">Bank Transfer</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Payment Proof (Screenshot / Receipt)</label>
      <input type="file" name="proof" class="form-control">
    </div>

    <h4>Total: MWK <?= number_format($total,2) ?></h4>

    <button type="submit" name="place_order" class="btn btn-success btn-lg mt-3">Place Order</button>
  </form>
</div>

<?php include "../includes/footer.php"; ?>
