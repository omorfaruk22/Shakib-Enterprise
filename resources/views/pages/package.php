<?php
declare(strict_types=1);

$pageHeading = (string) $package['title'];
$pageEyebrow = ($package['package_type'] ?? '') === 'holiday' ? 'Holiday package' : 'Tour package';
$pageLead = trim((string) ($package['summary'] ?? '')) ?: 'Contact the business for confirmed itinerary and package information.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
$itinerary = json_decode((string) ($package['itinerary'] ?? '[]'), true);
$textList = static function (?string $value): array {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value) ?: []), static fn (string $line): bool => $line !== ''));
};
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.25fr_.75fr] lg:px-8">
    <article>
      <h2 class="text-2xl font-extrabold text-brand-navy">Package overview</h2>
      <p class="mt-4 whitespace-pre-line leading-7 text-slate-600"><?= e((string) ($package['description'] ?? 'Detailed package information has not been added yet.')) ?></p>
      <?php if (is_array($itinerary) && $itinerary !== []): ?>
        <h3 class="mt-8 text-xl font-bold text-brand-navy">Itinerary</h3>
        <ol class="mt-4 space-y-3">
          <?php foreach ($itinerary as $day => $detail): ?><li class="rounded-xl border border-slate-200 p-4"><span class="font-bold text-brand-navy">Day <?= e((string) (is_numeric($day) ? ((int) $day + 1) : $day)) ?>:</span> <span class="text-slate-600"><?= e(is_scalar($detail) ? (string) $detail : '') ?></span></li><?php endforeach; ?>
        </ol>
      <?php endif; ?>
      <div class="mt-8 grid gap-6 sm:grid-cols-2">
        <?php foreach (['Inclusions' => $textList($package['inclusions'] ?? null), 'Exclusions' => $textList($package['exclusions'] ?? null)] as $listTitle => $items): ?>
          <section class="rounded-2xl bg-slate-50 p-5"><h3 class="font-bold text-brand-navy"><?= e($listTitle) ?></h3><?php if ($items === []): ?><p class="mt-2 text-sm text-slate-500">Details will be confirmed by the business.</p><?php else: ?><ul class="mt-3 list-inside list-disc space-y-2 text-sm text-slate-600"><?php foreach ($items as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul><?php endif; ?></section>
        <?php endforeach; ?>
      </div>
    </article>
    <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <dl class="space-y-4 text-sm">
        <div><dt class="text-slate-500">Package type</dt><dd class="mt-1 font-bold text-brand-navy"><?= ($package['package_type'] ?? '') === 'holiday' ? 'Holiday package' : 'Tour package' ?></dd></div>
        <?php if (trim((string) ($package['destination_name'] ?? '')) !== ''): ?><div><dt class="text-slate-500">Destination</dt><dd class="mt-1 font-bold text-brand-navy"><?= e((string) $package['destination_name']) ?></dd></div><?php endif; ?>
        <?php if ((int) ($package['duration_days'] ?? 0) > 0): ?><div><dt class="text-slate-500">Duration</dt><dd class="mt-1 font-bold text-brand-navy"><?= e((string) $package['duration_days']) ?> days</dd></div><?php endif; ?>
        <div><dt class="text-slate-500">Current price</dt><dd class="mt-1 font-bold text-brand-navy"><?php if (is_numeric($package['price'] ?? null) && (float) $package['price'] > 0): ?><?= e((string) ($package['currency'] ?? 'BDT')) ?> <?= e(number_format((float) $package['price'], 2)) ?><?php else: ?>Contact for current price<?php endif; ?></dd></div>
      </dl>
      <p class="mt-5 text-xs text-slate-500">The team must confirm availability, price and itinerary before any booking is final.</p>
      <a class="mt-6 inline-flex w-full justify-center rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e(app_url('/contact-us?package=' . rawurlencode((string) $package['slug']))) ?>">Ask about this package →</a>
    </aside>
  </div>
</section>
