<?php $base = '/admin/' . $section; ?>
<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy"><?= htmlspecialchars($cfg['title']) ?></h1>
  <a href="<?= url($base.'/create') ?>" class="btn-accent px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-plus mr-1"></i> Add <?= htmlspecialchars($cfg['singular']) ?></a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider">
      <tr>
        <th class="p-3">Order</th>
        <?php if ($cfg['hasStep']): ?><th>Step #</th><?php endif; ?>
        <?php if (!empty($cfg['fields'][0]) && $cfg['fields'][0]['name'] === 'icon'): ?><th>Icon</th><?php endif; ?>
        <th><?= $cfg['hasNumber'] ? 'Value / Label' : 'Title' ?></th>
        <?php if ($cfg['hasNumber']): ?><?php else: ?><th>Description</th><?php endif; ?>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr class="border-t hover:bg-gray-50">
          <td class="p-3 text-xs text-gray-500"><?= (int)$it['sort_order'] ?></td>
          <?php if ($cfg['hasStep']): ?><td class="font-bold text-brand-red"><?= (int)$it['step_number'] ?></td><?php endif; ?>
          <?php if (!empty($cfg['fields'][0]) && $cfg['fields'][0]['name'] === 'icon'): ?>
            <td class="text-center"><i class="<?= htmlspecialchars($it['icon']) ?> text-brand-navy text-lg"></i></td>
          <?php endif; ?>
          <td class="font-semibold text-brand-navy">
            <?php if ($cfg['hasNumber']): ?>
              <span class="font-display font-extrabold text-brand-red"><?= htmlspecialchars($it['number']) ?></span>
              <span class="text-gray-600 font-normal text-xs ml-1"><?= htmlspecialchars($it['label']) ?></span>
            <?php else: ?>
              <?= htmlspecialchars($it['title']) ?>
            <?php endif; ?>
          </td>
          <?php if (!$cfg['hasNumber']): ?>
            <td class="text-xs text-gray-600 max-w-xs truncate"><?= htmlspecialchars(mb_substr($it['description'] ?? '', 0, 80)) ?><?= mb_strlen($it['description'] ?? '') > 80 ? '...' : '' ?></td>
          <?php endif; ?>
          <td><?= !empty($it['is_active']) ? '<span class="text-green-600 text-xs font-bold">Active</span>' : '<span class="text-gray-400 text-xs">Inactive</span>' ?></td>
          <td class="text-right text-xs whitespace-nowrap p-3">
            <a href="<?= url($base.'/'.$it['id'].'/edit') ?>" class="text-brand-navy font-semibold mr-2">Edit</a>
            <form method="POST" action="<?= url($base.'/'.$it['id'].'/delete') ?>" data-confirm="Delete this <?= strtolower($cfg['singular']) ?>?" class="inline">
              <?= csrf_field() ?>
              <button class="text-red-600 font-semibold">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($items)): ?>
        <tr><td colspan="6" class="p-8 text-center text-gray-400"><i class="fa-solid fa-inbox text-3xl mb-2 block"></i>No <?= strtolower($cfg['title']) ?> yet. <a href="<?= url($base.'/create') ?>" class="text-brand-red font-semibold">Add one</a></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<p class="text-xs text-gray-500 mt-3"><i class="fa-solid fa-circle-info mr-1"></i> Tip: Use <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="underline">Font Awesome Free</a> icon names like <code class="bg-gray-100 px-1 rounded">fa-solid fa-shield-halved</code>.</p>
