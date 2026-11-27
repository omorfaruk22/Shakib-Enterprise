<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-extrabold text-2xl text-brand-navy"><?= $user ? 'Edit Admin User' : 'Add Admin / Manager' ?></h1>
  <a href="<?= url('/admin/managers') ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-xl">
  <?php if ($msg = flash('error')): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="POST" action="<?= $user ? url('/admin/managers/'.$user['id']) : url('/admin/managers') ?>" class="space-y-4">
    <?= csrf_field() ?>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        <i class="fa-solid fa-envelope text-gray-400 mr-1"></i> Email Address
        <span class="text-xs text-gray-500 font-normal">(Gmail, Yahoo, কাস্টম যেকোনো)</span>
      </label>
      <input type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="you@gmail.com">
      <p class="text-[11px] text-gray-500 mt-1"><i class="fa-brands fa-google text-red-500 mr-1"></i> @gmail.com অ্যাড্রেস দিলে Gmail badge দেখাবে লগইন পেজে</p>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role</label>
      <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white">
        <option value="super_admin" <?= ($user['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin — full access including user management</option>
        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin — content, bookings, settings</option>
        <option value="manager" <?= ($user['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager — bookings/customers/messages only</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        Password <?= $user ? '<span class="text-xs text-gray-500 font-normal">(খালি রাখলে পূর্বের পাসওয়ার্ড বহাল থাকবে)</span>' : '' ?>
      </label>
      <input type="password" name="password" <?= !$user ? 'required' : '' ?> minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="<?= $user ? 'New password (min 8 chars)' : 'Minimum 8 characters' ?>">
    </div>
    <button class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm"><i class="fa-solid fa-save mr-1"></i> <?= $user ? 'Save Changes' : 'Create User' ?></button>
  </form>
</div>
