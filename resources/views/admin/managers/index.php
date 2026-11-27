<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Admin Users & Managers</h1>
  <a href="<?= url('/admin/managers/create') ?>" class="btn-accent px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-plus mr-1"></i> Add User</a>
</div>

<div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 mb-5 text-sm">
  <i class="fa-solid fa-circle-info mr-2"></i>
  <strong>Gmail বা যেকোনো ইমেইল সাপোর্টেড:</strong> এখানে @gmail.com, @yahoo.com, @company.com যেকোনো ইমেইল দিয়ে admin/manager অ্যাকাউন্ট তৈরি করতে পারবেন। প্রত্যেকে নিজের ইমেইল ও পাসওয়ার্ড দিয়ে লগইন করবে।
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
<table class="w-full text-sm">
  <thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider">
    <tr>
      <th class="p-3">Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Last Login</th>
      <th class="text-right">Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($admins as $a):
    $roleCls = [
      'super_admin' => 'bg-purple-100 text-purple-700',
      'admin' => 'bg-brand-navy text-white',
      'manager' => 'bg-blue-100 text-blue-700',
    ][$a['role']] ?? 'bg-gray-100 text-gray-700';
  ?>
    <tr class="border-t hover:bg-gray-50">
      <td class="p-3 font-semibold"><?= htmlspecialchars($a['name']) ?></td>
      <td class="text-gray-700"><?= htmlspecialchars($a['email']) ?>
        <?php if (str_ends_with($a['email'], '@gmail.com')): ?>
          <span class="ml-1 inline-flex items-center gap-1 text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold"><i class="fa-brands fa-google"></i> Gmail</span>
        <?php endif; ?>
      </td>
      <td><span class="px-2 py-1 rounded text-xs font-bold <?= $roleCls ?>"><?= ucfirst(str_replace('_',' ',$a['role'])) ?></span></td>
      <td><span class="text-xs <?= $a['status']==='active'?'text-green-600 font-bold':'text-gray-400' ?>"><?= ucfirst($a['status']) ?></span></td>
      <td class="text-xs text-gray-500"><?= $a['last_login_at'] ? htmlspecialchars(date('d M Y H:i',strtotime($a['last_login_at']))) : 'Never' ?></td>
      <td class="text-right whitespace-nowrap">
        <a href="<?= url('/admin/managers/'.$a['id'].'/edit') ?>" class="text-brand-navy font-bold text-xs mr-2">Edit</a>
        <?php if ((int)$admin['id'] !== (int)$a['id']): ?>
        <form method="POST" action="<?= url('/admin/managers/'.$a['id'].'/delete') ?>" data-confirm="Delete this user?" class="inline"><?= csrf_field() ?><button class="text-red-600 text-xs font-bold">Delete</button></form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
