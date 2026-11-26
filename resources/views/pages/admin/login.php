<?php
declare(strict_types=1);
?>
<div class="mx-auto max-w-md py-8">
  <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
    <span class="grid size-12 place-items-center rounded-2xl bg-brand-navy text-lg font-extrabold text-white">SE</span>
    <h1 class="mt-6 text-3xl font-extrabold text-brand-navy">Admin sign in</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Sign in to manage travel inquiries and website content.</p>
    <?php if (isset($errors['_form'])): ?><div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e_value($errors['_form']) ?></div><?php endif; ?>
    <form class="mt-7 space-y-5" action="<?= e(app_url('/admin/login')) ?>" method="post">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <div>
        <label class="block text-sm font-bold text-slate-800" for="admin-email">Email address</label>
        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="admin-email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e_value($old['email'] ?? '') ?>">
      </div>
      <div>
        <label class="block text-sm font-bold text-slate-800" for="admin-password">Password</label>
        <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="admin-password" name="password" type="password" autocomplete="current-password" required>
      </div>
      <button class="w-full rounded-xl bg-brand-red px-5 py-3.5 font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2" type="submit">Sign in</button>
    </form>
    <?php if ($setupAvailable): ?><p class="mt-6 border-t border-slate-200 pt-5 text-sm text-slate-600">First administrator? <a class="font-bold text-brand-navy underline underline-offset-4 hover:text-brand-red" href="<?= e(app_url('/admin/setup')) ?>">Open one-time setup</a></p><?php endif; ?>
  </section>
</div>
