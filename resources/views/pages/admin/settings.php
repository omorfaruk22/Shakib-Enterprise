<?php
declare(strict_types=1);
$fields = [
    'site_name' => ['Site name', 'text'],
    'site_tagline' => ['Tagline', 'text'],
    'office_address' => ['Office address', 'text'],
    'office_hours' => ['Office hours', 'textarea'],
    'phone' => ['Business phone', 'tel'],
    'whatsapp' => ['WhatsApp number', 'tel'],
    'email' => ['Business email', 'email'],
    'facebook_url' => ['Facebook profile URL', 'url'],
    'instagram_url' => ['Instagram profile URL', 'url'],
    'map_embed_url' => ['Google Maps embed URL', 'url'],
    'homepage_hero_eyebrow' => ['Homepage hero eyebrow', 'text'],
    'homepage_hero_title' => ['Homepage hero title', 'text'],
    'homepage_hero_highlight' => ['Homepage hero highlighted text', 'text'],
    'homepage_hero_description' => ['Homepage hero description', 'textarea'],
    'homepage_meta_title' => ['Homepage SEO title', 'text'],
    'homepage_meta_description' => ['Homepage SEO description', 'textarea'],
];
?>
<div><h1 class="text-3xl font-extrabold text-brand-navy">Website settings</h1><p class="mt-2 text-slate-600">Changes are used by public pages. Social links and maps accept secure, approved domains only.</p></div>
<form class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8" action="<?= e(app_url('/admin/settings')) ?>" method="post">
  <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
  <div class="grid gap-5 sm:grid-cols-2">
    <?php foreach ($fields as $key => [$label, $type]): ?>
      <label class="block text-sm font-semibold <?= in_array($key, ['office_address', 'office_hours', 'facebook_url', 'instagram_url', 'map_embed_url', 'homepage_hero_description', 'homepage_meta_description'], true) ? 'sm:col-span-2' : '' ?>">
        <?= e($label) ?>
        <?php if ($type === 'textarea'): ?><textarea class="mt-1 min-h-24 w-full rounded-lg border border-slate-300 px-3 py-2" name="<?= e($key) ?>" maxlength="<?= $key === 'homepage_meta_description' ? 320 : 2000 ?>"><?= e_value($settings[$key] ?? '') ?></textarea>
        <?php else: ?><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="<?= e($key) ?>" type="<?= e($type) ?>" maxlength="<?= $key === 'map_embed_url' ? 2048 : 255 ?>" value="<?= e_value($settings[$key] ?? '') ?>"><?php endif; ?>
      </label>
    <?php endforeach; ?>
  </div>
  <button class="mt-6 rounded-xl bg-brand-red px-5 py-3 font-bold text-white hover:bg-red-700" type="submit">Save website settings</button>
  <p class="mt-4 text-xs leading-5 text-slate-500">Map embeds must use a Google Maps HTTPS embed URL. Facebook and Instagram fields accept only HTTPS links to their own domains.</p>
</form>
