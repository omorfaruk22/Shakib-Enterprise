<?php
// $service array with: slug, name, short_description, icon, image, url
$url = $url ?? ('/' . ($service['slug'] ?? '#'));
$icon = $service['icon'] ?? 'fa-solid fa-plane';
$img = $service['image'] ?? null;
?>
<a href="<?= url($url) ?>" class="card-hover bg-white rounded-2xl p-6 shadow-sm border border-gray-100 block group">
  <?php if ($img && !str_starts_with($icon, 'fa-')): ?>
    <div class="w-14 h-14 rounded-xl overflow-hidden mb-4"><img src="<?= htmlspecialchars(asset($img)) ?>" alt="" class="w-full h-full object-cover"></div>
  <?php else: ?>
    <div class="w-14 h-14 rounded-xl bg-brand-light text-brand-navy flex items-center justify-center text-2xl mb-4 group-hover:bg-brand-navy group-hover:text-white transition">
      <i class="<?= htmlspecialchars($icon) ?>"></i>
    </div>
  <?php endif; ?>
  <h3 class="font-display font-bold text-lg text-brand-navy mb-2 group-hover:text-brand-red transition"><?= htmlspecialchars($service['name'] ?? '') ?></h3>
  <p class="text-sm text-gray-600 leading-relaxed mb-3"><?= htmlspecialchars($service['short_description'] ?? '') ?></p>
  <span class="text-brand-navy font-semibold text-sm inline-flex items-center gap-1 group-hover:gap-2 transition-all">Learn More <i class="fa-solid fa-arrow-right text-xs"></i></span>
</a>
