<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";
include "./includes/header.php";

$id = intval($_GET['id']);

// ✅ Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("❌ Product not found.");
}

// ✅ Update product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category']; // ✅ comes directly from form

    $stmt = $conn->prepare("UPDATE products 
        SET name=?, description=?, price=?, stock=?, category=? 
        WHERE id=?");
    $stmt->bind_param("ssdi si", $name, $desc, $price, $stock, $category, $id);
    $stmt->execute();

    header("Location: products.php");
    exit;
}
?>

<h2>Edit Product</h2>

<form method="post" class="mt-3">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" 
           value="<?= htmlspecialchars($product['name']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Price</label>
    <input type="number" step="0.01" name="price" class="form-control" 
           value="<?= $product['price'] ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Stock</label>
    <input type="number" name="stock" class="form-control" 
           value="<?= $product['stock'] ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Category</label>
    <input type="text" name="category" class="form-control" 
           value="<?= htmlspecialchars($product['category']) ?>" required>
    <!-- If you prefer a dropdown, replace this input with <select> -->
  </div>

  <button type="submit" class="btn btn-primary">Update</button>
  <a href="products.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include "./includes/footer.php"; ?>
