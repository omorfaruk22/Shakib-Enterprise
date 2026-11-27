<?php
// Reusable form input: name, label, type, value, required, placeholder, error, class
$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? old($name);
$required = $required ?? false;
$placeholder = $placeholder ?? '';
$err = $error ?? (flash('errors')[$name] ?? null);
$class = $class ?? '';
?>
<div class="<?= $class ?>">
  <?php if ($label): ?>
    <label for="fld-<?= htmlspecialchars($name) ?>" class="block text-sm font-semibold text-gray-700 mb-1.5">
      <?= htmlspecialchars($label) ?>
      <?php if ($required): ?><span class="text-brand-red">*</span><?php endif; ?>
    </label>
  <?php endif; ?>
  <input id="fld-<?= htmlspecialchars($name) ?>" type="<?= htmlspecialchars($type) ?>" name="<?= htmlspecialchars($name) ?>"
    value="<?= htmlspecialchars($value) ?>" <?= $required ? 'required' : '' ?> placeholder="<?= htmlspecialchars($placeholder) ?>"
    class="w-full border <?= $err ? 'border-red-400' : 'border-gray-300' ?> rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy transition">
  <?php if ($err): ?><p class="text-red-600 text-xs mt-1"><?= htmlspecialchars($err) ?></p><?php endif; ?>
</div>
