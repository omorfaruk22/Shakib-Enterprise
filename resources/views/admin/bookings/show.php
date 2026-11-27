<div class="flex items-center justify-between mb-6"><div><h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Booking <?= htmlspecialchars($booking['booking_reference']) ?></h1><p class="text-sm text-gray-500">Submitted: <?= htmlspecialchars(date('d M Y H:i', strtotime($booking['created_at']))) ?></p></div><a href="<?= url('/admin/bookings') ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> All Bookings</a></div>
<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 space-y-5">
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-brand-navy mb-3">Customer Information</h3>
    <div class="grid md:grid-cols-2 gap-3 text-sm"><div><span class="text-gray-500">Name:</span> <strong><?= htmlspecialchars($booking['customer_name']) ?></strong></div>
    <div><span class="text-gray-500">Phone:</span> <a href="tel:<?= htmlspecialchars($booking['customer_phone']) ?>" class="text-brand-navy font-semibold"><?= htmlspecialchars($booking['customer_phone']) ?></a></div>
    <div><span class="text-gray-500">Email:</span> <?= htmlspecialchars($booking['customer_email'] ?? '—') ?></div>
    <div><span class="text-gray-500">Service:</span> <strong class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$booking['service_type'])) ?></strong></div></div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-brand-navy mb-3">Travel Details</h3>
    <div class="grid md:grid-cols-2 gap-3 text-sm"><div><span class="text-gray-500">From:</span> <strong><?= htmlspecialchars($booking['departure'] ?? '—') ?></strong></div><div><span class="text-gray-500">To:</span> <strong><?= htmlspecialchars($booking['destination'] ?? '—') ?></strong></div><div><span class="text-gray-500">Journey Date:</span> <strong><?= htmlspecialchars($booking['journey_date'] ?? '—') ?></strong></div><div><span class="text-gray-500">Return Date:</span> <?= htmlspecialchars($booking['return_date'] ?? '—') ?></div><div><span class="text-gray-500">Passengers:</span> <strong><?= (int)$booking['passenger_count'] ?></strong></div><div><span class="text-gray-500">Class:</span> <?= htmlspecialchars($booking['travel_class'] ?? '—') ?></div></div>
    <?php if (!empty($booking['customer_notes'])): ?><div class="mt-4 p-3 bg-brand-light rounded-lg text-sm"><strong>Customer Notes:</strong><br><?= htmlspecialchars($booking['customer_notes']) ?></div><?php endif; ?>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-brand-navy mb-3">Admin Notes</h3>
    <form method="POST" action="<?= url('/admin/bookings/'.$booking['id'].'/note') ?>" class="mb-4"><?= csrf_field() ?><textarea name="admin_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Internal notes (not visible to customer)..."><?= htmlspecialchars($booking['admin_notes'] ?? '') ?></textarea><button class="mt-2 btn-primary px-4 py-2 rounded-lg text-sm font-semibold">Save Notes</button></form>
    <?php if (!empty($history)): ?><h4 class="font-bold text-brand-navy mb-2">Status History</h4><div class="space-y-2 text-sm"><?php foreach ($history as $h): ?><div class="flex gap-3 items-start border-l-2 border-brand-navy/20 pl-3"><div class="text-xs text-gray-500 w-28 flex-shrink-0"><?= htmlspecialchars(date('d M H:i',strtotime($h['created_at']))) ?></div><div><?php partial('status-badge',['status'=>$h['status']]) ?> <span class="text-gray-600 ml-2"><?= htmlspecialchars($h['note'] ?? 'Status updated') ?></span></div></div><?php endforeach; ?></div><?php endif; ?>
  </div>
</div>
<div class="space-y-4">
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <h4 class="font-bold text-brand-navy mb-3">Update Status</h4>
    <form method="POST" action="<?= url('/admin/bookings/'.$booking['id'].'/status') ?>" class="space-y-3"><?= csrf_field() ?>
    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white"><?php foreach (['Pending','Contacted','Processing','Confirmed','Completed','Cancelled','Rejected'] as $s): ?><option value="<?= $s ?>" <?= $booking['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
    <textarea name="note" rows="2" placeholder="Optional note..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
    <button class="w-full btn-primary py-2 rounded-lg text-sm font-semibold">Update Status</button></form>
    <div class="mt-4 pt-4 border-t">Current Status:<br><?php partial('status-badge', ['status'=>$booking['status']]) ?></div>
  </div>
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <h4 class="font-bold text-brand-navy mb-3">Actions</h4>
    <a href="tel:<?= htmlspecialchars($booking['customer_phone']) ?>" class="btn-primary w-full py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 mb-2"><i class="fa-solid fa-phone"></i> Call Customer</a>
    <?php if (setting('whatsapp')): ?><a href="<?= whatsapp_url('Hello! This is SAKIB ENTERPRISE regarding your booking inquiry ('.$booking['booking_reference'].').') ?>" target="_blank" class="bg-green-500 text-white w-full py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 mb-2"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
    <form method="POST" action="<?= url('/admin/bookings/'.$booking['id'].'/delete') ?>" data-confirm="Delete this booking permanently?" class="mt-3"><?= csrf_field() ?><button class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-trash mr-1"></i> Delete Booking</button></form>
  </div>
</div>
</div>
