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
    $main_image = null;

    if (!empty($_FILES['images']['name'][0])) {
        $total_files = count($_FILES['images']['name']);
        if ($total_files > 5) $total_files = 5;

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === 0) {
                $image_name = time() . "_" . basename($_FILES['images']['name'][$i]);
                $target_dir = "../uploads/";
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    // Save in product_images table
                    $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $product_id, $image_name);
                    $img_stmt->execute();

                    // Set first uploaded image as main image
                    if ($i == 0) $main_image = $image_name;
                }
            }
        }
    }

    // ✅ Update main image in products table
    if ($main_image) {
        $update_stmt = $conn->prepare("UPDATE products SET main_image = ? WHERE id = ?");
        $update_stmt->bind_param("si", $main_image, $product_id);
        $update_stmt->execute();
    }

    echo "<div class='alert alert-success'>Product added successfully with images!</div>";
}
 else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
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

    <!-- Custom Category Input -->
    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <input type="text" name="category" id="category" class="form-control" 
             placeholder="Enter a category (e.g. Shoes, Electronics, Bags)" required>
      <small class="form-text text-muted">
        Type a new category or reuse an existing one.
      </small>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Images (max 5)</label>
      <input type="file" name="images[]" class="form-control" multiple>
    </div>

    <button type="submit" name="submit" class="btn btn-success">Add Product</button>
  </form>
</div>

<?php include "./includes/footer.php"; ?>
