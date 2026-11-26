<?php
declare(strict_types=1);

$pageHeading = (string) $service['name'];
$pageEyebrow = 'Travel service';
$pageLead = (string) $service['description'];
require BASE_PATH . '/resources/views/partials/page-heading.php';
$serviceSlug = (string) $service['slug'];
$supportsBookingForm = \App\Services\BookingService::definition($serviceSlug) !== null;
$bookingUrl = $supportsBookingForm
    ? app_url('/booking/' . rawurlencode($serviceSlug))
    : app_url('/contact-us?service=' . rawurlencode($serviceSlug));
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.3fr_.7fr] lg:px-8">
    <article>
      <p class="text-lg leading-8 text-slate-700"><?= e((string) $service['intro']) ?></p>
      <h2 class="mt-9 text-2xl font-extrabold text-brand-navy">Details to prepare</h2>
      <p class="mt-2 text-slate-600">These details help the team understand your request and respond with the right information.</p>
      <ul class="mt-5 grid gap-3 sm:grid-cols-2">
        <?php foreach ($service['fields'] as $field): ?>
          <li class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-slate-700"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-brand-sky text-brand-navy" aria-hidden="true">✓</span><?= e((string) $field) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if (($service['slug'] ?? '') === 'ship-ticket'): ?>
        <h2 class="mt-9 text-2xl font-extrabold text-brand-navy">Active ship routes</h2>
        <?php if (!$pageService->databaseAvailable()): ?>
          <p class="mt-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Route information is temporarily unavailable.</p>
        <?php elseif ($shipRoutes === []): ?>
          <p class="mt-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">No ship routes have been published yet. Contact the team to ask about current routes.</p>
        <?php else: ?>
          <ul class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach ($shipRoutes as $route): ?><li class="rounded-xl border border-slate-200 p-4"><p class="font-bold text-brand-navy"><?= e((string) $route['origin']) ?> <span class="text-brand-red" aria-hidden="true">→</span> <?= e((string) $route['destination']) ?></p><?php if (trim((string) ($route['description'] ?? '')) !== ''): ?><p class="mt-1 text-sm text-slate-600"><?= e((string) $route['description']) ?></p><?php endif; ?></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (($service['slug'] ?? '') === 'tour-travel' || ($service['slug'] ?? '') === 'holiday-package'): ?>
        <a class="mt-8 inline-flex font-bold text-brand-red hover:underline" href="<?= e(app_url('/packages')) ?>">Browse published packages <span class="ml-2" aria-hidden="true">→</span></a>
      <?php endif; ?>
      <a class="mt-8 inline-flex rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e($bookingUrl) ?>"><?= $supportsBookingForm ? 'Send an inquiry' : 'Contact the team' ?> →</a>
    </article>
    <aside class="h-fit rounded-3xl bg-brand-navy p-7 text-white sm:p-8">
      <span class="grid size-14 place-items-center rounded-2xl bg-white/10 text-3xl" aria-hidden="true"><?= e((string) ($service['icon'] ?? '✦')) ?></span>
      <h2 class="mt-5 text-2xl font-extrabold">Ask about <?= e((string) $service['name']) ?></h2>
      <p class="mt-3 text-blue-100">Contact the business with your travel dates and preferences. Availability and prices are confirmed directly by the team.</p>
      <a class="mt-6 inline-flex rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e($bookingUrl) ?>">Start an inquiry <span class="ml-2" aria-hidden="true">→</span></a>
      <p class="mt-4 text-xs text-blue-100">This is an inquiry only. No ticket is issued automatically.</p>
    </aside>
  </div>
</section>
