<?php
declare(strict_types=1);
$serviceLabels = [
    'air_ticket' => 'Air ticket',
    'ship_ticket' => 'Ship ticket',
    'bus_ticket' => 'Bus ticket',
    'tour_package' => 'Tour package',
    'holiday_package' => 'Holiday package',
];
?>
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
  <div><a class="text-sm font-semibold text-brand-red hover:underline" href="<?= e(app_url('/admin/bookings')) ?>">← All inquiries</a><h1 class="mt-3 text-3xl font-extrabold text-brand-navy"><?= e_value($booking['booking_reference']) ?></h1><p class="mt-1 text-slate-600"><?= e($serviceLabels[$booking['service_type']] ?? 'Travel inquiry') ?> · received <?= e_value($booking['created_at']) ?></p></div>
  <span class="w-fit rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-800"><?= e_value(ucfirst($booking['status'])) ?></span>
</div>
<div class="mt-7 grid gap-6 xl:grid-cols-[1.4fr_.8fr]">
  <div class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
      <h2 class="text-xl font-extrabold text-brand-navy">Travel details</h2>
      <form class="mt-5 space-y-4" action="<?= e(app_url('/admin/bookings/' . (int) $booking['id'] . '/details')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <div class="grid gap-4 sm:grid-cols-2">
          <label><span class="block text-sm font-semibold">Departure / origin</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="departure_location" maxlength="180" value="<?= e_value($booking['departure_location'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Destination</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="destination" maxlength="180" value="<?= e_value($booking['destination'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Journey date</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="journey_date" type="date" value="<?= e_value($booking['journey_date'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Return date</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="return_date" type="date" value="<?= e_value($booking['return_date'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Travelers</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="passenger_count" type="number" min="1" max="50" value="<?= e_value($booking['passenger_count'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Traveler mix</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="passenger_type" maxlength="80" value="<?= e_value($booking['passenger_type'] ?? '') ?>"></label>
          <label><span class="block text-sm font-semibold">Travel class</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="travel_class" maxlength="80" value="<?= e_value($booking['travel_class'] ?? '') ?>"></label>
          <label class="sm:col-span-2"><span class="block text-sm font-semibold">Customer message</span><textarea class="mt-1 min-h-28 w-full rounded-lg border border-slate-300 px-3 py-2" name="customer_message" maxlength="4000"><?= e_value($booking['customer_message'] ?? '') ?></textarea></label>
        </div>
        <button class="rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900" type="submit">Save travel details</button>
      </form>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
      <h2 class="text-xl font-extrabold text-brand-navy">Internal notes</h2>
      <form class="mt-4" action="<?= e(app_url('/admin/bookings/' . (int) $booking['id'] . '/notes')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label class="block text-sm font-semibold" for="booking-note">Add a note visible to admins only</label>
        <textarea class="mt-2 min-h-24 w-full rounded-lg border border-slate-300 px-3 py-2" id="booking-note" name="note" maxlength="10000" required></textarea>
        <button class="mt-3 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold hover:bg-slate-50" type="submit">Add note</button>
      </form>
      <?php if ($notes === []): ?><p class="mt-5 text-sm text-slate-500">No internal notes.</p><?php else: ?><ul class="mt-5 space-y-3"><?php foreach ($notes as $note): ?><li class="rounded-xl bg-slate-50 p-4"><p class="whitespace-pre-line text-sm"><?= e_value($note['note']) ?></p><p class="mt-2 text-xs text-slate-500"><?= e_value($note['admin_name']) ?> · <?= e_value($note['created_at']) ?></p></li><?php endforeach; ?></ul><?php endif; ?>
    </section>
  </div>
  <aside class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-lg font-extrabold text-brand-navy">Customer</h2>
      <a class="mt-3 inline-block font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/customers/' . (int) $booking['customer_id'])) ?>"><?= e_value($booking['customer_name']) ?></a>
      <p class="mt-2 text-sm text-slate-600"><?= e_value($booking['customer_phone']) ?></p>
      <?php if (($booking['customer_email'] ?? '') !== ''): ?><p class="mt-1 break-all text-sm text-slate-600"><?= e_value($booking['customer_email']) ?></p><?php endif; ?>
      <p class="mt-5 border-t border-slate-200 pt-4 text-sm text-slate-600">Departure: <?= e_value($booking['departure_location'] ?: $booking['ship_origin'] ?: 'Not specified') ?><br>Destination: <?= e_value($booking['destination'] ?: $booking['ship_destination'] ?: 'Not specified') ?><br>Travel date: <?= e_value($booking['journey_date'] ?: 'Not specified') ?></p>
      <?php if (trim((string) ($booking['package_title'] ?? '')) !== ''): ?><p class="mt-3 text-sm font-semibold">Package: <?= e_value($booking['package_title']) ?></p><?php endif; ?>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-lg font-extrabold text-brand-navy">Update status</h2>
      <form class="mt-4 space-y-4" action="<?= e(app_url('/admin/bookings/' . (int) $booking['id'] . '/status')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label class="block text-sm font-semibold">Status<select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="status"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>"<?= $booking['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
        <label class="block text-sm font-semibold">Status note (optional)<textarea class="mt-1 min-h-20 w-full rounded-lg border border-slate-300 px-3 py-2" name="note" maxlength="500"></textarea></label>
        <button class="w-full rounded-lg bg-brand-red px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700" type="submit">Save status</button>
      </form>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-lg font-extrabold text-brand-navy">Status history</h2>
      <?php if ($history === []): ?><p class="mt-3 text-sm text-slate-500">No status history recorded.</p><?php else: ?><ol class="mt-4 space-y-4"><?php foreach ($history as $event): ?><li class="border-l-2 border-brand-red pl-4"><p class="font-bold"><?= e_value($event['old_status'] ?: 'Created') ?> → <?= e_value($event['new_status']) ?></p><?php if (($event['note'] ?? '') !== ''): ?><p class="mt-1 text-sm text-slate-600"><?= e_value($event['note']) ?></p><?php endif; ?><p class="mt-1 text-xs text-slate-500"><?= e_value($event['admin_name'] ?? 'System') ?> · <?= e_value($event['created_at']) ?></p></li><?php endforeach; ?></ol><?php endif; ?>
    </section>
    <section class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-lg font-extrabold text-red-800">Delete inquiry</h2>
      <p class="mt-2 text-sm text-slate-600">This removes the inquiry, its notes, and its status history. The customer record remains.</p>
      <form class="mt-4 space-y-3" action="<?= e(app_url('/admin/bookings/' . (int) $booking['id'] . '/delete')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label class="flex items-start gap-2 text-sm text-slate-700"><input class="mt-1" name="confirm_delete" type="checkbox" value="1" required><span>I confirm this inquiry should be deleted.</span></label>
        <button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50" type="submit">Delete inquiry</button>
      </form>
    </section>
  </aside>
</div>
