<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'];
    $amount = $_POST['amount'];

    // Here you would integrate with payment APIs...
    // For now, just simulate
    echo "<div style='padding:20px; font-family:Arial'>";
    echo "<h2>Payment Successful!</h2>";
    echo "<p>You chose <strong>" . htmlspecialchars($method) . "</strong> and paid <strong>MWK " . number_format($amount) . "</strong>.</p>";
    echo "<a href='index.php'>Back to shop</a>";
    echo "</div>";

    // Clear cart after payment
    unset($_SESSION['cart']);
}
?>
