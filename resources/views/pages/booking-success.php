<?php
declare(strict_types=1);

$pageHeading = 'Inquiry received';
$pageEyebrow = 'Thank you';
$pageLead = 'Your travel inquiry has been saved. The business team will review your details and contact you.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
?>
<section class="px-4 py-12 sm:py-16"><div class="mx-auto max-w-2xl rounded-3xl border border-emerald-200 bg-white p-6 text-center shadow-sm sm:p-10">
  <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-50 text-3xl font-black text-emerald-700" aria-hidden="true">✓</span>
  <h2 class="mt-5 text-2xl font-extrabold text-brand-navy">Your request is with our team</h2>
  <p class="mt-2 text-slate-600">We have received your <?= e_value(strtolower((string) $confirmation['service_name'])) ?>. Keep this reference if you contact us about it.</p>
  <div class="mt-6 rounded-2xl bg-slate-50 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Inquiry reference</p><p class="mt-1 select-all font-mono text-xl font-extrabold tracking-wider text-brand-navy" aria-label="Inquiry reference <?= e_value($confirmation['reference']) ?>"><?= e_value($confirmation['reference']) ?></p><p class="mt-2 text-xs text-slate-500">Received <?= e_value(date('d M Y, g:i A', strtotime((string) $confirmation['created_at']))) ?></p></div>
  <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-left text-sm text-amber-900">This is an inquiry only—not a ticket, payment or confirmed booking. The team will confirm availability and next steps directly.</p>
  <div class="mt-7 flex flex-wrap justify-center gap-3"><a class="rounded-full bg-brand-navy px-5 py-3 font-bold text-white" href="<?= e(app_url('/')) ?>">Return home</a><a class="rounded-full border border-slate-300 px-5 py-3 font-bold text-brand-navy" href="<?= e(app_url('/services')) ?>">Browse services</a></div>
</div></section>
