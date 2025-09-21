<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Thank You</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="text-center">
    <h1 class="text-success">✅ Thank you for shopping with us!</h1>
    <p class="mt-3">We appreciate your business.</p>
    <a href="../index.php" class="btn btn-primary mt-4">Back to Home</a>
  </div>
</body>
</html>
<style>
  body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    position: relative;
    min-height: 100vh;
    background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
    color: #333;
    z-index: 0; 
    }
    /* Overlay to dim the background */ 
    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7); /* white transparent overlay */
        z-index: -1; 
    }   
</style>
