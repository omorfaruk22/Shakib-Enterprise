<?php
// $destination array: name, slug, description, image, url
$url = $url ?? ('/tours?destination=' . urlencode($destination['slug'] ?? ''));
$img = $destination['image'] ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&auto=format&fit=crop';
if ($img && !str_starts_with($img, 'http') && !str_starts_with($img, '/')) $img = asset($img);
?>
<a href="<?= htmlspecialchars($url) ?>" class="card-hover relative rounded-2xl overflow-hidden block group shadow-brand">
  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($destination['name'] ?? '') ?>" loading="lazy" class="w-full aspect-[4/5] object-cover group-hover:scale-110 transition-transform duration-500">
  <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
  <div class="absolute bottom-0 left-0 right-0 p-5 text-white">
    <h3 class="font-display font-bold text-xl mb-1"><?= htmlspecialchars($destination['name'] ?? '') ?></h3>
    <?php if (!empty($destination['description'])): ?>
      <p class="text-sm text-white/80 line-clamp-2"><?= htmlspecialchars(mb_substr($destination['description'], 0, 80)) ?>...</p>
    <?php endif; ?>
    <div class="mt-3 text-sm font-semibold inline-flex items-center gap-1 text-white"><i class="fa-solid fa-location-dot text-brand-red"></i> Explore</div>
  </div>
</a>
