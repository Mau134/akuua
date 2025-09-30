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
  <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- ✅ Mobile scaling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
      color: #333;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 1rem;
    }
    /* Overlay to dim the background */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.7);
      z-index: -1;
    }
    .thankyou-box {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 12px;
      padding: 2rem;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    h1 {
      font-size: 1.8rem;
    }
    @media (min-width: 576px) {
      h1 {
        font-size: 2.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="thankyou-box">
    <h1 class="text-success mb-3">✅ Thank you for shopping with us!</h1>
    <p class="mb-4">We appreciate your business.</p>
    <a href="../index.php" class="btn btn-primary btn-lg w-100 w-sm-auto">Back to Home</a>
  </div>
</body>
</html>
