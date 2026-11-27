<?php $_activeNav = ''; ?>
<section class="py-20 min-h-[60vh] flex items-center">
  <div class="max-w-xl mx-auto px-4 text-center">
    <div class="w-20 h-20 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl mx-auto mb-5"><i class="fa-solid fa-check"></i></div>
    <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-3">Thank You! Your Inquiry Has Been Received</h1>
    <?php if (!empty($booking['booking_reference'])): ?>
      <div class="bg-brand-light rounded-xl p-4 mb-5 inline-block">
        <div class="text-xs text-gray-500 mb-1">Your Booking Reference</div>
        <div class="font-display font-extrabold text-2xl text-brand-navy tracking-wider"><?= htmlspecialchars($booking['booking_reference']) ?></div>
      </div>
    <?php endif; ?>
    <p class="text-gray-600 leading-relaxed mb-6">We have received your booking inquiry and one of our agents will contact you shortly at the phone number you provided to confirm details and finalize arrangements.</p>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-sm text-left mb-6">
      <i class="fa-solid fa-circle-info mr-2"></i> <strong>Note:</strong> This is an inquiry submission only. No ticket or booking is confirmed until we contact you and verify availability and payment.
    </div>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?= url('/') ?>" class="btn-primary px-6 py-3 rounded-lg font-semibold"><i class="fa-solid fa-house mr-2"></i> Back to Home</a>
      <?php if (setting('whatsapp')): ?>
        <a href="<?= whatsapp_url('Hello! I just sent a booking inquiry. Reference: ' . ($booking['booking_reference'] ?? '')) ?>" target="_blank" rel="noopener" class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 inline-flex items-center gap-2"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
</section>
