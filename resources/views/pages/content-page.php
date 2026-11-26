<?php
declare(strict_types=1);
$pageHeading = (string) $page['title'];
$pageEyebrow = 'About our business';
$pageLead = trim((string) ($page['meta_description'] ?? ''));
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <article class="mx-auto max-w-4xl whitespace-pre-line px-4 text-lg leading-8 text-slate-700 sm:px-6 lg:px-8"><?= e_value($page['body'] ?? '') ?></article>
</section>
