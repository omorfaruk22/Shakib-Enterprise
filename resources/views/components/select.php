<?php
// name, label, options (value=>label), value, required
$name = $name ?? '';
$label = $label ?? '';
$value = $value ?? old($name);
$required = $required ?? false;
$options = $options ?? [];
$placeholder = $placeholder ?? '-- Select --';
?>
<div>
  <label for="fld-<?= htmlspecialchars($name) ?>" class="block text-sm font-semibold text-gray-700 mb-1.5">
    <?= htmlspecialchars($label) ?><?php if ($required): ?><span class="text-brand-red">*</span><?php endif; ?>
  </label>
  <select id="fld-<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" <?= $required ? 'required' : '' ?>
    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy bg-white transition">
    <option value=""><?= htmlspecialchars($placeholder) ?></option>
    <?php foreach ($options as $v => $lbl): ?>
      <option value="<?= htmlspecialchars($v) ?>" <?= (string)$v === (string)$value ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
    <?php endforeach; ?>
  </select>
</div>
