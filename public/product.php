<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/db.php";
include "../includes/header.php";

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='alert alert-danger text-center'>Product not found.</div>";
    include "../includes/footer.php";
    exit;
}

// Redirect to login if not logged in when adding to cart
if (isset($_GET['add']) && !isset($_SESSION['user_id'])) {
    $redirectUrl = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirectUrl");
    exit;
}
?>
<style>
  body {
    background: url("../assets/img/shop1.jpg") center center fixed;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-color: #f8f9fa;
    color: #333;
  }

  .product-img {
    max-height: 780px;
    width: 100%;
    object-fit: contain;
    border-radius: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
  }

  /* Option card styles */
  .option-card {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    border: 2px solid #ccc;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    user-select: none;
  }
  .option-card:hover {
    border-color: #007bff;
    background: #f0f8ff;
  }
  .option-card.selected {
    border-color: #007bff;
    background: #007bff;
    color: #fff;
    font-weight: bold;
  }
</style>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
           class="img-fluid rounded shadow-sm" 
           alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-6 product-details">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p class="text-muted fs-5">Category: <?= htmlspecialchars($product['category']) ?></p>
      <h4>MWK <?= number_format($product['price'], 2) ?></h4>
      <p><strong>Stock:</strong> <?= $product['stock'] > 0 ? $product['stock'] : "Out of Stock" ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <?php if ($product['stock'] > 0): ?>
        <?php if (!isset($_SESSION['user_id'])): ?>
          <!-- Not logged in: redirect to login -->
          <form action="login.php" method="GET" class="mt-3">
            <input type="hidden" name="redirect" value="product.php?id=<?= $product['id'] ?>">
            <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
          </form>
        <?php else: ?>
          <!-- Logged in: add to cart -->
          <form id="cartForm" action="cart.php" method="GET" class="mt-3">
            <input type="hidden" name="add" value="<?= $product['id'] ?>">
            <input type="hidden" name="size" id="selectedSize">
            <input type="hidden" name="color" id="selectedColor">

            <!-- Size Selection -->
            <div class="mb-3">
              <label class="form-label fw-bold">Select Size:</label><br>
              <?php if (strtolower($product['category']) === 'shoes'): ?>
                <?php for ($i = 36; $i <= 45; $i++): ?>
                  <div class="option-card size-option" data-value="<?= $i ?>"><?= $i ?></div>
                <?php endfor; ?>
              <?php else: ?>
                <?php foreach (["Small","Medium","Large","XL"] as $size): ?>
                  <div class="option-card size-option" data-value="<?= $size ?>"><?= $size ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <!-- Color Selection -->
            <div class="mb-3">
              <label class="form-label fw-bold">Select Color:</label><br>
              <?php foreach (["Black","White","Red","Blue","Green"] as $color): ?>
                <div class="option-card color-option" data-value="<?= $color ?>"><?= $color ?></div>
              <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
          </form>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Handle size selection
document.querySelectorAll('.size-option').forEach(card => {
  card.addEventListener('click', function() {
    document.querySelectorAll('.size-option').forEach(c => c.classList.remove('selected'));
    this.classList.add('selected');
    document.getElementById('selectedSize').value = this.dataset.value;
  });
});

// Handle color selection
document.querySelectorAll('.color-option').forEach(card => {
  card.addEventListener('click', function() {
    document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
    this.classList.add('selected');
    document.getElementById('selectedColor').value = this.dataset.value;
  });
});

// Ensure size & color selected before submitting
document.getElementById('cartForm')?.addEventListener('submit', function(e) {
  if (!document.getElementById('selectedSize').value || !document.getElementById('selectedColor').value) {
    e.preventDefault();
    alert("Please select a size and a color before adding to cart.");
  }
});
</script>

<?php include "../includes/footer.php"; ?>
