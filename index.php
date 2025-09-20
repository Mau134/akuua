<?php
session_start();
require_once "config/db.php";
include "includes/header.php";

// Redirect to login if not logged in and trying to access cart
$_SESSION['redirect_to_cart'] = true;
header("Location: login.php");
exit;


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
    background: rgba(255, 255, 255, 0.7); /* faded overlay */
    z-index: -1;
  }

  /* Login bar */
  .login-bar {
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    padding: 0.5rem 1rem;
    text-align: right;
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
</style>


<!-- Login Bar -->
<?php if (!isset($_SESSION['user_id'])): ?>
  <div class="login-bar">
    <a href="public/login.php" class="btn btn-sm btn-outline-primary">
      <i class="fas fa-user me-1"></i> Log In
    </a>
  </div>
<?php else: ?>
  <div class="login-bar">
    Welcome, <?= htmlspecialchars($_SESSION['username']) ?> |
    <a href="public/logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
  </div>
<?php endif; ?>

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
    <!-- Add more slides if needed -->
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- Category Bar -->
<div class="category-bar">
  <?php foreach ($categories as $cat): ?>
    <a href="#cat-<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?></a>
  <?php endforeach; ?>
</div>

<!-- Products Section -->
<div id="products" class="container py-5">
  <h2 class="text-center mb-5">Our Products</h2>
  <?php foreach ($categories as $cat): ?>
    <h3 id="cat-<?= urlencode($cat) ?>" class="text-center my-4"><?= htmlspecialchars($cat) ?></h3>
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
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height:200px; object-fit:cover;">
              <?php else: ?>
                <img src="assets/no-image.png" class="card-img-top" alt="No image" style="height:200px; object-fit:cover;">
              <?php endif; ?>

              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                <p class="card-text text-muted"><?= substr($row['description'], 0, 70) ?>...</p>
                <p class="fw-bold mb-2 text-success">MWK <?= number_format($row['price'],2) ?></p>
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

<!-- About Us Section -->
<section id="about" class="bg-light py-5">
  <div class="container text-center">
    <h2 class="fw-bold mb-4">About Us</h2>
    <p class="lead mb-4">
      At Akuua Store, we bring you quality products at affordable prices. From fashion to electronics, we ensure customer satisfaction with every order.
    </p>
    <div class="row mt-4">
      <div class="col-md-4">
        <h5>🌍 Our Mission</h5>
        <p>To make online shopping accessible and reliable for everyone in Malawi and beyond.</p>
      </div>
      <div class="col-md-4">
        <h5>🤝 Our Promise</h5>
        <p>Fast delivery, secure payments, and excellent customer service.</p>
      </div>
      <div class="col-md-4">
        <h5>📦 What We Offer</h5>
        <p>Fashion, electronics, home essentials, and more delivered to your doorstep.</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-5">
  <div class="container">
    <h2 class="text-center fw-bold mb-4">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            How do I place an order?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Browse our products, add items to your cart, and proceed to checkout. It's that simple!
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            Do you deliver nationwide?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, we deliver across Malawi. Delivery times may vary depending on your location.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            What payment methods do you accept?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            We accept Visa, MasterCard, mobile money, and cash on delivery (where available).
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include "includes/footer.php"; ?>
