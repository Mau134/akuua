<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ADD TO CART
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $size = $_GET['size'] ?? null;
    $color = $_GET['color'] ?? null;

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

    header("Location: ../index.php?added=1");
    exit;
}

// REMOVE ITEM
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit;
}

// UPDATE QUANTITY
if (isset($_GET['update']) && isset($_GET['qty'])) {
    $key = $_GET['update'];
    $qty = max(1, intval($_GET['qty']));
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = $qty;
    }
    header("Location: cart.php");
    exit;
}

// FETCH CART ITEMS
$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_unique(array_map(fn($i) => intval($i['id']), $_SESSION['cart']));
    $result = $conn->query("SELECT * FROM products WHERE id IN (" . implode(",", $ids) . ")");
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }

    foreach ($_SESSION['cart'] as $key => $cartItem) {
        if (isset($products[$cartItem['id']])) {
            $p = $products[$cartItem['id']];
            $cartItem['name'] = $p['name'];
            $cartItem['price'] = $p['price'];
            $cartItem['subtotal'] = $p['price'] * $cartItem['qty'];
            $items[$key] = $cartItem;
            $total += $cartItem['subtotal'];
        }
    }
}

include __DIR__ . "/../includes/header.php";
?>

<style>
body {
  position: relative;
  background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
  background-color: #f8f9fa;
}
body::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.7);
  z-index: -1;
}
.table-responsive {
  background: rgba(255,255,255,0.95);
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  padding: 20px;
}
.cart-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  padding: 15px;
  margin-bottom: 15px;
}
.cart-card h5 {
  font-weight: 600;
  margin-bottom: 5px;
}
.cart-card .subtotal {
  color: #28a745;
  font-weight: bold;
}
.cart-card .remove-btn {
  margin-top: 10px;
}
@media (max-width: 768px) {
  .table-responsive { display: none; }
}
@media (min-width: 769px) {
  .cart-card { display: none; }
}
</style>

<div class="container py-5">
  <h2 class="text-center mb-4">🛒 Your Shopping Cart</h2>

  <?php if ($items): ?>
    <!-- Desktop Table -->
    <div class="table-responsive">
      <table class="table align-middle text-center">
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
          <?php foreach ($items as $key => $i): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($i['name']) ?></td>
            <td><?= htmlspecialchars($i['size'] ?: '-') ?></td>
            <td><?= htmlspecialchars($i['color'] ?: '-') ?></td>
            <td>MWK <?= number_format($i['price'],2) ?></td>
            <td>
              <div class="input-group input-group-sm justify-content-center" style="max-width:120px;">
                <a href="?update=<?= urlencode($key) ?>&qty=<?= max(1,$i['qty']-1) ?>" class="btn btn-outline-secondary">−</a>
                <input type="text" readonly class="form-control text-center" value="<?= $i['qty'] ?>">
                <a href="?update=<?= urlencode($key) ?>&qty=<?= $i['qty']+1 ?>" class="btn btn-outline-secondary">+</a>
              </div>
            </td>
            <td class="fw-semibold text-success">MWK <?= number_format($i['subtotal'],2) ?></td>
            <td><a href="?remove=<?= urlencode($key) ?>" class="btn btn-sm btn-outline-danger">✕</a></td>
          </tr>
          <?php endforeach; ?>
          <tr class="fw-bold table-success">
            <td colspan="5" class="text-end">Total</td>
            <td colspan="2">MWK <?= number_format($total,2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <?php foreach ($items as $key => $i): ?>
      <div class="cart-card">
        <h5><?= htmlspecialchars($i['name']) ?></h5>
        <p>Size: <?= htmlspecialchars($i['size'] ?: '-') ?></p>
        <p>Color: <?= htmlspecialchars($i['color'] ?: '-') ?></p>
        <p>Price: MWK <?= number_format($i['price'],2) ?></p>
        <p>Qty: 
          <a href="?update=<?= urlencode($key) ?>&qty=<?= max(1,$i['qty']-1) ?>" class="btn btn-sm btn-outline-secondary">−</a>
          <?= $i['qty'] ?>
          <a href="?update=<?= urlencode($key) ?>&qty=<?= $i['qty']+1 ?>" class="btn btn-sm btn-outline-secondary">+</a>
        </p>
        <p class="subtotal">Subtotal: MWK <?= number_format($i['subtotal'],2) ?></p>
        <a href="?remove=<?= urlencode($key) ?>" class="btn btn-sm btn-outline-danger remove-btn">Remove</a>
      </div>
    <?php endforeach; ?>
    <div class="cart-card text-center fw-bold fs-5">
      Total: MWK <?= number_format($total,2) ?>
    </div>

    <!-- Buttons -->
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
