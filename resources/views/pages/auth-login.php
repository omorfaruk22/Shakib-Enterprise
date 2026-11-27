<?php
declare(strict_types=1);

$nextPath = is_string($nextPath ?? null) ? $nextPath : '/customer/dashboard';
$loginError = is_string($loginError ?? null) ? $loginError : '';
$notice = is_string($notice ?? null) ? $notice : '';
$registerErrors = is_array($registerErrors ?? null) ? $registerErrors : [];
$oldRegistration = is_array($oldRegistration ?? null) ? $oldRegistration : [];
$fieldError = static fn (string $key): string => is_string($registerErrors[$key] ?? null) ? $registerErrors[$key] : '';
$oldValue = static fn (string $key): string => e_value($oldRegistration[$key] ?? '');
$inputClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10';
$labelClass = 'block text-sm font-bold text-slate-700';
?>
<section class="bg-slate-50 py-12 sm:py-16">
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
      <span class="text-xs font-extrabold uppercase tracking-[.15em] text-brand-red">Customer and admin access</span>
      <h1 class="mt-3 text-3xl font-extrabold text-brand-navy sm:text-4xl">Sign in to book your trip</h1>
      <p class="mt-3 text-slate-600">Browse services, destinations and packages before signing in. Sign in or create a customer account when you are ready to send a booking inquiry.</p>
    </div>

    <?php if ($notice !== ''): ?><p class="mx-auto mt-6 max-w-3xl rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-900" role="status"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($fieldError('_form') !== '' && $registerErrors !== []): ?><p class="mx-auto mt-4 max-w-3xl rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e($fieldError('_form')) ?></p><?php endif; ?>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
      <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="customer-login-title">
        <span class="inline-flex rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-brand-navy">Returning customer</span>
        <h2 class="mt-4 text-2xl font-extrabold text-brand-navy" id="customer-login-title">Customer sign in</h2>
        <p class="mt-2 text-sm text-slate-600">Use the email and password for your customer account.</p>
        <?php if ($loginError !== ''): ?><p class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e($loginError) ?></p><?php endif; ?>
        <form class="mt-6 space-y-4" action="<?= e(app_url('/login')) ?>" method="post">
          <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
          <input type="hidden" name="next" value="<?= e($nextPath) ?>">
          <label class="<?= $labelClass ?>" for="customer-email">Email address
            <input class="<?= $inputClass ?>" id="customer-email" name="email" type="email" maxlength="190" autocomplete="email" required>
          </label>
          <label class="<?= $labelClass ?>" for="customer-password">Password
            <input class="<?= $inputClass ?>" id="customer-password" name="password" type="password" autocomplete="current-password" required>
          </label>
          <button class="w-full rounded-xl bg-brand-navy px-5 py-3 font-bold text-white transition hover:bg-blue-950" type="submit">Sign in and continue</button>
        </form>
        <p class="mt-5 text-sm text-slate-600">Only tracking an older inquiry? <a class="font-bold text-brand-navy underline" href="<?= e(app_url('/customer/login')) ?>">Use its reference and phone</a>.</p>
      </section>

      <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="customer-register-title">
        <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-brand-red">New customer</span>
        <h2 class="mt-4 text-2xl font-extrabold text-brand-navy" id="customer-register-title">Create an account</h2>
        <p class="mt-2 text-sm text-slate-600">Create your account once, then use it for travel inquiries and status updates.</p>
        <form class="mt-6 space-y-4" action="<?= e(app_url('/register')) ?>" method="post">
          <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
          <input type="hidden" name="next" value="<?= e($nextPath) ?>">
          <label class="<?= $labelClass ?>" for="register-name">Full name
            <input class="<?= $inputClass ?>" id="register-name" name="full_name" maxlength="160" autocomplete="name" value="<?= $oldValue('full_name') ?>" required>
            <?php if ($fieldError('full_name') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('full_name')) ?></span><?php endif; ?>
          </label>
          <label class="<?= $labelClass ?>" for="register-phone">Phone number
            <input class="<?= $inputClass ?>" id="register-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" placeholder="e.g. 017XXXXXXXX" value="<?= $oldValue('phone') ?>" required>
            <?php if ($fieldError('phone') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('phone')) ?></span><?php endif; ?>
          </label>
          <label class="<?= $labelClass ?>" for="register-email">Email address
            <input class="<?= $inputClass ?>" id="register-email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= $oldValue('email') ?>" required>
            <?php if ($fieldError('email') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('email')) ?></span><?php endif; ?>
          </label>
          <p class="-mt-2 text-xs leading-5 text-slate-500">Use an email address you can access. Email verification and password-reset messages are not configured.</p>
          <label class="<?= $labelClass ?>" for="register-reference">Earlier inquiry reference <span class="font-normal text-slate-500">(optional)</span>
            <input class="<?= $inputClass ?> font-mono uppercase tracking-wide" id="register-reference" name="booking_reference" maxlength="26" pattern="SE-[0-9]{6}-[A-Fa-f0-9]{16}" value="<?= $oldValue('booking_reference') ?>" placeholder="Needed only to link an earlier inquiry">
            <?php if ($fieldError('booking_reference') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('booking_reference')) ?></span><?php endif; ?>
          </label>
          <p class="-mt-2 text-xs leading-5 text-slate-500">If this phone number was already used for an inquiry, enter that inquiry's reference to link it to your account.</p>
          <label class="<?= $labelClass ?>" for="register-password">Password
            <input class="<?= $inputClass ?>" id="register-password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <?php if ($fieldError('password') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('password')) ?></span><?php endif; ?>
          </label>
          <label class="<?= $labelClass ?>" for="register-password-confirmation">Confirm password
            <input class="<?= $inputClass ?>" id="register-password-confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
            <?php if ($fieldError('password_confirmation') !== ''): ?><span class="mt-1 block text-xs font-medium text-red-700"><?= e($fieldError('password_confirmation')) ?></span><?php endif; ?>
          </label>
          <button class="w-full rounded-xl bg-brand-red px-5 py-3 font-bold text-white transition hover:bg-red-700" type="submit">Create account and continue</button>
        </form>
      </section>
    </div>

    <section class="mt-6 rounded-3xl bg-brand-deep p-6 text-white sm:p-8" aria-labelledby="admin-login-title">
      <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
        <div>
          <span class="text-xs font-extrabold uppercase tracking-[.14em] text-blue-200">For staff only</span>
          <h2 class="mt-1 text-xl font-extrabold" id="admin-login-title">Administrator access</h2>
          <p class="mt-1 text-sm text-blue-100">Enter the admin Gmail address and the existing administrator password. This is email and password sign-in.</p>
        </div>
        <form class="grid gap-3 sm:grid-cols-2" action="<?= e(app_url('/admin/login')) ?>" method="post">
          <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
          <label class="block text-sm font-bold text-white" for="admin-email">Admin email
            <input class="mt-1 w-full rounded-xl border border-white/20 bg-white px-4 py-3 text-slate-900 outline-none focus:ring-4 focus:ring-white/20" id="admin-email" name="email" type="email" maxlength="190" autocomplete="username" required>
          </label>
          <label class="block text-sm font-bold text-white" for="admin-password">Admin password
            <input class="mt-1 w-full rounded-xl border border-white/20 bg-white px-4 py-3 text-slate-900 outline-none focus:ring-4 focus:ring-white/20" id="admin-password" name="password" type="password" autocomplete="current-password" required>
          </label>
          <button class="rounded-xl bg-white px-5 py-3 font-bold text-brand-navy transition hover:bg-blue-50 sm:col-span-2" type="submit">Sign in to admin panel</button>
        </form>
      </div>
    </section>
  </div>
</section>
