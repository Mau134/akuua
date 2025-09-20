<?php
session_start();
require_once "../config/db.php";

// Show errors while debugging (remove on production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $_SESSION["error"] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION["error"] = "Email is already registered!";
        header("Location: register.php");
        exit();
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Default role = customer
    $role = "customer";

    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("ssss", $username, $email, $hashed, $role);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION["error"] = "Database error: " . $stmt->error;
        header("Location: register.php");
        exit();
    }
}
