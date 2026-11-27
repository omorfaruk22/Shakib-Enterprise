<?php
// page, pages, base url
$page = $page ?? 1; $pages = $pages ?? 1;
if ($pages <= 1) return;
$query = $_GET;
?>
<div class="flex items-center justify-center gap-1 mt-6">
  <?php if ($page > 1): $query['page'] = $page - 1; ?>
    <a href="?<?= http_build_query($query) ?>" class="px-3 py-2 rounded-lg border border-gray-200 text-sm hover:bg-brand-light"><i class="fa-solid fa-chevron-left"></i></a>
  <?php endif; ?>
  <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): $query['page'] = $i; ?>
    <a href="?<?= http_build_query($query) ?>" class="px-3.5 py-2 rounded-lg text-sm border <?= $i === $page ? 'bg-brand-navy text-white border-brand-navy' : 'border-gray-200 hover:bg-brand-light' ?>"><?= $i ?></a>
  <?php endfor; ?>
  <?php if ($page < $pages): $query['page'] = $page + 1; ?>
    <a href="?<?= http_build_query($query) ?>" class="px-3 py-2 rounded-lg border border-gray-200 text-sm hover:bg-brand-light"><i class="fa-solid fa-chevron-right"></i></a>
  <?php endif; ?>
</div>
