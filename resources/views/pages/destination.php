<?php
declare(strict_types=1);

$pageHeading = (string) $destination['name'];
$pageEyebrow = 'Destination guide';
$pageLead = trim((string) ($destination['description'] ?? '')) ?: 'Contact the team for confirmed travel information about this destination.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
$imagePath = ltrim(str_replace('\\', '/', trim((string) ($destination['image_path'] ?? ''))), '/');
$imageUrl = preg_match('#^(?:assets/images|uploads)/[A-Za-z0-9/_-]+\.(?:jpe?g|png|webp|avif)$#i', $imagePath) === 1 && !str_contains($imagePath, '..')
    ? app_url($imagePath)
    : null;
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
    <?php if ($imageUrl !== null): ?><img class="max-h-[480px] w-full rounded-3xl object-cover" src="<?= e($imageUrl) ?>" alt="<?= e((string) $destination['name']) ?>" loading="lazy"><?php else: ?><div class="grid min-h-72 place-items-center rounded-3xl bg-gradient-to-br from-blue-100 to-blue-300 text-sm font-semibold text-brand-navy">Destination photo coming soon</div><?php endif; ?>
    <div><h2 class="text-2xl font-extrabold text-brand-navy">Plan a visit to <?= e((string) $destination['name']) ?></h2><p class="mt-4 whitespace-pre-line leading-7 text-slate-600"><?= e((string) ($destination['description'] ?? 'Destination details have not been added yet.')) ?></p><div class="mt-7 rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">Travel options, dates and prices must be confirmed with the business.</div><a class="mt-6 inline-flex rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e(app_url('/contact-us?destination=' . rawurlencode((string) $destination['slug']))) ?>">Ask about this destination →</a></div>
  </div>
</section>
