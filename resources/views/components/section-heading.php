<?php
// $title, $subtitle, $center = true, $accent = false
$center = $center ?? true;
$accent = $accent ?? false;
?>
<div class="<?= $center ? 'text-center mx-auto' : '' ?> max-w-2xl mb-10">
  <?php if (!empty($eyebrow)): ?>
    <div class="inline-block text-brand-red font-semibold text-sm tracking-widest uppercase mb-2"><?= htmlspecialchars($eyebrow) ?></div>
  <?php endif; ?>
  <h2 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-3"><?= htmlspecialchars($title ?? '') ?></h2>
  <?php if (!empty($subtitle)): ?>
    <p class="text-gray-600 leading-relaxed<?= $center ? ' mx-auto' : '' ?>"><?= htmlspecialchars($subtitle) ?></p>
  <?php endif; ?>
  <?php if ($accent): ?>
    <div class="h-1 w-16 <?= $center ? 'mx-auto' : '' ?> bg-brand-red mt-4 rounded-full"></div>
  <?php endif; ?>
</div>
