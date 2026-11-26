<?php
declare(strict_types=1);
?>
<section class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
  <span class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-xl font-extrabold text-amber-900">!</span>
  <h1 class="mt-5 text-2xl font-extrabold text-brand-navy"><?= e($adminPageTitle) ?></h1>
  <p class="mt-3 leading-7 text-slate-600"><?= e_value($errorMessage ?? 'The requested action could not be completed.') ?></p>
  <a class="mt-6 inline-flex rounded-xl bg-brand-navy px-5 py-3 font-bold text-white hover:bg-blue-900" href="<?= e(app_url('/admin')) ?>">Return to dashboard</a>
</section>
