<?php
session_start();
require_once "config/db.php";

// Show errors while debugging (remove on production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login status
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Akuua</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <!-- Brand Logo -->
    <a class="navbar-brand" href="../index.php">
      <i class="fas fa-shopping-bag me-2"></i> Akuua
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- About Us -->
        <li class="nav-item">
          <a class="nav-link" href="/index.php#about">
            <i class="fas fa-info-circle me-1"></i> About Us
          </a>
        </li>

        <!-- FAQ -->
        <li class="nav-item">
          <a class="nav-link" href="/index.php#faq">
            <i class="fas fa-question-circle me-1"></i> FAQ
          </a>
        </li>

        <!-- Cart -->
        <li class="nav-item">
          <a class="nav-link" href="/public/cart.php">
            <i class="fas fa-shopping-cart me-1"></i> Cart
          </a>
        </li>

        <!-- Track Order -->
        <li class="nav-item">
          <a class="nav-link" href="/public/order_status.php">
            <i class="fas fa-truck me-1"></i> Track Order
          </a>
        </li>

        <?php if (!$is_logged_in): ?>
          <li class="nav-item">
            <a class="nav-link" href="../public/login.php">
              <i class="fas fa-user me-1"></i> Log In
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i> <?= $username ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="../public/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Main content wrapper -->
<main class="flex-grow-1">
  <div class="container my-5">
