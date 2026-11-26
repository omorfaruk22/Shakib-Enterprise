<?php
declare(strict_types=1);

$pageHeading = $packageType === 'tour' ? 'Tour Packages' : ($packageType === 'holiday' ? 'Holiday Packages' : 'Travel Packages');
$pageEyebrow = 'Published plans';
$pageLead = 'See the package details currently published by the business. Confirm prices, availability and itinerary directly with the team.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mb-7 flex flex-wrap gap-2" aria-label="Package filters">
      <a class="rounded-full px-4 py-2 text-sm font-bold <?= $packageType === null ? 'bg-brand-navy text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" href="<?= e(app_url('/packages')) ?>">All packages</a>
      <a class="rounded-full px-4 py-2 text-sm font-bold <?= $packageType === 'tour' ? 'bg-brand-navy text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" href="<?= e(app_url('/packages?type=tour')) ?>">Tours</a>
      <a class="rounded-full px-4 py-2 text-sm font-bold <?= $packageType === 'holiday' ? 'bg-brand-navy text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>" href="<?= e(app_url('/packages?type=holiday')) ?>">Holidays</a>
    </div>
    <?php if (!$pageService->databaseAvailable()): ?>
      <p class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Package listings are temporarily unavailable. Please check again later.</p>
    <?php elseif ($packages === []): ?>
      <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center"><h2 class="text-xl font-bold text-brand-navy">No packages published yet</h2><p class="mt-2 text-slate-600">The business will list packages after confirming the itinerary, inclusions and current prices.</p><a class="mt-5 inline-flex font-bold text-brand-red hover:underline" href="<?= e(app_url('/contact-us')) ?>">Ask the team about a trip →</a></div>
    <?php else: ?>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($packages as $package): ?>
          <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <span class="self-start rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold text-brand-navy"><?= ($package['package_type'] ?? '') === 'holiday' ? 'Holiday package' : 'Tour package' ?></span>
            <h2 class="mt-4 text-xl font-bold text-brand-navy"><?= e((string) $package['title']) ?></h2>
            <?php if (trim((string) ($package['destination_name'] ?? '')) !== ''): ?><p class="mt-1 text-sm font-semibold text-slate-500"><?= e((string) $package['destination_name']) ?></p><?php endif; ?>
            <p class="mt-3 text-sm leading-6 text-slate-600"><?= e((string) ($package['summary'] ?? 'Contact the business for package details.')) ?></p>
            <div class="mt-auto border-t border-slate-100 pt-5">
              <div class="flex flex-wrap justify-between gap-2 text-sm text-slate-600">
                <?php if ((int) ($package['duration_days'] ?? 0) > 0): ?><span><?= e((string) $package['duration_days']) ?> days</span><?php endif; ?>
                <?php if (is_numeric($package['price'] ?? null) && (float) $package['price'] > 0): ?><span class="font-bold text-brand-navy"><?= e((string) ($package['currency'] ?? 'BDT')) ?> <?= e(number_format((float) $package['price'], 2)) ?></span><?php else: ?><span>Ask for current price</span><?php endif; ?>
              </div>
              <a class="mt-4 inline-flex font-bold text-brand-red hover:underline" href="<?= e(app_url('/packages/' . rawurlencode((string) $package['slug']))) ?>">View package details →</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p class="mt-8 text-sm text-slate-500">Displayed prices and dates must be reconfirmed with the business. A package inquiry is not a booking confirmation.</p>
  </div>
</section>
