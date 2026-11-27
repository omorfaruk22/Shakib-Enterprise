<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">My Profile</h1></div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-xl">
<?php if ($msg = flash('success')): ?><div class="mb-4 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<form method="POST" action="<?= url('/admin/profile') ?>" class="space-y-4"><?= csrf_field() ?>
<?php partial('input', ['name'=>'name','label'=>'Name','required'=>true,'value'=>$admin['name']]); partial('input', ['name'=>'email','label'=>'Email','type'=>'email','required'=>true,'value'=>$admin['email']]); ?>
<hr class="border-gray-100"><p class="text-sm text-gray-500">Leave password fields empty to keep current password.</p>
<?php partial('input', ['name'=>'current_password','label'=>'Current Password','type'=>'password']); partial('input', ['name'=>'new_password','label'=>'New Password (min 8 chars)','type'=>'password']); partial('input', ['name'=>'confirm_password','label'=>'Confirm New Password','type'=>'password']); ?>
<button class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm"><i class="fa-solid fa-save mr-1"></i> Update Profile</button></form>
</div>
