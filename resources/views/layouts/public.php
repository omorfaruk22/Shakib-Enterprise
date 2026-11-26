<?php
declare(strict_types=1);

$siteName = trim((string) ($siteSettings['site_name'] ?? 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
$siteTagline = trim((string) ($siteSettings['site_tagline'] ?? 'YOUR TRUSTED TRAVEL PARTNER')) ?: 'YOUR TRUSTED TRAVEL PARTNER';
$officeAddress = trim((string) ($siteSettings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh"));
$phoneDisplay = trim((string) ($siteSettings['phone'] ?? ''));
$phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?? '';
if (preg_match('/^\+?[0-9]{7,15}$/D', $phoneHref) !== 1) {
    $phoneHref = '';
}
$whatsappDigits = preg_replace('/\D+/', '', (string) ($siteSettings['whatsapp'] ?? '')) ?? '';
$whatsappUrl = strlen($whatsappDigits) >= 8 && strlen($whatsappDigits) <= 15 && $whatsappDigits[0] !== '0'
    ? 'https://wa.me/' . $whatsappDigits
    : '';
$email = filter_var(trim((string) ($siteSettings['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
$facebookUrl = safe_social_url($siteSettings['facebook_url'] ?? '', 'facebook.com');
$instagramUrl = safe_social_url($siteSettings['instagram_url'] ?? '', 'instagram.com');
$mapEmbedUrl = safe_map_embed_url($siteSettings['map_embed_url'] ?? '');

ob_start();
require $contentView;
$pageBody = (string) ob_get_clean();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="theme-color" content="#102b70">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <link rel="canonical" href="<?= e($pageCanonical) ?>">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= e(app_url('assets/css/site.css')) ?>">
  <script defer src="<?= e(app_url('assets/js/site.js')) ?>"></script>
</head>
<body class="m-0 bg-white font-sans text-base leading-relaxed text-slate-800 antialiased">
  <div class="bg-brand-deep text-xs text-blue-100">
    <div class="mx-auto flex max-w-7xl justify-between gap-4 px-4 py-2 sm:px-6 lg:px-8">
      <span><?= e($officeAddress) ?></span>
      <span class="hidden sm:inline">
        <?php if ($phoneHref !== ''): ?><a class="text-white hover:underline" href="tel:<?= e($phoneHref) ?>">Call <?= e($phoneDisplay) ?></a><?php endif; ?>
        <?php if ($phoneHref !== '' && $whatsappUrl !== ''): ?> · <?php endif; ?>
        <?php if ($whatsappUrl !== ''): ?><a class="text-white hover:underline" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a><?php endif; ?>
        <?php if ($phoneHref === '' && $whatsappUrl === ''): ?>Contact details will be configured by the business<?php endif; ?>
      </span>
    </div>
  </div>

  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex min-h-[76px] max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">
      <a class="inline-flex items-center gap-3 font-extrabold tracking-wide text-brand-navy" href="<?= e(app_url('/')) ?>" aria-label="<?= e($siteName) ?> home">
        <span class="grid size-11 place-items-center rounded-2xl bg-brand-red text-white shadow-lg" aria-hidden="true">SE</span>
        <span class="leading-tight"><?= e($siteName) ?><small class="mt-1 block text-[10px] font-bold tracking-[.13em] text-slate-500"><?= e($siteTagline) ?></small></span>
      </a>
      <button class="grid size-11 place-items-center rounded-xl border border-slate-200 bg-white text-xl text-brand-navy md:hidden" data-menu-toggle aria-expanded="false" aria-controls="public-navigation" aria-label="Toggle navigation">☰</button>
      <nav id="public-navigation" class="absolute left-0 right-0 top-full hidden flex-col border-b border-slate-200 bg-white px-5 pb-5 pt-3 shadow-xl md:static md:flex md:flex-row md:items-center md:gap-6 md:border-0 md:bg-transparent md:p-0 md:shadow-none" data-menu aria-label="Main navigation">
        <a class="border-b border-slate-100 py-3 text-sm font-semibold hover:text-brand-red md:border-0 md:py-0" href="<?= e(app_url('/services')) ?>">Services</a>
        <a class="border-b border-slate-100 py-3 text-sm font-semibold hover:text-brand-red md:border-0 md:py-0" href="<?= e(app_url('/destinations')) ?>">Destinations</a>
        <a class="border-b border-slate-100 py-3 text-sm font-semibold hover:text-brand-red md:border-0 md:py-0" href="<?= e(app_url('/packages')) ?>">Packages</a>
        <a class="border-b border-slate-100 py-3 text-sm font-semibold hover:text-brand-red md:border-0 md:py-0" href="<?= e(app_url('/about-us')) ?>">About</a>
        <a class="py-3 text-sm font-semibold hover:text-brand-red md:py-0" href="<?= e(app_url('/contact-us')) ?>">Contact</a>
      </nav>
      <a class="hidden rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg sm:inline-flex" href="<?= e(app_url('/contact-us')) ?>">Plan a trip <span class="ml-2" aria-hidden="true">↗</span></a>
    </div>
  </header>

  <main><?= $pageBody ?></main>

  <footer class="bg-brand-deep py-12 text-blue-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <a class="inline-flex items-center gap-3 font-extrabold text-white" href="<?= e(app_url('/')) ?>"><span class="grid size-10 place-items-center rounded-xl bg-brand-red">SE</span><span><?= e($siteName) ?><small class="block text-[10px] tracking-widest text-blue-200"><?= e($siteTagline) ?></small></span></a>
          <p class="mt-4 text-sm text-blue-200">Travel services and inquiry support from <?= e($officeAddress) ?>.</p>
        </div>
        <div><h2 class="text-sm font-bold text-white">Services</h2><ul class="mt-3 space-y-2 text-sm text-blue-200"><li><a class="hover:text-white" href="<?= e(app_url('/air-tickets')) ?>">Air tickets</a></li><li><a class="hover:text-white" href="<?= e(app_url('/ship-tickets')) ?>">Ship tickets</a></li><li><a class="hover:text-white" href="<?= e(app_url('/bus-tickets')) ?>">Bus tickets</a></li><li><a class="hover:text-white" href="<?= e(app_url('/tour-travel')) ?>">Tours and holidays</a></li></ul></div>
        <div><h2 class="text-sm font-bold text-white">Explore</h2><ul class="mt-3 space-y-2 text-sm text-blue-200"><li><a class="hover:text-white" href="<?= e(app_url('/destinations')) ?>">Destinations</a></li><li><a class="hover:text-white" href="<?= e(app_url('/packages')) ?>">Packages</a></li><li><a class="hover:text-white" href="<?= e(app_url('/about-us')) ?>">About us</a></li></ul></div>
        <div><h2 class="text-sm font-bold text-white">Contact</h2><p class="mt-3 text-sm text-blue-200"><?= e($officeAddress) ?></p><a class="mt-2 inline-block text-sm text-blue-200 hover:text-white" href="<?= e(app_url('/contact-us')) ?>">Contact details →</a><?php if ($facebookUrl !== '' || $instagramUrl !== ''): ?><div class="mt-4 flex gap-4 text-sm font-semibold"><?php if ($facebookUrl !== ''): ?><a class="hover:text-white" href="<?= e($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a><?php endif; ?><?php if ($instagramUrl !== ''): ?><a class="hover:text-white" href="<?= e($instagramUrl) ?>" target="_blank" rel="noopener noreferrer">Instagram</a><?php endif; ?></div><?php endif; ?></div>
      </div>
      <div class="mt-8 flex flex-col justify-between gap-2 border-t border-white/15 pt-4 text-xs text-blue-200 sm:flex-row"><span>© <span data-year><?= e(date('Y')) ?></span> <?= e($siteName) ?></span><span><?= e($officeAddress) ?></span></div>
    </div>
  </footer>
</body>
</html>
