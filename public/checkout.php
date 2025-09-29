<?php
session_start();
require_once "../config/db.php";
include "../includes/header1.php";

// Show PHP errors for debugging (disable in production)
error_reporting(E_ALL);
ini_set("display_errors", 1);

/**
 * Normalize cart into consistent structure:
 * Resulting array items: [ ['id'=>int, 'quantity'=>int, 'size'=>string, 'color'=>string], ... ]
 * Supports both:
 *  - old: $_SESSION['cart'] = [ product_id => qty, ... ]
 *  - new: $_SESSION['cart'] = [ 0 => ['id'=>..., 'quantity'=>..., 'size'=>..., 'color'=>...], ... ]
 */
$rawCart = $_SESSION['cart'] ?? [];
$cart = [];

if (!empty($rawCart) && is_array($rawCart)) {
    foreach ($rawCart as $key => $val) {
        // Case 1: item is an array with 'id' key (new structure)
        if (is_array($val) && isset($val['id'])) {
            $pid = (int)$val['id'];
            $qty = isset($val['quantity']) ? (int)$val['quantity'] : (isset($val['qty']) ? (int)$val['qty'] : 1);
            $size = $val['size'] ?? '';
            $color = $val['color'] ?? '';
            if ($pid > 0 && $qty > 0) {
                $cart[] = ['id' => $pid, 'quantity' => $qty, 'size' => $size, 'color' => $color];
            }
            continue;
        }

        // Case 2: associative mapping product_id => qty (older structure)
        if (is_numeric($key)) {
            $pid = (int)$key;
            $qty = is_numeric($val) ? (int)$val : 1;
            if ($pid > 0 && $qty > 0) {
                $cart[] = ['id' => $pid, 'quantity' => $qty, 'size' => '', 'color' => ''];
            }
            continue;
        }

        // Unknown format: ignore
    }
}

// Auto-fill user info if logged in
$user_email = "";
$user_phone = "";
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $res = $conn->prepare("SELECT email, phone FROM users WHERE id = ?");
    $res->bind_param("i", $uid);
    $res->execute();
    $r = $res->get_result();
    if ($r && $row = $r->fetch_assoc()) {
        $user_email = $row['email'] ?? "";
        $user_phone = $row['phone'] ?? "";
    }
    $res->close();
}

// If cart is empty -> show message later; compute total safely
$total = 0.0;
if (!empty($cart)) {
    // Prepare price lookup
    $priceStmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    foreach ($cart as $item) {
        $pid = (int)$item['id'];
        $qty = max(1, (int)$item['quantity']);

        $priceStmt->bind_param("i", $pid);
        $priceStmt->execute();
        $res = $priceStmt->get_result();
        if ($res && $r = $res->fetch_assoc()) {
            $price = (float)$r['price'];
            $total += $price * $qty;
        }
    }
    $priceStmt->close();
}

// Handle order submission
if (isset($_POST['place_order'])) {
    if (empty($cart)) {
        echo "<div class='alert alert-warning'>Your cart is empty. Add items before placing an order.</div>";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $payment_method = trim($_POST['payment_method'] ?? '');
        $delivery_address = trim($_POST['delivery_address'] ?? '');
        $proof = "";

        // Basic validation
        if ($name === "" || $email === "" || $phone === "" || $delivery_address === "" || $payment_method === "") {
            echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
        } else {
            // Handle upload
            if (!empty($_FILES['proof']['name'])) {
                $proof = time() . "_" . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($_FILES['proof']['name']));
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (!move_uploaded_file($_FILES['proof']['tmp_name'], $uploadDir . $proof)) {
                    echo "<div class='alert alert-danger'>Failed to upload payment proof.</div>";
                    $proof = "";
                }
            }

            // Recompute total server-side (trusted)
            $computedTotal = 0.0;
            $priceStmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            foreach ($cart as $item) {
                $pid = (int)$item['id'];
                $qty = max(1, (int)$item['quantity']);

                $priceStmt->bind_param("i", $pid);
                $priceStmt->execute();
                $res = $priceStmt->get_result();
                if ($res && $r = $res->fetch_assoc()) {
                    $price = (float)$r['price'];
                    $computedTotal += $price * $qty;
                }
            }
            $priceStmt->close();

            // Generate order number
            $order_number = "ORD" . time();

            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, total, payment_method, payment_proof, delivery_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Order Received')");
            if ($stmt === false) {
                echo "<div class='alert alert-danger'>DB prepare error: " . htmlspecialchars($conn->error) . "</div>";
            } else {
                $stmt->bind_param("ssssdsss", $order_number, $name, $email, $phone, $computedTotal, $payment_method, $proof, $delivery_address);
                if ($stmt->execute()) {
                    $order_id = $stmt->insert_id;

                    // Insert order items (try with size+color; fallback if DB doesn't have columns)
                    $oiQuery = "INSERT INTO order_items (order_id, product_id, quantity, price, size, color) VALUES (?, ?, ?, ?, ?, ?)";
                    $oiStmt = $conn->prepare($oiQuery);
                    $useFullColumns = true;
                    if ($oiStmt === false) {
                        // maybe order_items doesn't have size/color; try simpler insert
                        $useFullColumns = false;
                        $oiStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    }

                    foreach ($cart as $item) {
                        $pid = (int)$item['id'];
                        $qty = max(1, (int)$item['quantity']);

                        // Get price one more time (or you could reuse earlier data)
                        $pStmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                        $pStmt->bind_param("i", $pid);
                        $pStmt->execute();
                        $r = $pStmt->get_result()->fetch_assoc();
                        $price = $r ? (float)$r['price'] : 0.0;
                        $pStmt->close();

                        if ($useFullColumns) {
                            $size = $item['size'] ?? '';
                            $color = $item['color'] ?? '';
                            $oiStmt->bind_param("iiidss", $order_id, $pid, $qty, $price, $size, $color);
                        } else {
                            $oiStmt->bind_param("iiid", $order_id, $pid, $qty, $price);
                        }
                        $oiStmt->execute();
                    }

                    if ($oiStmt) $oiStmt->close();

                    // Optionally update user's phone (if logged in) - uncomment to enable
                    if (isset($_SESSION['user_id']) && !empty($phone)) {
                        // $upd = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
                        // $uid = (int)$_SESSION['user_id'];
                        // $upd->bind_param("si", $phone, $uid);
                        // $upd->execute();
                        // $upd->close();
                    }

                    // Confirmation email (silent failure allowed)
                    $subject = "Your Order Confirmation (#$order_number)";
                    $message = "Hello $name,\n\nThank you for your purchase! Your order number is $order_number.\n\nDelivery Address: $delivery_address\nPhone: $phone\n\nWe will contact you soon.";
                    @mail($email, $subject, $message, "From: no-reply@akuua.com");

                    // Clear cart
                    $_SESSION['cart'] = [];

                    echo "<div class='alert alert-success'>Order placed successfully! Your order number is <strong>" . htmlspecialchars($order_number) . "</strong>.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Failed to create order: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            }
        }
    }
}
?>

<style>
  body { background: #fff; color: #333; }
  .payment-logo { height: 60px; max-width: 150px; object-fit: contain; }
  @media (max-width: 576px) { .payment-logo { height: 45px; margin-bottom: 10px; } }
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

  <?php if (empty($cart)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="../index.php">Continue shopping</a></div>
  <?php endif; ?>

  <!-- Checkout Form -->
  <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_email ?: ($_POST['email'] ?? '')) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Phone Number</label>
      <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user_phone ?: ($_POST['phone'] ?? '')) ?>" placeholder="e.g. 0991 234 567" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Delivery Address</label>
      <textarea name="delivery_address" class="form-control" rows="3" placeholder="e.g. Blantyre CTS / Lilongwe Speed Courier" required><?= htmlspecialchars($_POST['delivery_address'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Payment Method</label>
      <select name="payment_method" class="form-select" required onchange="showAccount(this.value)">
        <option value="">-- Select Payment Method --</option>
        <option value="Mpamba" <?= (($_POST['payment_method'] ?? '') === 'Mpamba') ? 'selected' : '' ?>>Mpamba</option>
        <option value="Airtel Money" <?= (($_POST['payment_method'] ?? '') === 'Airtel Money') ? 'selected' : '' ?>>Airtel Money</option>
        <option value="Bank Transfer" <?= (($_POST['payment_method'] ?? '') === 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
      </select>
    </div>

    <div id="account_details" class="alert alert-info" style="display:none;"></div>

    <div class="mb-3">
      <label class="form-label">Upload Payment Proof (Screenshot / Receipt)</label>
      <input type="file" name="proof" class="form-control">
    </div>

    <h4>Total: MWK <?= number_format($total,2) ?></h4>

    <button type="submit" name="place_order" class="btn btn-success btn-lg mt-3" <?= empty($cart) ? 'disabled' : '' ?>>Place Order</button>
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
