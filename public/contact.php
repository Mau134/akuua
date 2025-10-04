<?php
session_start();
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";

// --- Handle form submission ---
if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $to = "akuuastore@gmail.com";
    $subject = "New Contact Message from $name";
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $headers = "From: no-reply@akuua.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $adminMail = mail($to, $subject, $body, $headers);

    // Confirmation to user
    $userSubject = "Your message to Akuua Store was received!";
    $userMessage = "Hello $name,\n\nThank you for contacting Akuua Store.\n\nWe’ve received your message and will respond as soon as possible.\n\nBest regards,\nAkuua Store Team";
    $userHeaders = "From: no-reply@akuua.com\r\n";

    $userMail = mail($email, $userSubject, $userMessage, $userHeaders);

    if ($adminMail) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Message Sent!',
            'text' => "Thank you, $name! Your message has been sent successfully. Please check your email for confirmation."
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Message Failed!',
            'text' => "Sorry, your message could not be sent. Please try again later."
        ];
    }

    header("Location: contact.php"); // Prevent form resubmission
    exit;
}
?>

<!-- STYLES SAME AS BEFORE -->
<style>
  body {
    position: relative;
    background: url("../assets/img/shop1.jpg") center/cover no-repeat fixed;
    background-color: #f8f9fa;
    color: #333;
  }

  body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255,255,255,0.7);
    z-index: -1;
  }

  .contact-container {
    max-width: 700px;
    margin: 80px auto;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    padding: 40px;
  }

  .btn-primary {
    border-radius: 10px;
    padding: 12px;
    background: linear-gradient(135deg, #007bff, #00bfff);
    border: none;
    font-weight: 600;
    transition: 0.3s;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #0099cc);
    transform: translateY(-2px);
  }
</style>

<div class="container">
  <div class="contact-container">
    <div class="text-center mb-3">
      <i class="fas fa-envelope-circle-check fa-3x text-primary"></i>
    </div>
    <h2 class="text-center fw-bold">Contact Us</h2>
    <p class="text-center text-muted mb-4">Have questions, feedback, or need assistance? We'd love to hear from you.</p>

    <form action="contact.php" method="post">
      <div class="mb-3">
        <label for="name" class="form-label"><i class="fas fa-user me-2"></i> Full Name</label>
        <input type="text" name="name" id="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i> Email Address</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="message" class="form-label"><i class="fas fa-comment-dots me-2"></i> Your Message</label>
        <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
      </div>

      <button type="submit" name="submit" class="btn btn-primary w-100">
        <i class="fas fa-paper-plane me-2"></i> Send Message
      </button>
    </form>
  </div>
</div>

<!-- SweetAlert2 for Popup -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Show popup alert if it exists
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo "<script>
        Swal.fire({
            icon: '{$alert['type']}',
            title: '{$alert['title']}',
            text: '{$alert['text']}',
            confirmButtonColor: '#007bff'
        });
    </script>";
    unset($_SESSION['alert']);
}
?>

<?php include __DIR__ . "/../includes/footer.php"; ?>
