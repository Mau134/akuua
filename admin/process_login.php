<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on role
   if ($user['role'] === 'admin') {
    header("Location: /akuua/admin/dashboard.php");
    exit;
} else {
    header("Location: /akuua/index.php");
    exit;
}

        } else {
            die("❌ Invalid password!");
        }
    } else {
        die("❌ User not found!");
    }

    $stmt->close();
    $conn->close();
}
?>
