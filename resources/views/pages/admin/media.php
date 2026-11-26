<?php
declare(strict_types=1);
$pageUrl = static fn (int $number): string => app_url('/admin/media?' . http_build_query(['page' => $number]));
?>
<div><h1 class="text-3xl font-extrabold text-brand-navy">Media library</h1><p class="mt-2 text-slate-600">Upload JPEG, PNG or WebP images up to 5 MB and 6000 × 6000 pixels.</p></div>
<form class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-[1fr_1fr_auto]" action="<?= e(app_url('/admin/media/upload')) ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
  <label class="block text-sm font-semibold">Image file<input class="mt-2 block w-full rounded-lg border border-slate-300 p-2 text-sm" name="image" type="file" accept="image/jpeg,image/png,image/webp" required></label>
  <label class="block text-sm font-semibold">Alt text (optional)<input class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="alt_text" type="text" maxlength="255"></label>
  <button class="self-end rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900" type="submit">Upload image</button>
</form>
<section class="mt-6">
  <?php if ($result['items'] === []): ?><div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">No images uploaded.</div><?php else: ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <?php foreach ($result['items'] as $media): ?>
        <?php $safePath = preg_match('#^uploads/media/[a-f0-9]{40}\.(jpg|png|webp)$#D', (string) $media['storage_path']) === 1; ?>
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <?php if ($safePath): ?><img class="aspect-[4/3] w-full bg-slate-100 object-cover" src="<?= e(app_url('/' . $media['storage_path'])) ?>" alt="<?= e_value($media['alt_text'] ?? '') ?>" loading="lazy"><?php else: ?><div class="grid aspect-[4/3] place-items-center bg-slate-100 text-sm text-slate-500">Invalid storage path</div><?php endif; ?>
          <div class="p-4"><p class="truncate font-bold text-slate-900"><?= e_value($media['original_name']) ?></p><p class="mt-1 text-xs text-slate-500"><?= e_value(strtoupper(pathinfo($media['storage_path'], PATHINFO_EXTENSION))) ?> · <?= e_value(number_format((int) $media['file_size'] / 1024)) ?> KB</p><?php if ($safePath): ?><label class="mt-3 block text-xs font-bold text-slate-600">Public path<input class="mt-1 w-full rounded-lg border border-slate-300 bg-slate-50 px-2 py-1.5 font-normal" readonly value="<?= e_value($media['storage_path']) ?>"></label><?php endif; ?>
            <form class="mt-4 border-t border-slate-200 pt-3" action="<?= e(app_url('/admin/media/' . (int) $media['id'] . '/delete')) ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><label class="flex gap-2 text-xs text-slate-600"><input class="mt-0.5" type="checkbox" name="confirm_delete" value="1" required><span>I confirm deleting this image.</span></label><button class="mt-2 text-sm font-bold text-red-700 hover:underline" type="submit">Delete image</button></form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php if ($result['pages'] > 1): ?><nav class="mt-5 flex justify-between text-sm"><span>Page <?= e_value((string) $result['page']) ?> of <?= e_value((string) $result['pages']) ?></span><span class="flex gap-2"><?php if ($result['page'] > 1): ?><a class="rounded-lg border bg-white px-4 py-2" href="<?= e($pageUrl($result['page'] - 1)) ?>">Previous</a><?php endif; ?><?php if ($result['page'] < $result['pages']): ?><a class="rounded-lg border bg-white px-4 py-2" href="<?= e($pageUrl($result['page'] + 1)) ?>">Next</a><?php endif; ?></span></nav><?php endif; ?>
