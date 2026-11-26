<section class="bg-brand-sky py-12 sm:py-16">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <nav class="mb-4 text-sm text-slate-500" aria-label="Breadcrumb"><a class="hover:text-brand-red" href="<?= e(app_url('/')) ?>">Home</a><span class="mx-2" aria-hidden="true">/</span><span class="text-brand-navy"><?= e($pageHeading) ?></span></nav>
    <?php if (($pageEyebrow ?? '') !== ''): ?><p class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red"><?= e($pageEyebrow) ?></p><?php endif; ?>
    <h1 class="mb-3 mt-2 text-4xl font-extrabold leading-tight text-brand-navy sm:text-5xl"><?= e($pageHeading) ?></h1>
    <?php if (($pageLead ?? '') !== ''): ?><p class="m-0 max-w-3xl text-lg text-slate-600"><?= e($pageLead) ?></p><?php endif; ?>
  </div>
</section>
