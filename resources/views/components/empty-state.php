<?php
// $icon, $title, $description, $adminOnly = true
$icon = $icon ?? 'fa-solid fa-inbox';
$title = $title ?? 'No content yet';
$description = $description ?? 'Content will appear here once added from the Admin Dashboard.';
?>
<div class="text-center py-12 px-4 bg-gray-50 rounded-xl border border-dashed border-gray-200">
  <i class="<?= htmlspecialchars($icon) ?> text-4xl text-gray-300 mb-3"></i>
  <h4 class="font-bold text-gray-700 mb-1"><?= htmlspecialchars($title) ?></h4>
  <p class="text-sm text-gray-500 max-w-sm mx-auto"><?= htmlspecialchars($description) ?></p>
</div>
