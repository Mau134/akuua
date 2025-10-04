<?php include "../includes/header.php"; ?>

<div class="container my-5">
  <h1 class="text-center mb-4">Frequently Asked Questions</h1>
  <div class="accordion" id="faqAccordion">

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          How do I place an order?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Browse our store, add products to your cart, and proceed to checkout. Once payment is confirmed, your order will be processed for delivery.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          What payment methods do you accept?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          We currently support Airtel Money, TNM Mpamba, and bank transfers. More payment methods will be added soon.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          How long does delivery take?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Delivery usually takes 1–3 business days depending on your location. You can track your order anytime using the “Track Order” page.
        </div>
      </div>
    </div>

  </div>
</div>

<?php include "../includes/footer.php"; ?>
