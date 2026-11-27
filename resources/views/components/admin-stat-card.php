<?php
// label, value, icon, color, url
$color = $color ?? 'navy';
$colors = [
  'navy' => 'bg-brand-navy',
  'red' => 'bg-brand-red',
  'blue' => 'bg-blue-500',
  'green' => 'bg-emerald-500',
  'amber' => 'bg-amber-500',
  'gray' => 'bg-gray-500',
  'purple' => 'bg-purple-500',
];
$bg = $colors[$color] ?? $colors['navy'];
?>
<a href="<?= htmlspecialchars($url ?? '#') ?>" class="block bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-brand transition">
  <div class="flex items-start justify-between">
    <div>
      <div class="text-sm text-gray-500 mb-1"><?= htmlspecialchars($label ?? '') ?></div>
      <div class="font-display font-extrabold text-3xl text-gray-900"><?= htmlspecialchars($value ?? '0') ?></div>
    </div>
    <div class="w-12 h-12 rounded-lg <?= $bg ?> text-white flex items-center justify-center text-xl"><i class="<?= htmlspecialchars($icon ?? 'fa-solid fa-chart-line') ?>"></i></div>
  </div>
</a>
