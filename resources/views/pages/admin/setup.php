<?php
declare(strict_types=1);
?>
<div class="mx-auto max-w-xl py-8">
  <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
    <a class="text-sm font-semibold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/admin/login')) ?>">← Admin sign in</a>
    <h1 class="mt-5 text-3xl font-extrabold text-brand-navy">One-time administrator setup</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Create the first account. Setup closes as soon as an administrator is added.</p>
    <?php if (!$setupConfigured): ?>
      <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Setup is disabled. Add a random secret of at least 32 characters as <code>ADMIN_SETUP_TOKEN</code> in the protected project <code>.env</code> file, then open this page again.</div>
    <?php elseif (!$setupAvailable): ?>
      <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">Initial setup is closed because an administrator already exists.</div>
    <?php else: ?>
      <?php if (isset($errors['_form'])): ?><div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e_value($errors['_form']) ?></div><?php endif; ?>
      <form class="mt-7 space-y-5" action="<?= e(app_url('/admin/setup')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <div>
          <label class="block text-sm font-bold text-slate-800" for="setup-token">One-time setup secret</label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="setup-token" name="setup_token" type="password" autocomplete="off" required>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="setup-name">Administrator name</label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="setup-name" name="name" type="text" maxlength="160" autocomplete="name" required value="<?= e_value($old['name'] ?? '') ?>">
          <?php if (isset($errors['name'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['name']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="setup-email">Email address</label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="setup-email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e_value($old['email'] ?? '') ?>">
          <?php if (isset($errors['email'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['email']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="setup-password">Password</label>
          <p class="mt-1 text-xs text-slate-500">Use at least 12 characters (maximum 72 bytes).</p>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="setup-password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
          <?php if (isset($errors['password'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['password']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="setup-confirm">Confirm password</label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="setup-confirm" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
          <?php if (isset($errors['password_confirmation'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['password_confirmation']) ?></p><?php endif; ?>
        </div>
        <button class="w-full rounded-xl bg-brand-red px-5 py-3.5 font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2" type="submit">Create first admin</button>
      </form>
      <p class="mt-5 text-xs leading-5 text-slate-500">On a live site, perform setup over HTTPS. Remove the setup secret from <code>.env</code> after creating the account.</p>
    <?php endif; ?>
  </section>
</div>
