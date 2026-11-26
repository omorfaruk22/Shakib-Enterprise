<?php
declare(strict_types=1);

$pageHeading = 'Travel Services';
$pageEyebrow = 'How can we help?';
$pageLead = 'Choose the type of travel support you need. The team will review your inquiry and confirm details directly.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <?php if (!$pageService->databaseAvailable()): ?>
      <p class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">The database is temporarily unavailable. General service information is shown below.</p>
    <?php endif; ?>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($services as $service): ?>
        <a class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand-navy/30 hover:shadow-lg" href="<?= e(app_url($pageService->servicePath((string) $service['slug']))) ?>">
          <span class="mb-5 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true"><?= e((string) ($service['icon'] ?? '✦')) ?></span>
          <h2 class="text-xl font-bold text-brand-navy group-hover:text-brand-red"><?= e((string) $service['name']) ?></h2>
          <p class="mt-2 text-sm leading-6 text-slate-600"><?= e((string) $service['description']) ?></p>
          <span class="mt-5 inline-flex font-bold text-brand-red">View service <span class="ml-2" aria-hidden="true">→</span></span>
        </a>
      <?php endforeach; ?>
    </div>
    <p class="mt-8 rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">All travel availability, prices and booking details are confirmed directly by the business. Submitting an inquiry does not issue a ticket or confirm a booking.</p>
  </div>
</section>
