<?php if ($msg = flash('success')): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<h1 class="font-display font-extrabold text-2xl text-brand-navy mb-1">Welcome Back</h1>
<p class="text-sm text-gray-500 mb-6">Login to view your bookings and manage your account.</p>
<form method="POST" action="<?= url('/login') ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone or Email</label>
    <input type="text" name="login" required value="<?= htmlspecialchars(old('login')) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="01XXXXXXXXX or you@example.com" autofocus>
  </div>
  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
    <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="••••••">
  </div>
  <button type="submit" class="w-full btn-primary py-3 rounded-lg font-semibold"><i class="fa-solid fa-right-to-bracket mr-2"></i> Login</button>
</form>
<div class="mt-5 text-center text-sm text-gray-600">Don't have an account? <a href="<?= url('/register') ?>" class="text-brand-red font-semibold hover:underline">Create one</a></div>
<div class="mt-3 text-center"><a href="<?= url('/') ?>" class="text-xs text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Home</a></div>
