<?php
// $package (tour/holiday), $type = 'tour'|'holiday'
$type = $type ?? 'tour';
$slug = $package['slug'] ?? '';
$url = $type === 'holiday' ? url('/holiday-packages/' . $slug) : url('/tours/' . $slug);
$img = $package['image'] ?? 'https://images.unsplash.com/photo-1530789253388-582c481c54b0?w=800&auto=format&fit=crop';
if ($img && !str_starts_with($img, 'http') && !str_starts_with($img, '/')) $img = asset($img);
$duration = ($package['duration_days'] ?? 3) . ' Days / ' . ($package['duration_nights'] ?? 2) . ' Nights';
$dest = $package['destination_name'] ?? '';
$price = (float)($package['price'] ?? 0);
?>
<div class="card-hover bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 flex flex-col">
  <a href="<?= $url ?>" class="block relative overflow-hidden">
    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($package['title'] ?? '') ?>" loading="lazy" class="w-full aspect-tour">
    <?php if (!empty($package['is_featured'])): ?>
      <span class="absolute top-3 left-3 bg-brand-red text-white text-xs font-bold px-3 py-1 rounded-full">Featured</span>
    <?php endif; ?>
    <span class="absolute top-3 right-3 bg-white/90 backdrop-blur text-brand-navy text-xs font-semibold px-3 py-1 rounded-full">
      <i class="fa-regular fa-clock mr-1"></i> <?= htmlspecialchars($duration) ?>
    </span>
  </a>
  <div class="p-5 flex flex-col flex-1">
    <?php if ($dest): ?>
      <div class="text-xs text-brand-red font-semibold mb-1"><i class="fa-solid fa-location-dot mr-1"></i> <?= htmlspecialchars($dest) ?></div>
    <?php endif; ?>
    <h3 class="font-display font-bold text-lg text-brand-navy mb-2 line-clamp-2">
      <a href="<?= $url ?>" class="hover:text-brand-red"><?= htmlspecialchars($package['title'] ?? '') ?></a>
    </h3>
    <?php if (!empty($package['short_description'])): ?>
      <p class="text-sm text-gray-600 leading-relaxed line-clamp-2 mb-4 flex-1"><?= htmlspecialchars($package['short_description']) ?></p>
    <?php endif; ?>
    <div class="flex items-end justify-between pt-4 border-t border-gray-100">
      <div>
        <div class="text-xs text-gray-500">Starting from</div>
        <div class="font-display font-extrabold text-xl text-brand-red"><?= format_price($price) ?></div>
      </div>
      <a href="<?= $url ?>" class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-1">View Details <i class="fa-solid fa-arrow-right text-xs"></i></a>
    </div>
  </div>
</div>
