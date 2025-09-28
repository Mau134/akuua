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

  /* Category bar */
  .category-bar {
    background: #fff;
    border-bottom: 2px solid #ddd;
    padding: 0.7rem 0;
    text-align: center;
  }
  .category-bar a {
    margin: 0 12px;
    text-decoration: none;
    font-weight: 500;
    color: #333;
  }
  .category-bar a:hover {
    color: #007bff;
  }

.product-img {
  max-height: 780px;   /* allow bigger image */
  width: 100%;
  object-fit: contain; /* keep image proportions */
  border-radius: 15px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}

/* Product text styling */
.product-details h2 {
  font-size: 2rem;        /* bigger title */
  font-weight: 700;
  color: #222;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.product-details h4 {
  font-size: 1.6rem;
  font-weight: 600;
  color: #28a745;         /* green for price */
}

.product-details p {
  font-size: 1.1rem;
  line-height: 1.6;
}

.product-details strong {
  font-weight: 700;
  color: #000;
}

  .rating {
    color: #FFD700; /* gold stars */
    font-size: 0.9rem;
  }

#backToTop {
  position: fixed;
  bottom: 25px;
  right: 25px;
  display: none;
  width: 55px;
  height: 55px;
  border-radius: 50%;
  background: linear-gradient(135deg, #007bff, #00d4ff);
  color: white;
  font-size: 28px;
  line-height: 55px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  cursor: pointer;
  transition: all 0.3s ease;
}

#backToTop:hover {
  background: linear-gradient(135deg, #0056b3, #0099cc);
  transform: translateY(-4px) scale(1.05);
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
  <form action="cart.php" method="GET" class="mt-3">
    <input type="hidden" name="add" value="<?= $product['id'] ?>">

<!-- Size Selection -->
<div class="mb-3">
  <label for="size" class="form-label fw-bold">Select Size:</label>
  <select name="size" id="size" class="form-select" required>
    <option value="">-- Choose Size --</option>
    <?php if (strtolower($product['category']) === 'shoes'): ?>
      <?php for ($i = 36; $i <= 45; $i++): ?>
        <option value="<?= $i ?>"><?= $i ?></option>
      <?php endfor; ?>
    <?php else: ?>
      <option value="Small">Small</option>
      <option value="Medium">Medium</option>
      <option value="Large">Large</option>
      <option value="XL">XL</option>
    <?php endif; ?>
  </select>
</div>
    <!-- Color Selection -->
    <div class="mb-3">
      <label for="color" class="form-label fw-bold">Select Color:</label>
      <select name="color" id="color" class="form-select" required>
        <option value="">-- Choose Color --</option>
        <option value="Black">Black</option>
        <option value="White">White</option>
        <option value="Red">Red</option>
        <option value="Blue">Blue</option>
        <option value="Green">Green</option>
      </select>
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
// Show button when scrolling down
window.onscroll = function() {
    let btn = document.getElementById("backToTop");
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
};

// Smooth scroll to top
document.getElementById("backToTop").addEventListener("click", function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
<?php include "../includes/footer.php"; ?>
