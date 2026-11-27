<?php
declare(strict_types=1);

$customer = is_array($customer ?? null) ? $customer : [];
$bookings = is_array($bookings ?? null) ? $bookings : [];
$serviceLabels = [
    'air_ticket' => 'Air ticket inquiry',
    'ship_ticket' => 'Ship ticket inquiry',
    'bus_ticket' => 'Bus ticket inquiry',
    'tour_package' => 'Tour inquiry',
    'holiday_package' => 'Holiday package inquiry',
];
$formatDate = static function (mixed $value, string $format = 'd M Y, g:i A'): string {
    if (!is_string($value) || $value === '') {
        return '—';
    }
    $timestamp = strtotime($value . (strlen($value) === 10 ? '' : ' UTC'));
    return $timestamp === false ? '—' : date($format, $timestamp);
};
?>
<section class="bg-slate-50 py-12 sm:py-16">
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <span class="text-xs font-extrabold uppercase tracking-[.14em] text-brand-red">Customer portal</span>
        <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">Hello, <?= e_value($customer['full_name'] ?? 'Customer') ?></h1>
        <p class="mt-2 text-sm text-slate-600">Your inquiry status and reference numbers are listed below.</p>
      </div>
      <form action="<?= e(app_url('/customer/logout')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <button class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100" type="submit">Sign out</button>
      </form>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <?php if ($bookings === []): ?>
        <div class="p-8 text-center"><p class="text-sm text-slate-600">No travel inquiries are linked to your account yet.</p><a class="mt-4 inline-flex rounded-xl bg-brand-red px-5 py-3 font-bold text-white" href="<?= e(app_url('/services')) ?>">Browse services and start an inquiry</a></div>
      <?php else: ?>
        <ul class="divide-y divide-slate-200">
          <?php foreach ($bookings as $booking): ?>
            <li class="p-5 sm:p-6">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500"><?= e_value($serviceLabels[$booking['service_type']] ?? 'Travel inquiry') ?></p>
                  <p class="mt-1 break-all font-mono text-sm font-bold text-brand-navy"><?= e_value($booking['booking_reference']) ?></p>
                </div>
                <span class="w-fit rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold text-brand-navy"><?= e_value(ucfirst(str_replace('_', ' ', (string) $booking['status']))) ?></span>
              </div>
              <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                <div><dt class="text-xs font-semibold text-slate-500">Destination</dt><dd class="mt-1 font-semibold text-slate-800"><?= e_value($booking['destination'] ?: 'Not specified') ?></dd></div>
                <div><dt class="text-xs font-semibold text-slate-500">Journey date</dt><dd class="mt-1 font-semibold text-slate-800"><?= e_value($formatDate($booking['journey_date'] ?? null, 'd M Y')) ?></dd></div>
                <div><dt class="text-xs font-semibold text-slate-500">Last updated</dt><dd class="mt-1 font-semibold text-slate-800"><?= e_value($formatDate($booking['updated_at'] ?? null)) ?></dd></div>
              </dl>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ((int) ($bookingTotal ?? 0) > count($bookings)): ?><p class="border-t border-slate-200 p-4 text-center text-xs text-slate-500">Showing the latest 50 of <?= e_value((string) $bookingTotal) ?> inquiries. Contact the team to review older requests.</p><?php endif; ?>
      <?php endif; ?>
    </div>

    <p class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900">These are inquiry statuses. The team must confirm availability and any ticket or travel arrangements directly with you.</p>
  </div>
</section>
