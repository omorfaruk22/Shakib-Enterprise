<?php
declare(strict_types=1);
$pageUrl = static fn (int $number): string => app_url('/admin/content/pages?' . http_build_query(['q' => $search, 'page' => $number]));
$pageRecord ??= null;
$pageValues = is_array($pageRecord) ? $pageRecord : [];
$controlClass = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
?>
<div><h1 class="text-3xl font-extrabold text-brand-navy">Website pages</h1><p class="mt-2 text-slate-600">Edit published page copy and search metadata.</p></div>
<form class="mt-5 flex gap-2 rounded-2xl border border-slate-200 bg-white p-4" action="<?= e(app_url('/admin/content/pages')) ?>" method="get"><label class="flex-1"><span class="block text-xs font-bold text-slate-600">Search pages</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="q" type="search" maxlength="120" value="<?= e_value($search) ?>"></label><button class="self-end rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white">Search</button></form>
<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1.2fr]">
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-extrabold text-brand-navy">Pages</h2></div>
    <?php if ($result['items'] === []): ?><p class="p-8 text-sm text-slate-500">No pages found.</p><?php else: ?><ul class="divide-y divide-slate-100"><?php foreach ($result['items'] as $page): ?><li class="flex items-center justify-between gap-3 px-5 py-4"><div><p class="font-bold text-slate-900"><?= e_value($page['title']) ?></p><p class="mt-1 text-xs text-slate-500">/<?= e_value($page['slug']) ?> · <?= (int) $page['is_published'] === 1 ? 'Published' : 'Draft' ?></p></div><a class="shrink-0 font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/content/pages?' . http_build_query(['edit' => (int) $page['id'], 'q' => $search]))) ?>">Edit</a></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($result['pages'] > 1): ?><nav class="flex justify-between border-t border-slate-200 px-5 py-3 text-sm"><span>Page <?= e_value((string) $result['page']) ?> / <?= e_value((string) $result['pages']) ?></span><span class="flex gap-3"><?php if ($result['page'] > 1): ?><a class="font-bold text-brand-red" href="<?= e($pageUrl($result['page'] - 1)) ?>">Previous</a><?php endif; ?><?php if ($result['page'] < $result['pages']): ?><a class="font-bold text-brand-red" href="<?= e($pageUrl($result['page'] + 1)) ?>">Next</a><?php endif; ?></span></nav><?php endif; ?>
  </section>
  <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <div class="flex items-center justify-between gap-3"><h2 class="text-xl font-extrabold text-brand-navy"><?= $pageRecord === null ? 'Add page' : 'Edit page' ?></h2><?php if ($pageRecord !== null): ?><a class="text-sm font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/content/pages')) ?>">New page</a><?php endif; ?></div>
    <form class="mt-5 space-y-4" action="<?= e(app_url('/admin/content/pages/save')) ?>" method="post">
      <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><input type="hidden" name="id" value="<?= e_value((string) ($pageValues['id'] ?? '')) ?>">
      <label class="block text-sm font-semibold">Page title<input class="<?= $controlClass ?>" name="title" maxlength="200" required value="<?= e_value($pageValues['title'] ?? '') ?>"></label>
      <label class="block text-sm font-semibold">URL slug<input class="<?= $controlClass ?>" name="slug" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="180" required value="<?= e_value($pageValues['slug'] ?? '') ?>"></label>
      <label class="block text-sm font-semibold">Page body<textarea class="<?= $controlClass ?> min-h-56" name="body" maxlength="100000"><?= e_value($pageValues['body'] ?? '') ?></textarea></label>
      <label class="block text-sm font-semibold">SEO title<input class="<?= $controlClass ?>" name="meta_title" maxlength="200" value="<?= e_value($pageValues['meta_title'] ?? '') ?>"></label>
      <label class="block text-sm font-semibold">SEO description<textarea class="<?= $controlClass ?> min-h-20" name="meta_description" maxlength="320"><?= e_value($pageValues['meta_description'] ?? '') ?></textarea></label>
      <label class="flex items-center gap-2 text-sm font-semibold"><input name="is_published" type="checkbox" value="1"<?= (int) ($pageValues['is_published'] ?? 0) === 1 ? ' checked' : '' ?>>Published</label>
      <button class="rounded-lg bg-brand-red px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700" type="submit"><?= $pageRecord === null ? 'Create page' : 'Save page' ?></button>
    </form>
    <?php if ($pageRecord !== null): ?><form class="mt-5 border-t border-slate-200 pt-4" action="<?= e(app_url('/admin/content/pages/' . (int) $pageRecord['id'] . '/delete')) ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><label class="flex gap-2 text-xs text-slate-600"><input class="mt-0.5" type="checkbox" name="confirm_delete" value="1" required><span>I confirm permanently deleting this page.</span></label><button class="mt-3 rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50" type="submit">Delete page</button></form><?php endif; ?>
  </section>
</div>
