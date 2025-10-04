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

// ✅ Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);

    // Start with current image
    $image = $product['image'];

    // ✅ Remove existing image if requested
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if (!empty($product['image']) && file_exists("../uploads/" . $product['image'])) {
            unlink("../uploads/" . $product['image']);
        }
        $image = null;
    }

    // ✅ Handle new image upload
    if (!empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] === 0) {
            $new_image_name = time() . "_" . basename($_FILES['image']['name']);
            $target_dir = "../uploads/";
            $target_file = $target_dir . $new_image_name;

            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image
                if (!empty($product['image']) && file_exists("../uploads/" . $product['image'])) {
                    unlink("../uploads/" . $product['image']);
                }
                $image = $new_image_name;
            } else {
                echo "<div class='alert alert-danger'>❌ Failed to upload new image.</div>";
            }
        }
    }

    // ✅ Update product in database
    $stmt = $conn->prepare("UPDATE products 
        SET name=?, description=?, price=?, stock=?, category=?, image=? 
        WHERE id=?");
    $stmt->bind_param("ssdissi", $name, $desc, $price, $stock, $category, $image, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success mt-3'>✅ Product updated successfully!</div>";
        // Refresh product info
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger mt-3'>❌ Update failed: " . htmlspecialchars($stmt->error) . "</div>";
    }
}
?>

<div class="container mt-4">
  <h2>Edit Product</h2>

  <form method="post" enctype="multipart/form-data" class="mt-3">
    <div class="mb-3">
      <label class="form-label">Product Name</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Price (MWK)</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Stock</label>
      <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($product['stock']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Category</label>
      <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($product['category']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Product Image</label><br>

      <?php if (!empty($product['image']) && file_exists("../uploads/" . $product['image'])): ?>
        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="Product Image" style="max-width:150px; height:auto;" class="mb-2 rounded border">
        <div class="form-check">
          <input type="checkbox" name="remove_image" value="1" id="remove_image" class="form-check-input">
          <label for="remove_image" class="form-check-label text-danger">Remove this image</label>
        </div>
      <?php else: ?>
        <p class="text-muted">No image uploaded yet.</p>
      <?php endif; ?>

      <input type="file" name="image" class="form-control mt-2" accept="image/*">
      <small class="text-muted">Upload a new image to replace the current one.</small>
    </div>

    <button type="submit" class="btn btn-primary">💾 Update Product</button>
    <a href="products.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

<?php include "./includes/footer.php"; ?>
