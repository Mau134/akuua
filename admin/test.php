<?php
// Run this once in a standalone PHP file to create an admin user
require_once "../config/db.php";

$email = "admin@akuua.com";
$password = "Admin123!";
$role = "admin";
$hash = password_hash($password, PASSWORD_DEFAULT);

// Check if user exists
$result = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (email, password_hash, role) 
            VALUES ('$email', '$hash', '$role')";
    if ($conn->query($sql)) {
        echo "✅ Admin user created. Email: $email | Password: $password";
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "⚠️ User already exists. Update instead.";
}
$conn->close();
?>