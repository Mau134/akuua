<?php
include "./includes/header.php";
?>

<style>
  body {
    background: #f9fafb;
  }

  .contact-container {
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 40px;
    max-width: 700px;
    margin: 50px auto;
    transition: all 0.3s ease;
  }

  .contact-container:hover {
    box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
  }

  .contact-container h2 {
    font-weight: 700;
    color: #222;
  }

  .form-label {
    font-weight: 500;
    color: #444;
  }

  .form-control {
    border-radius: 10px;
    padding: 12px 14px;
    border: 1px solid #ddd;
  }

  .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
  }

  .btn-primary {
    border-radius: 10px;
    padding: 12px;
    background-color: #007bff;
    border: none;
    font-weight: 600;
    transition: 0.3s;
  }

  .btn-primary:hover {
    background-color: #0056b3;
  }

  .contact-header {
    text-align: center;
    margin-bottom: 40px;
  }

  .contact-header i {
    font-size: 3rem;
    color: #007bff;
    margin-bottom: 10px;
  }

  .alert {
    max-width: 700px;
    margin: 20px auto;
    border-radius: 10px;
  }
</style>

<div class="contact-container">
  <div class="contact-header">
    <i class="fas fa-envelope-circle-check"></i>
    <h2>Contact Us</h2>
    <p class="text-muted">
      Have questions, feedback, or need assistance? We'd love to hear from you.
    </p>
  </div>

  <form action="contact.php" method="post">
    <div class="mb-3">
      <label for="name" class="form-label">
        <i class="fas fa-user me-2"></i> Full Name
      </label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">
        <i class="fas fa-envelope me-2"></i> Email Address
      </label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="message" class="form-label">
        <i class="fas fa-comment-dots me-2"></i> Your Message
      </label>
      <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
    </div>

    <button type="submit" name="submit" class="btn btn-primary w-100">
      <i class="fas fa-paper-plane me-2"></i> Send Message
    </button>
  </form>
</div>

<?php
if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $to = "akuuastores@gmail.com"; // ✅ replace with your real email
    $subject = "New Contact Form Message from $name";
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    $headers = "From: $email";

    if (mail($to, $subject, $body, $headers)) {
        echo "<div class='alert alert-success text-center'>
                <i class='fas fa-check-circle me-2'></i>
                Thank you for contacting us! We’ll get back to you soon.
              </div>";
    } else {
        echo "<div class='alert alert-danger text-center'>
                <i class='fas fa-times-circle me-2'></i>
                Sorry, your message couldn’t be sent. Please try again later.
              </div>";
    }
}
?>

<?php include "./includes/footer.php"; ?>
