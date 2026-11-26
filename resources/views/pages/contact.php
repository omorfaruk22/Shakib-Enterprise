<?php
declare(strict_types=1);

$pageHeading = 'Contact us';
$pageEyebrow = 'Talk with the team';
$pageLead = 'Use the confirmed contact options below to ask about travel services, destinations or packages.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_.8fr] lg:px-8">
    <div class="rounded-3xl bg-brand-navy p-7 text-white sm:p-9">
      <h2 class="text-2xl font-extrabold">Contact <?= e((string) ($siteSettings['site_name'] ?? 'SAKIB ENTERPRISE')) ?></h2>
      <p class="mt-3 text-blue-100">Share your travel question with the business. The team can confirm current details directly.</p>
      <?php if ($requestedService !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Your inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedService) ?></p><p class="mt-2 text-sm text-blue-100">When you contact the team, include your travel dates, route or destination, and number of travelers.</p></div><?php endif; ?>
      <?php if ($requestedPackage !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Package inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedPackage) ?></p><p class="mt-2 text-sm text-blue-100">Mention your preferred dates and group size when contacting the team.</p></div><?php endif; ?>
      <?php if ($requestedDestination !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Destination inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedDestination) ?></p><p class="mt-2 text-sm text-blue-100">Mention your preferred dates and traveler count when contacting the team.</p></div><?php endif; ?>
      <div class="mt-7 space-y-4">
        <?php if ($phoneHref !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="tel:<?= e($phoneHref) ?>"><span aria-hidden="true">☎</span><span><?= e($phoneDisplay) ?></span></a><?php endif; ?>
        <?php if ($whatsappUrl !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">◉</span><span>Chat on WhatsApp</span></a><?php endif; ?>
        <?php if ($email !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="mailto:<?= e($email) ?>"><span aria-hidden="true">✉</span><span><?= e($email) ?></span></a><?php endif; ?>
        <?php if ($phoneHref === '' && $whatsappUrl === '' && $email === ''): ?><p class="rounded-xl border border-white/20 p-4 text-sm text-blue-100">Phone, WhatsApp and email details will be added after the business confirms them.</p><?php endif; ?>
      </div>
    </div>
    <aside class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
      <h2 class="text-2xl font-extrabold text-brand-navy">Office information</h2>
      <p class="mt-4 text-slate-600"><?= e((string) ($siteSettings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh")) ?></p>
      <?php if (trim((string) ($siteSettings['office_hours'] ?? '')) !== ''): ?><h3 class="mt-6 font-bold text-brand-navy">Opening hours</h3><p class="mt-1 whitespace-pre-line text-slate-600"><?= e((string) $siteSettings['office_hours']) ?></p><?php endif; ?>
      <div class="mt-7 rounded-2xl bg-slate-50 p-5 text-sm leading-6 text-slate-600">For a travel inquiry, include your requested service, route or destination, travel date and traveler count. Availability, prices and booking confirmation are provided by the business.</div>
      <p class="mt-4 text-xs text-slate-500">The website contact form and saved inquiry workflow will be added in the upcoming booking and database phases.</p>
    </aside>
  </div>
</section>
