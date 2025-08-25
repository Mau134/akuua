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

// Fetch products
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Manage Products</h2>

  <a href="product_add.php" class="btn btn-primary mb-3">+ Add Product</a>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>$<?= number_format($row['price'], 2) ?></td>
            <td><?= $row['stock'] > 0 ? $row['stock'] : '<span class="badge bg-danger">Out</span>' ?></td>
            <td><img src="../uploads/<?= $row['image'] ?>" alt="" style="width:60px; height:60px; object-fit:cover;"></td>
            <td>
              <a href="product_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="products.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include "./includes/footer.php"; ?>

</body>
</html>
