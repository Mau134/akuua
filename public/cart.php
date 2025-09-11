<?php
session_start();
require_once "../config/db.php";
include "../includes/header1.php";

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 1; // first time add
    } else {
        $_SESSION['cart'][$id]++; // increase qty
    }

    // Redirect back to shop instead of staying in cart
    header("Location: ../index.php?added=1");
    exit;
}

// Remove item
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items from DB
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(",", array_keys($_SESSION['cart']));
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['qty'] * $row['price'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>
<style>
  body {
    position: relative;
    background: url("../assets/img/background1.jpg") center center fixed;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-color: #f8f9fa; /* fallback */
    color: #333;
    z-index: 0;
  }

  /* Overlay to dim the background */
  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7); /* white transparent overlay */
    z-index: -1;
  }
</style>
<div class="container py-5">
  <h2 class="mb-4 text-center">Your Cart</h2>
  
  <?php if ($items): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($item['name']) ?></td>
              <td>MWK <?= number_format($item['price'],2) ?></td>
              <td><?= $item['qty'] ?></td>
              <td class="fw-semibold">MWK <?= number_format($item['subtotal'],2) ?></td>
              <td>
                <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger">
                  ✕ Remove
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3" class="text-end fw-bold">Total</td>
            <td colspan="2" class="fw-bold text-success fs-5">
              MWK <?= number_format($total,2) ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Action buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mt-4">
      <a href="../index.php" class="btn btn-outline-secondary btn-lg w-100 w-md-auto">← Continue Shopping</a>
      <a href="checkout.php" class="btn btn-success btn-lg w-100 w-md-auto">Proceed to Checkout →</a>
    </div>

  <?php else: ?>
    <div class="alert alert-info text-center">
      Your cart is empty. <a href="../index.php" class="alert-link">Go shopping</a>
    </div>
  <?php endif; ?>
</div>


<?php include "../includes/footer.php"; ?>
