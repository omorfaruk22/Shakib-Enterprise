<?php
// $testimonial: customer_name, location, rating, message, image
$img = $testimonial['image'] ?? null;
if ($img && !str_starts_with($img, 'http') && !str_starts_with($img, '/')) $img = asset($img);
?>
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full">
  <div class="text-brand-red mb-3">
    <?php for ($i=1; $i<=5; $i++): ?>
      <i class="fa-solid fa-star<?= $i > (int)($testimonial['rating'] ?? 5) ? '-half' : '' ?>"></i>
    <?php endfor; ?>
  </div>
  <p class="text-gray-700 leading-relaxed mb-5 italic">"<?= htmlspecialchars($testimonial['message'] ?? '') ?>"</p>
  <div class="flex items-center gap-3 mt-auto">
    <?php if ($img): ?>
      <img src="<?= htmlspecialchars($img) ?>" alt="" class="w-12 h-12 rounded-full object-cover">
    <?php else: ?>
      <div class="w-12 h-12 rounded-full bg-brand-light text-brand-navy flex items-center justify-center font-bold"><?= htmlspecialchars(strtoupper(substr($testimonial['customer_name'] ?? 'G',0,1))) ?></div>
    <?php endif; ?>
    <div>
      <div class="font-display font-bold text-brand-navy"><?= htmlspecialchars($testimonial['customer_name'] ?? 'Guest') ?></div>
      <?php if (!empty($testimonial['location'])): ?><div class="text-xs text-gray-500"><?= htmlspecialchars($testimonial['location']) ?></div><?php endif; ?>
    </div>
  </div>
</div>
