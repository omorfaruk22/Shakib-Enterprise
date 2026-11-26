<?php
declare(strict_types=1);

$pageHeading = (string) $aboutPage['title'];
$pageEyebrow = 'About us';
$pageLead = "A travel business based in " . (string) ($siteSettings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh") . '.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
    <img class="max-h-[440px] w-full rounded-3xl object-cover shadow-lg" src="<?= e(app_url('assets/images/coxs-bazar-beach.webp')) ?>" alt="Beach shoreline at Cox's Bazar" loading="lazy">
    <article><h2 class="text-2xl font-extrabold text-brand-navy">Travel information from a local team</h2><div class="mt-4 whitespace-pre-line leading-8 text-slate-600"><?= nl2br(e((string) $aboutPage['body'])) ?></div><a class="mt-7 inline-flex rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e(app_url('/contact-us')) ?>">Contact the team →</a></article>
  </div>
</section>
