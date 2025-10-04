<?php
include "./includes/header.php";
?>

<div class="container mt-5">
  <h2 class="text-center mb-4">Contact Us</h2>
  <p class="text-center text-muted mb-5">
    Have questions, feedback, or need assistance? We'd love to hear from you.
  </p>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <form action="contact.php" method="post">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="message" class="form-label">Your Message</label>
          <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
        </div>

        <button type="submit" name="submit" class="btn btn-primary w-100">Send Message</button>
      </form>
    </div>
  </div>

  <?php
  if (isset($_POST['submit'])) {
      $name = htmlspecialchars($_POST['name']);
      $email = htmlspecialchars($_POST['email']);
      $message = htmlspecialchars($_POST['message']);

      $to = "akuuastores@gmail.com"; // 🔧 replace with your actual support email
      $subject = "New Contact Form Message from $name";
      $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
      $headers = "From: $email";

      if (mail($to, $subject, $body, $headers)) {
          echo "<div class='alert alert-success mt-4 text-center'>Thank you for contacting us! We’ll get back to you soon.</div>";
      } else {
          echo "<div class='alert alert-danger mt-4 text-center'>Sorry, your message couldn’t be sent. Please try again later.</div>";
      }
  }
  ?>
</div>

<?php include "./includes/footer.php"; ?>
