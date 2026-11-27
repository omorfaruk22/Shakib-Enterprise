<?php if ($msg = flash('error')): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<h1 class="font-display font-extrabold text-2xl text-brand-navy mb-1">Create Account</h1>
<p class="text-sm text-gray-500 mb-6">Register to track your bookings and get faster service.</p>
<form method="POST" action="<?= url('/register') ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
    <input type="text" name="name" required value="<?= htmlspecialchars(old('name')) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="Your full name"></div>
  <div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone Number</label>
    <input type="tel" name="phone" required value="<?= htmlspecialchars(old('phone')) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="01XXXXXXXXX"></div>
  <div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Email (optional)</label>
    <input type="email" name="email" value="<?= htmlspecialchars(old('email')) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="you@example.com"></div>
  <div class="grid grid-cols-2 gap-3">
    <div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label><input type="password" name="password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy"></div>
    <div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm</label><input type="password" name="confirm_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy"></div>
  </div>
  <button type="submit" class="w-full btn-accent py-3 rounded-lg font-semibold"><i class="fa-solid fa-user-plus mr-2"></i> Create Account</button>
</form>
<div class="mt-5 text-center text-sm text-gray-600">Already have an account? <a href="<?= url('/login') ?>" class="text-brand-navy font-semibold hover:underline">Login</a></div>
