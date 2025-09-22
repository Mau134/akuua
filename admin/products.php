<?php
include '../config/db.php';
include "./includes/header.php";

// Handle delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: products.php");
    exit;
}

// Fetch products grouped by category
$result = $conn->query("SELECT * FROM products ORDER BY category, id DESC");

// Organize products by category
$productsByCategory = [];
while ($row = $result->fetch_assoc()) {
    $productsByCategory[$row['category']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Products by Category</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Manage Products by Category</h2>

  <a href="product_add.php" class="btn btn-primary mb-3">+ Add Product</a>

  <?php if (!empty($productsByCategory)): ?>
    <?php foreach ($productsByCategory as $category => $products): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0"><?= htmlspecialchars($category) ?></h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Image</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $row): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>MWK<?= number_format($row['price'], 2) ?></td>
                <td><?= $row['stock'] > 0 ? $row['stock'] : '<span class="badge bg-danger">Out</span>' ?></td>
                <td>
                  <?php if (!empty($row['image'])): ?>
                    <img src="../uploads/<?= $row['image'] ?>" alt="" style="width:60px; height:60px; object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted">No image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="product_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="alert alert-info">No products found.</div>
  <?php endif; ?>
</div>
<?php include "./includes/footer.php"; ?>
</body>
</html>
