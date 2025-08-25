<?php
require_once "../config/db.php";
include "./includes/header.php";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category']; 
    $image_name = null;

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Create a unique name for the uploaded file
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        
        $target_dir = "../uploads/";
        $target_file = $target_dir . $image_name;

        // Move the file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            echo "<div class='alert alert-success'>File uploaded successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to upload file.</div>";
        }
    }

    // Insert into DB
$stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category, image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssdss", $name, $description, $price, $stock, $category, $image_name);
$stmt->execute();


    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product added successfully!</div>";
    } else {
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

    <div class="mb-3">
      <label for="category" class="form-label">Category</label>
      <select name="category" id="category" class="form-control" required>
        <option value="">-- Select Category --</option>
        <option value="Cup">Cup</option>
        <option value="Shoes">Shoes</option>
        <option value="Clothes">Clothes</option>
        <option value="Bags">Bags</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Image</label>
      <input type="file" name="image" class="form-control">
    </div>

    <button type="submit" name="submit" class="btn btn-success">Add Product</button>
  </form>
</div>

<?php include "./includes/footer.php"; ?>
