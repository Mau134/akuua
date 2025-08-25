<?php
require_once "config/db.php";
include "includes/header.php";

// Fetch distinct categories from the database
$categories = [];
$cat_query = $conn->query("SELECT DISTINCT category FROM products");
while ($row = $cat_query->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!-- Hero Section -->
<section class="bg-dark text-white py-5 mb-5" style="background: url('assets/hero-bg.jpg') center/cover no-repeat;">
  <div class="container text-center py-5">
    <h1 class="display-4 fw-bold">Welcome to Akuua Store</h1>
    <p class="lead mb-4">Discover the best deals on fashion, accessories, and more.</p>
    <a href="#products" class="btn btn-primary btn-lg px-4">Shop Now</a>
  </div>
</section>

<!-- Products Section -->
<div id="products" class="container">
  <?php foreach ($categories as $cat): ?>
    <h2 class="text-center my-4"><?= htmlspecialchars($cat) ?></h2>
    <div class="row g-4 mb-5">
      <?php
      $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
      $stmt->bind_param("s", $cat);
      $stmt->execute();
      $result = $stmt->get_result();
      ?>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm h-100">
              <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($row['name']) ?>" 
                     style="height:200px; object-fit:cover;">
              <?php else: ?>
                <img src="assets/no-image.png" 
                     class="card-img-top" 
                     alt="No image available" 
                     style="height:200px; object-fit:cover;">
              <?php endif; ?>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                <p class="card-text text-muted"><?= substr($row['description'], 0, 70) ?>...</p>
                <p class="fw-bold mb-2 text-success">MWK <?= $row['price'] ?></p>
                <?php if ($row['stock'] <= 0): ?>
                  <span class="badge bg-danger">Out of Stock</span>
                <?php else: ?>
                  <a href="public/cart.php?add=<?= $row['id'] ?>" class="btn btn-primary mt-auto">Add to Cart</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted text-center">No products found in this category.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php include "includes/footer.php"; ?>
