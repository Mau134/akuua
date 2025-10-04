<?php
session_start();
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";
?>

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

  .contact-container h2 {
    font-weight: 700;
    text-align: center;
    color: #222;
    margin-bottom: 15px;
  }

  .contact-container p {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
  }

  .form-label {
    font-weight: 600;
    color: #333;
  }

  .form-control {
    border-radius: 10px;
    padding: 12px;
    border: 1px solid #ccc;
  }

  .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
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

  .alert {
    max-width: 700px;
    margin: 20px auto;
    border-radius: 10px;
  }

  /* Back to top button */
  #backToTop {
    position: fixed;
    bottom: 25px;
    right: 25px;
    display: none;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff, #00d4ff);
    color: white;
    font-size: 28px;
    line-height: 55px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 9999;
  }

  #backToTop:hover {
    background: linear-gradient(135deg, #0056b3, #0099cc);
    transform: translateY(-4px) scale(1.05);
  }

  .contact-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    font-size: 3rem;
    color: #007bff;
  }
</style>

<div class="container">
  <div class="contact-container">
    <div class="contact-icon">
      <i class="fas fa-envelope-circle-check"></i>
    </div>
    <h2>Contact Us</h2>
    <p>Have questions, feedback, or need assistance? We'd love to hear from you.</p>

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

  <?php
  if (isset($_POST['submit'])) {
      $name = htmlspecialchars($_POST['name']);
      $email = htmlspecialchars($_POST['email']);
      $message = htmlspecialchars($_POST['message']);

      $to = "akuuastores@gmail.com"; // your actual email
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
</div>

<!-- Back to Top -->
<a href="#" id="backToTop" class="btn btn-primary rounded-circle">
  <i class="bi bi-arrow-up-short"></i>
</a>

<script>
window.onscroll = function() {
  let btn = document.getElementById("backToTop");
  if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
      btn.style.display = "block";
  } else {
      btn.style.display = "none";
  }
};

document.getElementById("backToTop").addEventListener("click", function(e) {
  e.preventDefault();
  window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
