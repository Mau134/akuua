<?php
session_start();
require_once __DIR__ . "/../config/db.php"; // centralized DB + session

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $size = isset($_GET['size']) ? trim($_GET['size']) : null;
    $color = isset($_GET['color']) ? trim($_GET['color']) : null;

    // Create unique cart key based on product + size + color
    $cartKey = $id . "_" . $size . "_" . $color;

    if (!isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey] = [
            'id' => $id,
            'qty' => 1,
            'size' => $size,
            'color' => $color
        ];
    } else {
        $_SESSION['cart'][$cartKey]['qty']++;
    }

    // Redirect back to shop
    header("Location: ../index.php?added=1");
    exit;
}

// Remove item
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    unset($_SESSION['cart'][$key]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items from DB
$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map(function ($item) {
        return intval($item['id']);
    }, $_SESSION['cart']);
    $ids = implode(",", array_unique($ids));

    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }

    foreach ($_SESSION['cart'] as $key => $cartItem) {
        if (isset($products[$cartItem['id']])) {
            $product = $products[$cartItem['id']];
            $cartItem['name'] = $product['name'];
            $cartItem['price'] = $product['price'];
            $cartItem['subtotal'] = $cartItem['qty'] * $product['price'];
            $total += $cartItem['subtotal'];
            $items[$key] = $cartItem;
        }
    }
}

include __DIR__ . "/../includes/header.php";
?>
<style>
  body {
    position: relative;
    background: url("../assets/img/shop1.jpg") center center fixed;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-color: #f8f9fa;
    color: #333;
    z-index: 0;
  }

  body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    z-index: -1;
  }

  /* Desktop: normal table */
  .cart-table {
    display: table;
    width: 100%;
  }

  /* Mobile: show cards instead of table */
  @media (max-width: 768px) {
    .cart-table {
      display: none;
    }

    .cart-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      margin-bottom: 15px;
      padding: 15px;
    }

    .cart-card h5 {
      font-size: 1rem;
      margin-bottom: 8px;
      font-weight: 600;
    }

    .cart-card p {
      margin: 2px 0;
      font-size: 0.9rem;
    }

    .cart-card .subtotal {
      font-weight: bold;
      color: #28a745;
    }

    .cart-card .remove-btn {
      margin-top: 10px;
    }
  }
</style>

<div class="container py-5">
  <h2 class="mb-4 text-center">Your Cart</h2>
  
  <?php if ($items): ?>
    <!-- Desktop Table View -->
    <div class="table-responsive cart-table">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Color</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $key => $item): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($item['name']) ?></td>
              <td><?= htmlspecialchars($item['size'] ?: '-') ?></td>
              <td><?= htmlspecialchars($item['color'] ?: '-') ?></td>
              <td>MWK <?= number_format($item['price'],2) ?></td>
              <td><?= $item['qty'] ?></td>
              <td class="fw-semibold">MWK <?= number_format($item['subtotal'],2) ?></td>
              <td>
                <a href="cart.php?remove=<?= urlencode($key) ?>" class="btn btn-sm btn-outline-danger">✕ Remove</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="5" class="text-end fw-bold">Total</td>
            <td colspan="2" class="fw-bold text-success fs-5">
              MWK <?= number_format($total,2) ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div class="d-md-none">
      <?php foreach ($items as $key => $item): ?>
        <div class="cart-card">
          <h5><?= htmlspecialchars($item['name']) ?></h5>
          <p>Size: <?= htmlspecialchars($item['size'] ?: '-') ?></p>
          <p>Color: <?= htmlspecialchars($item['color'] ?: '-') ?></p>
          <p>Price: MWK <?= number_format($item['price'],2) ?></p>
          <p>Qty: <?= $item['qty'] ?></p>
          <p class="subtotal">Subtotal: MWK <?= number_format($item['subtotal'],2) ?></p>
          <a href="cart.php?remove=<?= urlencode($key) ?>" class="btn btn-sm btn-outline-danger remove-btn">✕ Remove</a>
        </div>
      <?php endforeach; ?>
      <div class="cart-card">
        <h5>Total</h5>
        <p class="subtotal fs-5">MWK <?= number_format($total,2) ?></p>
      </div>
    </div>

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

<?php include __DIR__ . "/../includes/footer.php"; ?>
