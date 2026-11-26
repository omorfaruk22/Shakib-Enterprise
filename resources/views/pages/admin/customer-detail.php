<?php
declare(strict_types=1);
?>
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
  <div><a class="text-sm font-semibold text-brand-red hover:underline" href="<?= e(app_url('/admin/customers')) ?>">← All customers</a><h1 class="mt-3 text-3xl font-extrabold text-brand-navy"><?= e_value($customer['full_name']) ?></h1><p class="mt-1 text-slate-600">Customer record #<?= e_value((string) $customer['id']) ?></p></div>
  <span class="w-fit rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-800"><?= e_value((string) $bookingCount) ?> inquiries</span>
</div>
<div class="mt-7 grid gap-6 xl:grid-cols-[.8fr_1.2fr]">
  <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <h2 class="text-xl font-extrabold text-brand-navy">Edit customer details</h2>
    <form class="mt-5 space-y-4" action="<?= e(app_url('/admin/customers/' . (int) $customer['id'] . '/update')) ?>" method="post">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <label class="block text-sm font-semibold">Name<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="full_name" maxlength="160" required value="<?= e_value($customer['full_name']) ?>"></label>
      <label class="block text-sm font-semibold">Phone<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="phone" maxlength="40" required value="<?= e_value($customer['phone']) ?>"></label>
      <label class="block text-sm font-semibold">Email<input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="email" type="email" maxlength="190" value="<?= e_value($customer['email'] ?? '') ?>"></label>
      <button class="rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900" type="submit">Save customer</button>
    </form>
    <?php if ($bookingCount === 0): ?><form class="mt-7 border-t border-slate-200 pt-5" action="<?= e(app_url('/admin/customers/' . (int) $customer['id'] . '/delete')) ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><label class="flex gap-2 text-sm"><input class="mt-1" name="confirm_delete" type="checkbox" value="1" required><span>I confirm deleting this customer with no booking history.</span></label><button class="mt-3 rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50" type="submit">Delete customer</button></form><?php else: ?><p class="mt-6 border-t border-slate-200 pt-4 text-xs leading-5 text-slate-500">Customer records with booking history are retained to preserve inquiry references. Showing the latest <?= e_value((string) count($bookings)) ?> of <?= e_value((string) $bookingCount) ?> inquiries.</p><?php endif; ?>
  </section>
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4"><h2 class="text-xl font-extrabold text-brand-navy">Inquiry history</h2></div>
    <?php if ($bookings === []): ?><p class="p-8 text-sm text-slate-500">No booking inquiries are linked to this customer.</p><?php else: ?><div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Reference</th><th class="px-5 py-3">Service</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Submitted</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($bookings as $booking): ?><tr><td class="px-5 py-4"><a class="font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/bookings/' . (int) $booking['id'])) ?>"><?= e_value($booking['booking_reference']) ?></a></td><td class="px-5 py-4"><?= e_value(str_replace('_', ' ', $booking['service_type'])) ?></td><td class="px-5 py-4"><?= e_value(ucfirst($booking['status'])) ?></td><td class="px-5 py-4 text-xs"><?= e_value($booking['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
</div>
