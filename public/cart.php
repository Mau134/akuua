<?php
session_start();
require_once "../config/db.php";

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $size = isset($_GET['size']) ? $_GET['size'] : null;
    $color = isset($_GET['color']) ? $_GET['color'] : null;

    // Use product_id as key + size + color to support variants
    $key = $id . "-" . ($size ?? "default") . "-" . ($color ?? "default");

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$key] = [
            "product_id" => $id,
            "size"       => $size,
            "color"      => $color,
            "quantity"   => 1
        ];
    }

    $_SESSION['success'] = "Item added to cart!";
    header("Location: cart.php");
    exit;
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
    header("Location: cart.php");
    exit;
}

// Clear cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Cart count for badge
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// --- CART DISPLAY PAGE ---
include "../includes/header.php";
?>
<div class="container py-5">
    <h2>Your Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="text-muted">Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_id']) ?></td>
                    <td><?= htmlspecialchars($item['size']) ?></td>
                    <td><?= htmlspecialchars($item['color']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>
                        <a href="cart.php?remove=<?= urlencode($key) ?>" class="btn btn-sm btn-danger">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="cart.php?clear=1" class="btn btn-warning">Clear Cart</a>
    <?php endif; ?>
</div>
<?php include "../includes/footer.php"; ?>