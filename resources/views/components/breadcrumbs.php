<?php
// Usage: partial('breadcrumbs', ['items' => [['label'=>'Home','url'=>'/'], ['label'=>'Tours']]])
$items = $items ?? [];
if (!$items) return;
?>
<div class="bg-brand-light border-b border-blue-100">
  <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy"><?= htmlspecialchars($items[count($items)-1]['label'] ?? '') ?></h1>
    <nav class="flex items-center gap-2 text-sm text-gray-600" aria-label="Breadcrumb">
      <?php foreach ($items as $i => $it): ?>
        <?php if ($i > 0): ?><i class="fa-solid fa-chevron-right text-xs text-gray-400"></i><?php endif; ?>
        <?php if (!empty($it['url']) && $i < count($items) - 1): ?>
          <a href="<?= url($it['url']) ?>" class="hover:text-brand-red"><?= htmlspecialchars($it['label']) ?></a>
        <?php else: ?>
          <span class="text-brand-navy font-medium"><?= htmlspecialchars($it['label']) ?></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </div>
</div>
