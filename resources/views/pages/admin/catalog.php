<?php
declare(strict_types=1);
$isEdit = is_array($record);
$record = $record ?? [];
$fieldClass = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
$resourcePath = app_url('/admin/catalog/' . $resourcePathName);
$pageUrl = static fn (int $number): string => $resourcePath . '?' . http_build_query(['q' => $search, 'active' => $activeFilter, 'page' => $number]);
$current = static fn (string $key, mixed $default = ''): string => e_value($record[$key] ?? $default);
?>
<div><h1 class="text-3xl font-extrabold text-brand-navy"><?= e($title) ?></h1><p class="mt-2 text-slate-600">Manage the active content shown on the public website.</p></div>
<form class="mt-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row" action="<?= e($resourcePath) ?>" method="get">
  <label class="flex-1"><span class="block text-xs font-bold text-slate-600">Search catalog</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="q" type="search" maxlength="120" value="<?= e_value($search) ?>"></label>
  <?php if (in_array($resource, ['services', 'destinations', 'packages', 'ship_routes'], true)): ?><label class="sm:w-48"><span class="block text-xs font-bold text-slate-600">State</span><select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="active"><option value="">All records</option><option value="1"<?= $activeFilter === '1' ? ' selected' : '' ?>>Active only</option><option value="0"<?= $activeFilter === '0' ? ' selected' : '' ?>>Inactive only</option></select></label><?php endif; ?>
  <div class="flex items-end gap-2"><button class="rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white" type="submit">Search</button><a class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold" href="<?= e($resourcePath) ?>">Clear</a></div>
</form>
<div class="mt-6 grid gap-6 xl:grid-cols-[1.3fr_.8fr]">
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h2 class="text-lg font-extrabold text-brand-navy">Records</h2><p class="text-xs text-slate-500"><?= e_value((string) $result['total']) ?> total</p></div><a class="text-sm font-bold text-brand-red hover:underline" href="<?= e($resourcePath) ?>">New record</a></div>
    <?php if ($result['items'] === []): ?><p class="p-8 text-sm text-slate-500">No catalog records found.</p><?php else: ?>
      <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Details</th><th class="px-5 py-3">State</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100">
        <?php foreach ($result['items'] as $item): ?>
          <?php
            $primary = match ($resource) {
                'services' => $item['name'],
                'destinations' => $item['name'],
                'packages' => $item['title'],
                default => $item['origin'] . ' → ' . $item['destination'],
            };
            $secondary = match ($resource) {
                'services', 'destinations' => $item['slug'],
                'packages' => $item['package_type'] . ' · ' . ($item['currency'] ?? 'BDT') . ' ' . ($item['price'] ?? '—'),
                default => 'Ship route',
            };
            $active = array_key_exists('is_active', $item) ? (int) $item['is_active'] === 1 : false;
          ?>
          <tr>
            <td class="px-5 py-4"><span class="font-bold text-slate-900"><?= e_value($primary) ?></span><span class="mt-1 block text-xs text-slate-500"><?= e_value($secondary) ?></span></td>
            <?php $description = (string) ($item['description'] ?? $item['summary'] ?? ''); $description = function_exists('mb_substr') ? mb_substr($description, 0, 90, 'UTF-8') : substr($description, 0, 90); ?>
            <td class="px-5 py-4 text-xs text-slate-600"><?= e_value($description) ?></td>
            <td class="px-5 py-4"><span class="rounded-full <?= $active ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-600' ?> px-3 py-1 text-xs font-bold"><?= $active ? 'Active' : 'Inactive' ?></span></td>
            <td class="px-5 py-4"><a class="font-bold text-brand-red hover:underline" href="<?= e($resourcePath . '?edit=' . (int) $item['id']) ?>">Edit</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
    <?php if ($result['pages'] > 1): ?><nav class="flex justify-between border-t border-slate-200 px-5 py-3 text-sm"><span>Page <?= e_value((string) $result['page']) ?> / <?= e_value((string) $result['pages']) ?></span><span class="flex gap-3"><?php if ($result['page'] > 1): ?><a class="font-bold text-brand-red" href="<?= e($pageUrl($result['page'] - 1)) ?>">Previous</a><?php endif; ?><?php if ($result['page'] < $result['pages']): ?><a class="font-bold text-brand-red" href="<?= e($pageUrl($result['page'] + 1)) ?>">Next</a><?php endif; ?></span></nav><?php endif; ?>
  </section>
  <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <h2 class="text-xl font-extrabold text-brand-navy"><?= $isEdit ? 'Edit record' : 'Add record' ?></h2>
    <form class="mt-5 space-y-4" action="<?= e($resourcePath . '/save') ?>" method="post">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="id" value="<?= e_value((string) ($record['id'] ?? '')) ?>">
      <?php if ($resource === 'services' || $resource === 'destinations'): ?>
        <label class="block text-sm font-semibold">Name<input class="<?= $fieldClass ?>" name="name" maxlength="160" required value="<?= $current('name') ?>"></label>
        <label class="block text-sm font-semibold">URL slug<input class="<?= $fieldClass ?>" name="slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="180" required value="<?= $current('slug') ?>"></label>
        <label class="block text-sm font-semibold">Description<textarea class="<?= $fieldClass ?> min-h-28" name="description" maxlength="20000"><?= $current('description') ?></textarea></label>
        <label class="block text-sm font-semibold">Image path<input class="<?= $fieldClass ?>" name="image_path" maxlength="500" value="<?= $current('image_path') ?>" placeholder="uploads/media/…"></label>
        <?php if ($resource === 'destinations'): ?><label class="flex items-center gap-2 text-sm font-semibold"><input name="is_featured" type="checkbox" value="1"<?= (int) ($record['is_featured'] ?? 0) === 1 ? ' checked' : '' ?>>Feature this destination</label><?php else: ?><label class="block text-sm font-semibold">Sort order<input class="<?= $fieldClass ?>" name="sort_order" type="number" min="0" max="1000000" value="<?= $current('sort_order', 0) ?>"></label><?php endif; ?>
        <label class="flex items-center gap-2 text-sm font-semibold"><input name="is_active" type="checkbox" value="1"<?= (int) ($record['is_active'] ?? 1) === 1 ? ' checked' : '' ?>>Active on the public site</label>
      <?php elseif ($resource === 'packages'): ?>
        <label class="block text-sm font-semibold">Title<input class="<?= $fieldClass ?>" name="title" maxlength="200" required value="<?= $current('title') ?>"></label>
        <label class="block text-sm font-semibold">URL slug<input class="<?= $fieldClass ?>" name="slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="180" required value="<?= $current('slug') ?>"></label>
        <label class="block text-sm font-semibold">Package type<select class="<?= $fieldClass ?>" name="package_type" required><option value="tour"<?= ($record['package_type'] ?? '') === 'tour' ? ' selected' : '' ?>>Tour</option><option value="holiday"<?= ($record['package_type'] ?? '') === 'holiday' ? ' selected' : '' ?>>Holiday</option></select></label>
        <label class="block text-sm font-semibold">Destination<select class="<?= $fieldClass ?>" name="destination_id"><option value="">No destination selected</option><?php foreach ($destinationChoices as $destination): ?><option value="<?= (int) $destination['id'] ?>"<?= (string) ($record['destination_id'] ?? '') === (string) $destination['id'] ? ' selected' : '' ?>><?= e_value($destination['name']) ?></option><?php endforeach; ?></select></label>
        <label class="block text-sm font-semibold">Short summary<input class="<?= $fieldClass ?>" name="summary" maxlength="500" value="<?= $current('summary') ?>"></label>
        <label class="block text-sm font-semibold">Description<textarea class="<?= $fieldClass ?> min-h-28" name="description" maxlength="20000"><?= $current('description') ?></textarea></label>
        <div class="grid grid-cols-2 gap-3"><label class="block text-sm font-semibold">Duration in days<input class="<?= $fieldClass ?>" name="duration_days" type="number" min="1" max="32767" value="<?= $current('duration_days') ?>"></label><label class="block text-sm font-semibold">Price<input class="<?= $fieldClass ?>" name="price" type="number" step="0.01" min="0" value="<?= $current('price') ?>"></label></div>
        <label class="block text-sm font-semibold">Currency<input class="<?= $fieldClass ?>" name="currency" maxlength="3" value="<?= $current('currency', 'BDT') ?>"></label>
        <label class="block text-sm font-semibold">Inclusions<textarea class="<?= $fieldClass ?> min-h-20" name="inclusions" maxlength="20000"><?= $current('inclusions') ?></textarea></label>
        <label class="block text-sm font-semibold">Exclusions<textarea class="<?= $fieldClass ?> min-h-20" name="exclusions" maxlength="20000"><?= $current('exclusions') ?></textarea></label>
        <label class="block text-sm font-semibold">Itinerary JSON<textarea class="<?= $fieldClass ?> min-h-24 font-mono text-xs" name="itinerary" maxlength="20000"><?= e_value(is_array($record['itinerary'] ?? null) ? json_encode($record['itinerary'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ($record['itinerary'] ?? '')) ?></textarea></label>
        <label class="block text-sm font-semibold">Image path<input class="<?= $fieldClass ?>" name="image_path" maxlength="500" value="<?= $current('image_path') ?>" placeholder="uploads/media/…"></label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input name="is_featured" type="checkbox" value="1"<?= (int) ($record['is_featured'] ?? 0) === 1 ? ' checked' : '' ?>>Featured package</label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input name="is_active" type="checkbox" value="1"<?= (int) ($record['is_active'] ?? 1) === 1 ? ' checked' : '' ?>>Active on the public site</label>
      <?php else: ?>
        <label class="block text-sm font-semibold">Origin port<input class="<?= $fieldClass ?>" name="origin" maxlength="160" required value="<?= $current('origin') ?>"></label>
        <label class="block text-sm font-semibold">Destination port<input class="<?= $fieldClass ?>" name="destination" maxlength="160" required value="<?= $current('destination') ?>"></label>
        <label class="block text-sm font-semibold">Description<textarea class="<?= $fieldClass ?> min-h-24" name="description" maxlength="20000"><?= $current('description') ?></textarea></label>
        <label class="block text-sm font-semibold">Sort order<input class="<?= $fieldClass ?>" name="sort_order" type="number" min="0" max="1000000" value="<?= $current('sort_order', 0) ?>"></label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input name="is_active" type="checkbox" value="1"<?= (int) ($record['is_active'] ?? 1) === 1 ? ' checked' : '' ?>>Active</label>
      <?php endif; ?>
      <div class="flex flex-wrap gap-2"><button class="rounded-lg bg-brand-red px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700" type="submit"><?= $isEdit ? 'Save changes' : 'Create record' ?></button><?php if ($isEdit): ?><a class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold" href="<?= e($resourcePath) ?>">Cancel</a><?php endif; ?></div>
    </form>
    <?php if ($isEdit): ?><form class="mt-5 border-t border-slate-200 pt-4" action="<?= e($resourcePath . '/' . (int) $record['id'] . '/delete') ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><label class="flex gap-2 text-xs text-slate-600"><input class="mt-0.5" name="confirm_delete" type="checkbox" value="1" required><span>I confirm permanently deleting this record.</span></label><button class="mt-3 rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50" type="submit">Delete record</button></form><?php endif; ?>
  </section>
</div>
