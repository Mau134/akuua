<?php
require_once "../config/db.php";
include "./includes/header.php";

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);

    // ✅ Handle single image upload
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] === 0) {
            $image_name = time() . "_" . basename($_FILES['image']['name']);
            $target_dir = "../uploads/";
            $target_file = $target_dir . $image_name;

            // Create uploads folder if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                echo "<div class='alert alert-danger'>❌ Failed to upload image.</div>";
                $image_name = null;
            }
        }
    }

    // ✅ Insert into products table
    $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock, image, created_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Active')");
    $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $image_name);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success mt-3'>✅ Product added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>❌ Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
}
?>

<div class="container mt-4">
  <h2>Add Product</h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Product Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3" required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Price (MWK)</label>
      <input type="number" name="price" class="form-control" step="0.01" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Stock Quantity</label>
      <input type="number" name="stock" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Category</label>
      <input type="text" name="category" class="form-control" placeholder="e.g. Shoes, Electronics, Bags" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Product Image</label>
      <input type="file" name="image" class="form-control" accept="image/*" required>
      <small class="text-muted">Only one image allowed per product.</small>
    </div>

    <button type="submit" name="submit" class="btn btn-success">Add Product</button>
  </form>
</div>

<?php include "./includes/footer.php"; ?>
