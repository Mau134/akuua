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
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255, 255, 255, 0.7);
    z-index: -1;
  }

.product-img {
  max-height: 780px;
  width: 100%;
  object-fit: contain;
  border-radius: 15px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}

/* Selection cards */
.option-card {
  display: inline-block;
  padding: 10px 20px;
  margin: 5px;
  border: 2px solid #ccc;
  border-radius: 8px;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease-in-out;
}
.option-card:hover {
  border-color: #007bff;
  background: #f0f8ff;
}
.option-card.selected {
  border-color: #007bff;
  background: #007bff;
  color: white;
  font-weight: bold;
}
</style>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-6 product-details">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p class="text-muted fs-5">Category: <?= htmlspecialchars($product['category']) ?></p>
      <h4>MWK <?= number_format($product['price'], 2) ?></h4>
      <p><strong>Stock:</strong> <?= $product['stock'] > 0 ? $product['stock'] : "Out of Stock" ?></p>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <?php if ($product['stock'] > 0): ?>
      <form action="cart.php" method="GET" id="cartForm" class="mt-3">
        <input type="hidden" name="add" value="<?= $product['id'] ?>">
        <input type="hidden" name="size" id="selectedSize">
        <input type="hidden" name="color" id="selectedColor">

        <!-- Size Selection -->
        <div class="mb-3">
          <label class="form-label fw-bold">Select Size:</label><br>
          <?php if (strtolower($product['category']) === 'shoes'): ?>
            <?php for ($i = 36; $i <= 45; $i++): ?>
              <span class="option-card size-option" data-value="<?= $i ?>"><?= $i ?></span>
            <?php endfor; ?>
          <?php else: ?>
              <span class="option-card size-option" data-value="Small">Small</span>
              <span class="option-card size-option" data-value="Medium">Medium</span>
              <span class="option-card size-option" data-value="Large">Large</span>
              <span class="option-card size-option" data-value="XL">XL</span>
          <?php endif; ?>
        </div>

        <!-- Color Selection -->
        <div class="mb-3">
          <label class="form-label fw-bold">Select Color:</label><br>
          <span class="option-card color-option" data-value="Black">Black</span>
          <span class="option-card color-option" data-value="White">White</span>
          <span class="option-card color-option" data-value="Red">Red</span>
          <span class="option-card color-option" data-value="Blue">Blue</span>
          <span class="option-card color-option" data-value="Green">Green</span>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
      </form>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Size & color card selection
document.querySelectorAll('.size-option').forEach(card => {
  card.addEventListener('click', function() {
    document.querySelectorAll('.size-option').forEach(c => c.classList.remove('selected'));
    this.classList.add('selected');
    document.getElementById('selectedSize').value = this.dataset.value;
  });
});

document.querySelectorAll('.color-option').forEach(card => {
  card.addEventListener('click', function() {
    document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
    this.classList.add('selected');
    document.getElementById('selectedColor').value = this.dataset.value;
  });
});

// Ensure size & color selected before submit
document.getElementById('cartForm').addEventListener('submit', function(e) {
  if (!document.getElementById('selectedSize').value || !document.getElementById('selectedColor').value) {
    e.preventDefault();
    alert("Please select a size and a color before adding to cart.");
  }
});
</script>

<?php include "../includes/footer.php"; ?>
