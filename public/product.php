<?php
session_start();
require_once "config/db.php";
include "includes/header1.php";

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger text-center'>Product not found.</div>";
    include "includes/footer.php";
    exit;
}
?>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-6">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p class="text-muted">Category: <?= htmlspecialchars($product['category']) ?></p>
      <h4 class="text-success">MWK <?= number_format($product['price'], 2) ?></h4>
      <p><strong>Stock:</strong> <?= $product['stock'] > 0 ? $product['stock'] : "Out of Stock" ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      
      <?php if ($product['stock'] > 0): ?>
        <a href="cart.php?add=<?= $product['id'] ?>" class="btn btn-primary btn-lg">Add to Cart</a>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>
