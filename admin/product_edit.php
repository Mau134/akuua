<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require_once "../config/db.php";
include "./includes/header.php";

$id = intval($_GET['id']);

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Fetch all categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=? WHERE id=?");
    $stmt->bind_param("ssdiii", $name, $desc, $price, $stock, $category_id, $id);
    $stmt->execute();

    header("Location: products.php");
    exit;
}
?>

<h2>Edit Product</h2>
<form method="post" class="mt-3">
  <div class="mb-3">
    <label>Name</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
  </div>

  <div class="mb-3">
    <label>Description</label>
    <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
  </div>

  <div class="mb-3">
    <label>Price</label>
    <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
  </div>

  <div class="mb-3">
    <label>Stock</label>
    <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
  </div>

  <div class="mb-3">
    <label>Category</label>
    <select name="category_id" class="form-control" required>
      <option value="">-- Select Category --</option>
      <?php while ($cat = $categories->fetch_assoc()): ?>
        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Update</button>
  <a href="products.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include "./includes/footer.php"; ?>
