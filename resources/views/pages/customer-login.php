<?php
declare(strict_types=1);

$loginError = is_string($errorMessage ?? null) ? $errorMessage : '';
?>
<section class="bg-slate-50 py-14 sm:py-20">
  <div class="mx-auto max-w-xl px-4 sm:px-6">
    <a class="text-sm font-bold text-brand-red hover:underline" href="<?= e(app_url('/')) ?>">← Back to home</a>
    <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-9">
      <span class="text-xs font-extrabold uppercase tracking-[.14em] text-brand-red">Customer access</span>
      <h1 class="mt-2 text-3xl font-extrabold text-brand-navy">Check your inquiry status</h1>
      <p class="mt-3 text-sm leading-6 text-slate-600">Enter the booking reference from your inquiry confirmation and the same phone number you used when you sent it.</p>
      <p class="mt-3 text-sm text-slate-600">Have a customer account? <a class="font-bold text-brand-navy underline" href="<?= e(app_url('/login')) ?>">Sign in or create one</a>.</p>

      <?php if ($loginError !== ''): ?><p class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e($loginError) ?></p><?php endif; ?>

      <form class="mt-6 space-y-5" action="<?= e(app_url('/customer/login')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label class="block text-sm font-semibold text-slate-700" for="booking-reference">Booking reference
          <input class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm uppercase tracking-wide" id="booking-reference" name="booking_reference" type="text" pattern="SE-[0-9]{6}-[A-Fa-f0-9]{16}" maxlength="26" placeholder="SE-202611-0123456789ABCDEF" autocomplete="off" autocapitalize="characters" required>
        </label>
        <label class="block text-sm font-semibold text-slate-700" for="customer-phone">Phone number used for the inquiry
          <input class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" id="customer-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" placeholder="e.g. 017XXXXXXXX" required>
        </label>
        <button class="w-full rounded-xl bg-brand-red px-5 py-3 font-bold text-white transition hover:bg-red-700" type="submit">View my inquiries</button>
      </form>

      <p class="mt-5 text-xs leading-5 text-slate-500">Your booking reference is shown after you submit an inquiry. This page shows inquiry progress only; it does not issue a ticket or confirm travel.</p>
      <p class="mt-4 text-sm text-slate-600">Lost your reference? <a class="font-bold text-brand-navy underline" href="<?= e(app_url('/contact-us')) ?>">Contact SAKIB ENTERPRISE</a>.</p>
    </div>
  </div>
</section>
