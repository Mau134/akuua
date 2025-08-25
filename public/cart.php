<?php
session_start();
require_once "../config/db.php";
include "../includes/header.php";

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
    header("Location: cart.php");
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

<div class="container py-5">
  <h2 class="mb-4">Your Cart</h2>
  
  <?php if ($items): ?>
    <table class="table table-bordered">
      <thead>
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
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>MWK <?= number_format($item['price'],2) ?></td>
            <td><?= $item['qty'] ?></td>
            <td>MWK <?= number_format($item['subtotal'],2) ?></td>
            <td>
              <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Remove</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="3" class="text-end fw-bold">Total</td>
          <td colspan="2" class="fw-bold">MWK <?= number_format($total,2) ?></td>
        </tr>
      </tbody>
    </table>

    <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>

  <?php else: ?>
    <p class="text-muted">Your cart is empty.</p>
  <?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>
