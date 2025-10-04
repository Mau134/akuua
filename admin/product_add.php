<?php
require_once "../config/db.php";
include "./includes/header.php";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];

    // Insert product first
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $category);

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;

        // ✅ Handle single image upload
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['error'] === 0) {
                $image_name = time() . "_" . basename($_FILES['image']['name']);
                $target_dir = "../uploads/";
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Save image path in separate table
                    $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $product_id, $image_name);
                    $img_stmt->execute();
                }
            }
        }

        echo "<div class='alert alert-success'>✅ Product added successfully with image!</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Error: " . $stmt->error . "</div>";
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
      <textarea name="description" class="form-control" required></textarea>
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

    <!-- ✅ Single image upload -->
    <div class="mb-3">
      <label class="form-label">Upload Product Image</label>
      <input type="file" name="image" class="form-control" accept="image/*" required>
    </div>

    <button type="submit" name="submit" class="btn btn-success">Add Product</button>
  </form>
</div>

<?php include "./includes/footer.php"; ?>
