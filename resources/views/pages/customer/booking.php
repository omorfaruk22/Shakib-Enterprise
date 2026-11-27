<?php
\App\Core\View::partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'My Account','url'=>'/customer'],['label'=>$booking['booking_reference']]]); ?>
<section class="py-10">
<div class="max-w-4xl mx-auto px-4">
  <a href="<?= url('/customer') ?>" class="text-sm text-gray-500 hover:text-brand-navy mb-4 inline-block"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard</a>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
    <div class="flex flex-wrap justify-between items-start gap-3 mb-5 pb-5 border-b">
      <div><h1 class="font-display font-extrabold text-2xl text-brand-navy">Booking #<?= htmlspecialchars($booking['booking_reference']) ?></h1><p class="text-sm text-gray-500">Submitted on <?= htmlspecialchars(date('d M Y, H:i', strtotime($booking['created_at']))) ?></p></div>
      <?php \App\Core\View::partial('status-badge', ['status'=>$booking['status']]) ?>
    </div>
    <div class="grid md:grid-cols-2 gap-6 text-sm">
      <div><h4 class="font-bold text-brand-navy mb-3">Travel Information</h4>
        <div class="space-y-2 text-gray-700">
          <div><span class="text-gray-500">Service:</span> <strong class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$booking['service_type'])) ?></strong></div>
          <div><span class="text-gray-500">From:</span> <strong><?= htmlspecialchars($booking['departure'] ?? '—') ?></strong></div>
          <div><span class="text-gray-500">To:</span> <strong><?= htmlspecialchars($booking['destination'] ?? '—') ?></strong></div>
          <div><span class="text-gray-500">Journey Date:</span> <strong><?= htmlspecialchars($booking['journey_date'] ?? '—') ?></strong></div>
          <?php if (!empty($booking['return_date'])): ?><div><span class="text-gray-500">Return Date:</span> <strong><?= htmlspecialchars($booking['return_date']) ?></strong></div><?php endif; ?>
          <div><span class="text-gray-500">Passengers:</span> <strong><?= (int)$booking['passenger_count'] ?></strong></div>
          <?php if (!empty($booking['travel_class'])): ?><div><span class="text-gray-500">Class:</span> <strong><?= htmlspecialchars($booking['travel_class']) ?></strong></div><?php endif; ?>
        </div>
      </div>
      <div><h4 class="font-bold text-brand-navy mb-3">Contact Information</h4>
        <div class="space-y-2 text-gray-700">
          <div><span class="text-gray-500">Name:</span> <strong><?= htmlspecialchars($booking['customer_name']) ?></strong></div>
          <div><span class="text-gray-500">Phone:</span> <strong><?= htmlspecialchars($booking['customer_phone']) ?></strong></div>
          <?php if (!empty($booking['customer_email'])): ?><div><span class="text-gray-500">Email:</span> <strong><?= htmlspecialchars($booking['customer_email']) ?></strong></div><?php endif; ?>
        </div>
        <?php if (!empty($booking['customer_notes'])): ?><div class="mt-4 p-3 bg-brand-light rounded-lg"><strong>Your notes:</strong><br><?= htmlspecialchars($booking['customer_notes']) ?></div><?php endif; ?>
      </div>
    </div>
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800"><i class="fa-solid fa-circle-info mr-2"></i> Our team will contact you shortly on your phone number to confirm availability and pricing. If you have urgent questions, please call us directly or message on WhatsApp.</div>
  </div>
</div>
</section>
