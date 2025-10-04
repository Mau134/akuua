<?php
session_start();
require_once "config/db.php";
include "includes/header.php";

// Handle search
$searchTerm = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
  $searchTerm = trim($_GET['search']);
}

// Fetch distinct categories
$categories = [];
$cat_query = $conn->query("SELECT DISTINCT category FROM products");
while ($row = $cat_query->fetch_assoc()) {
  $categories[] = $row['category'];
}
?>

<style>
  body {
    position: relative;
    background: url("assets/img/shop1.jpg") center center fixed;
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
    height: 180px;
    width: 100%;
    object-fit: cover;
    border-radius: 10px;
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
  z-index: 9999; /* 🔥 This keeps it on top */
}


#backToTop:hover {
  background: linear-gradient(135deg, #0056b3, #0099cc);
  transform: translateY(-4px) scale(1.05);
}
</style>

<!-- Hero Section -->
<div id="heroCarousel" class="carousel slide mb-0" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active" style="background: url('assets/img/hero1.jpg') center/cover no-repeat; height: 80vh;">
      <div class="container text-center text-white d-flex flex-column justify-content-center h-100">
        <h1 class="display-3 fw-bold">Welcome to Akuua Store</h1>
        <p class="lead mb-4">Shop fashion, electronics, accessories, and more at the best prices!</p>
        <a href="#products" class="btn btn-primary btn-lg px-4">Shop Now</a>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="navbar-toggler-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="navbar-toggler-icon"></span>
  </button>
</div>

<!-- Back to Top Button -->
<a href="#" id="backToTop" class="btn btn-primary rounded-circle">
  <i class="bi bi-arrow-up-short"></i>
</a>

<!-- Category Bar -->
<div class="category-bar">
  <?php foreach ($categories as $cat): ?>
    <a href="#cat-<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
  <?php endforeach; ?>
</div>

<!-- 🔍 Search Bar -->
<div class="container mt-4">
  <form method="GET" action="index.php" class="d-flex justify-content-center">
    <input type="text" name="search" class="form-control w-50 me-2" 
           placeholder="Search for products..." value="<?= htmlspecialchars($searchTerm) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
  </form>
</div>

<!-- Products Section -->
<div id="products" class="container py-5">
  <h2 class="text-center mb-5">Our Products</h2>

  <?php
  if (!empty($searchTerm)) {
    echo "<h3 class='text-center my-4'>Search Results for: " . htmlspecialchars($searchTerm) . "</h3>";

    // Improved search query (more accurate)
    $stmt = $conn->prepare("
      SELECT * FROM products 
      WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
      ORDER BY 
        CASE 
          WHEN name = ? THEN 1
          WHEN name LIKE ? THEN 2
          WHEN description LIKE ? THEN 3
          ELSE 4
        END, name ASC
    ");

    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("ssssss", $likeTerm, $likeTerm, $likeTerm, $searchTerm, $likeTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<div class="row g-3 mb-5">';
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
  ?>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
          <div class="card shadow-sm h-100">
            <a href="public/product.php?id=<?= $row['id'] ?>">
              <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($row['name']) ?>">
              <?php else: ?>
                <img src="assets/no-image.png" class="card-img-top product-img" alt="No image">
              <?php endif; ?>
            </a>

            <div class="card-body d-flex flex-column">
              <h6 class="card-title mb-1">
                <a href="public/product.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
                  <?= htmlspecialchars($row['name']) ?>
                </a>
              </h6>
              <p class="fw-bold text-success mb-1">MWK <?= number_format($row['price'],2) ?></p>

              <!-- ⭐ Star Ratings + Verified -->
              <div class="rating mb-2">
                <span class="text-primary fw-semibold ms-2">✔ Verified</span>
              </div>

              <p class="card-text text-muted small mb-2"><?= substr($row['description'], 0, 40) ?>...</p>

              <?php if ($row['stock'] <= 0): ?>
                <span class="badge bg-danger">Out of Stock</span>
              <?php else: ?>
                <?php if (!isset($_SESSION['user_id'])): ?>
                  <a href="/public/login.php?redirect=/index.php&add=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                <?php else: ?>
                  <a href="public/cart.php?add=<?= $row['id'] ?>" class="btn btn-success btn-sm mt-auto">Add to Cart</a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
  <?php
      }
    } else {
      echo "<p class='text-center text-muted'>No products found.</p>";
    }
    echo '</div>';
  } else {
    foreach ($categories as $cat):
  ?>
    <h3 id="cat-<?= urlencode($cat) ?>" class="text-center my-4"><?= htmlspecialchars($cat) ?></h3>
    <div class="row g-3 mb-5">
      <?php
      $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
      $stmt->bind_param("s", $cat);
      $stmt->execute();
      $result = $stmt->get_result();
      ?>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="card shadow-sm h-100">
              <a href="product.php?id=<?= $row['id'] ?>">
                <?php if (!empty($row['image'])): ?>
                  <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                  <img src="assets/no-image.png" class="card-img-top product-img" alt="No image">
                <?php endif; ?>
              </a>

              <div class="card-body d-flex flex-column">
                <h6 class="card-title mb-1">
                  <a href="public/product.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
                    <?= htmlspecialchars($row['name']) ?>
                  </a>
                </h6>
                <p class="fw-bold text-success mb-1">MWK <?= number_format($row['price'],2) ?></p>

                <!-- ⭐ Star Ratings + Verified -->
                <div class="rating mb-2">
                  ★ ★ ★ ★ ☆ <small class="text-muted">(89)</small>
                  <span class="text-primary fw-semibold ms-2">✔ Verified</span>
                </div>

                <p class="card-text text-muted small mb-2"><?= substr($row['description'], 0, 40) ?>...</p>

                <?php if ($row['stock'] <= 0): ?>
                  <span class="badge bg-danger">Out of Stock</span>
                <?php else: ?>
                  <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/public/login.php?redirect=/index.php&add=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                  <?php else: ?>
                    <a href="public/cart.php?add=<?= $row['id'] ?>" class="btn btn-success btn-sm mt-auto">Add to Cart</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted text-center">No products found in this category.</p>
      <?php endif; ?>
    </div>
  <?php endforeach;
  } ?>
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

<?php include "includes/footer.php"; ?>
