<?php
declare(strict_types=1);

$pageHeading = 'Destinations';
$pageEyebrow = 'Explore';
$pageLead = 'Browse destinations published by the business. Travel details appear when they have been confirmed.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <?php if (!$pageService->databaseAvailable()): ?>
      <p class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Destination listings are temporarily unavailable. Please check again later.</p>
    <?php elseif ($destinations === []): ?>
      <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center"><h2 class="text-xl font-bold text-brand-navy">No destinations published yet</h2><p class="mt-2 text-slate-600">Confirmed destination information will appear here after the business publishes it.</p><a class="mt-5 inline-flex font-bold text-brand-red hover:underline" href="<?= e(app_url('/contact-us')) ?>">Contact the team →</a></div>
    <?php else: ?>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($destinations as $destination): ?>
          <?php
          $imagePath = ltrim(str_replace('\\', '/', trim((string) ($destination['image_path'] ?? ''))), '/');
          $imageUrl = preg_match('#^(?:assets/images|uploads)/[A-Za-z0-9/_-]+\.(?:jpe?g|png|webp|avif)$#i', $imagePath) === 1 && !str_contains($imagePath, '..')
              ? app_url($imagePath)
              : null;
          ?>
          <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <?php if ($imageUrl !== null): ?><img class="h-56 w-full object-cover" src="<?= e($imageUrl) ?>" alt="<?= e((string) $destination['name']) ?>" loading="lazy"><?php else: ?><div class="grid h-56 place-items-center bg-gradient-to-br from-blue-100 to-blue-300 text-sm font-semibold text-brand-navy">Photo coming soon</div><?php endif; ?>
            <div class="p-5"><h2 class="text-xl font-bold text-brand-navy"><?= e((string) $destination['name']) ?></h2><p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600"><?= e((string) ($destination['description'] ?? 'Contact the team to ask about this destination.')) ?></p><a class="mt-4 inline-flex font-bold text-brand-red hover:underline" href="<?= e(app_url('/destinations/' . rawurlencode((string) $destination['slug']))) ?>">Explore destination →</a></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
