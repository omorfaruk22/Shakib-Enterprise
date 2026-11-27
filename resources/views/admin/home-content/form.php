<?php $base = '/admin/' . $section; ?>
<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-extrabold text-2xl text-brand-navy"><?= $item ? 'Edit' : 'Add' ?> <?= htmlspecialchars($cfg['singular']) ?></h1>
  <a href="<?= url($base) ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back to <?= htmlspecialchars($cfg['title']) ?></a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
  <form method="POST" action="<?= $item ? url($base.'/'.$item['id']) : url($base) ?>" class="space-y-4">
    <?= csrf_field() ?>

    <?php foreach ($cfg['fields'] as $f): ?>
      <?php
        $val = $item[$f['name']] ?? ($f['default'] ?? '');
        $type = $f['type'] ?? 'text';
      ?>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= htmlspecialchars($f['label']) ?><?php if (!empty($f['required'])): ?><span class="text-brand-red">*</span><?php endif; ?></label>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= htmlspecialchars($f['name']) ?>" rows="3" placeholder="<?= htmlspecialchars($f['placeholder'] ?? '') ?>" <?= !empty($f['required']) ? 'required' : '' ?> class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy transition"><?= htmlspecialchars($val) ?></textarea>
        <?php else: ?>
          <input type="text" name="<?= htmlspecialchars($f['name']) ?>" value="<?= htmlspecialchars($val) ?>" placeholder="<?= htmlspecialchars($f['placeholder'] ?? '') ?>" <?= !empty($f['required']) ? 'required' : '' ?> class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy transition">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($cfg['hasStep']): ?>
      <?php partial('input', ['name'=>'step_number','label'=>'Step Number (1, 2, 3...)','type'=>'number','value'=>$item['step_number'] ?? 1]); ?>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-4">
      <?php partial('input', ['name'=>'sort_order','label'=>'Sort Order','type'=>'number','value'=>$item['sort_order'] ?? 0]); ?>
      <div class="flex items-center gap-2 mt-6">
        <input type="checkbox" name="is_active" id="a" <?= !$item || !empty($item['is_active']) ? 'checked' : '' ?> class="w-4 h-4">
        <label for="a" class="text-sm font-semibold">Active (visible on homepage)</label>
      </div>
    </div>

    <div class="pt-4 border-t flex items-center gap-3">
      <button class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm"><i class="fa-solid fa-save mr-1"></i> Save</button>
      <a href="<?= url($base) ?>" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
  </form>
</div>
