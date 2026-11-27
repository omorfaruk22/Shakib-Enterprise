<?php
$name = $name ?? '';
$label = $label ?? '';
$value = $value ?? old($name);
$required = $required ?? false;
$placeholder = $placeholder ?? '';
$rows = $rows ?? 4;
$err = form_errors()[$name] ?? null;
?>
<div>
  <label for="fld-<?= htmlspecialchars($name) ?>" class="block text-sm font-semibold text-gray-700 mb-1.5">
    <?= htmlspecialchars($label) ?>
    <?php if ($required): ?><span class="text-brand-red">*</span><?php endif; ?>
  </label>
  <textarea id="fld-<?= htmlspecialchars($name) ?>" name="<?= htmlspecialchars($name) ?>" rows="<?= (int)$rows ?>" <?= $required ? 'required' : '' ?> placeholder="<?= htmlspecialchars($placeholder) ?>"
    class="w-full border <?= $err ? 'border-red-400' : 'border-gray-300' ?> rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy transition"><?= htmlspecialchars($value) ?></textarea>
  <?php if ($err): ?><p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($err) ?></p><?php endif; ?>
</div>
