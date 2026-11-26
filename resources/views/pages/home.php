<?php
declare(strict_types=1);

$settings = $homepage['settings'];
$siteName = trim((string) ($settings['site_name'] ?? 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
$siteTagline = trim((string) ($settings['site_tagline'] ?? 'YOUR TRUSTED TRAVEL PARTNER')) ?: 'YOUR TRUSTED TRAVEL PARTNER';
$address = trim((string) ($settings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh"));
$phoneDisplay = trim((string) ($settings['phone'] ?? ''));
$phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay) ?? '';
if (preg_match('/^\+?[0-9]{7,15}$/D', $phoneHref) !== 1) {
    $phoneHref = '';
}
$whatsappDigits = preg_replace('/\D+/', '', (string) ($settings['whatsapp'] ?? '')) ?? '';
$whatsappUrl = strlen($whatsappDigits) >= 8 && strlen($whatsappDigits) <= 15 && $whatsappDigits[0] !== '0'
    ? 'https://wa.me/' . $whatsappDigits
    : '';
$email = filter_var(trim((string) ($settings['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
$contactUrl = $whatsappUrl !== '' ? $whatsappUrl : ($phoneHref !== '' ? 'tel:' . $phoneHref : ($email !== '' ? 'mailto:' . $email : app_url('/contact-us')));
$contactLabel = $whatsappUrl !== '' ? 'Chat on WhatsApp' : ($phoneHref !== '' ? 'Call the team' : ($email !== '' ? 'Email the team' : 'Contact the team'));
$inquiryUrl = static function (string $subject) use ($whatsappUrl, $email, $phoneHref): string {
    $message = 'Hello, I would like to ask about ' . $subject . '.';
    if ($whatsappUrl !== '') {
        return $whatsappUrl . '?text=' . rawurlencode($message);
    }
    if ($email !== '') {
        return 'mailto:' . $email . '?subject=' . rawurlencode($subject . ' inquiry') . '&body=' . rawurlencode($message);
    }
    return $phoneHref !== '' ? 'tel:' . $phoneHref : app_url('/contact-us');
};
$servicePath = static fn (string $slug): string => match ($slug) {
    'air-ticket' => '/air-tickets',
    'ship-ticket' => '/ship-tickets',
    'bus-ticket' => '/bus-tickets',
    'tour-travel' => '/tour-travel',
    'holiday-package' => '/holiday-packages',
    default => '/services/' . rawurlencode($slug),
};
$publicImage = static function (?string $path): ?string {
    $path = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
    if ($path === '' || str_contains($path, '..')) {
        return null;
    }
    if (preg_match('#^(?:assets/images|uploads)/[A-Za-z0-9/_-]+\.(?:jpe?g|png|webp|avif)$#i', $path) !== 1) {
        return null;
    }
    return app_url($path);
};
?>
<section id="home" class="relative isolate flex min-h-[560px] items-center overflow-hidden bg-brand-deep text-white sm:min-h-[620px]" aria-labelledby="hero-title">
  <img class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_56%]" src="<?= e(app_url('assets/images/coxs-bazar-beach.webp')) ?>" alt="" fetchpriority="high">
  <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-deep/95 via-brand-deep/75 to-brand-deep/10"></div>
  <div class="mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
    <div class="max-w-3xl">
      <span class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[.12em] text-red-100"><span class="h-0.5 w-6 bg-brand-red"></span><?= e((string) $settings['homepage_hero_eyebrow']) ?></span>
      <h1 id="hero-title" class="my-5 text-5xl font-extrabold leading-[1.04] tracking-tight sm:text-6xl lg:text-7xl"><?= e((string) $settings['homepage_hero_title']) ?> <span class="text-rose-300"><?= e((string) $settings['homepage_hero_highlight']) ?></span></h1>
      <p class="max-w-2xl text-lg leading-8 text-blue-100"><?= e((string) $settings['homepage_hero_description']) ?></p>
      <div class="mt-7 flex flex-wrap gap-3">
        <a class="inline-flex items-center gap-2 rounded-full bg-brand-red px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg" href="<?= e($contactUrl) ?>"<?= $whatsappUrl !== '' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($contactLabel) ?><span aria-hidden="true">→</span></a>
        <a class="inline-flex items-center gap-2 rounded-full border border-white/60 px-5 py-3 font-bold text-white transition hover:bg-white/10" href="<?= e(app_url('/services')) ?>">Explore our services</a>
      </div>
      <p class="mt-4 text-xs text-blue-100">Booking inquiries are reviewed by our team. Tickets are not issued automatically.</p>
    </div>
  </div>
  <div class="absolute bottom-7 right-[max(1rem,calc((100vw-80rem)/2))] hidden max-w-[265px] rounded-2xl bg-white px-5 py-4 text-slate-800 shadow-xl lg:block"><b class="block text-brand-navy"><?= e($siteTagline) ?></b><span class="text-sm text-slate-500"><?= e($address) ?></span></div>
</section>

<section id="about" class="py-16 sm:py-20">
  <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-brand-sky"><img class="aspect-[4/3] w-full object-cover" src="<?= e(app_url('assets/images/coxs-bazar-beach.webp')) ?>" alt="Visitors enjoy the shoreline at Cox's Bazar" loading="lazy"><div class="absolute bottom-4 left-4 rounded-xl bg-white px-4 py-2 text-sm font-bold text-brand-navy shadow">Discover the beauty of Cox's Bazar</div></div>
    <div><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Welcome to <?= e($siteName) ?></span><h2 class="my-3 text-3xl font-extrabold leading-tight tracking-tight text-brand-navy sm:text-4xl">Thoughtful travel planning, made easier</h2><p class="max-w-2xl text-slate-600">Explore ticketing assistance and tour options with a local travel partner. Share your travel details and the team will follow up to discuss availability and next steps.</p><ul class="my-6 grid list-none gap-3 p-0 text-slate-700"><li class="flex items-start gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-50 text-xs font-black text-emerald-700" aria-hidden="true">✓</span>Travel services gathered in one place</li><li class="flex items-start gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-50 text-xs font-black text-emerald-700" aria-hidden="true">✓</span>Clear inquiry process and direct follow-up</li><li class="flex items-start gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-50 text-xs font-black text-emerald-700" aria-hidden="true">✓</span>Packages and business information managed by the team</li></ul><a class="font-bold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/about-us')) ?>">More about us →</a></div>
  </div>
</section>

<section id="services" class="bg-slate-50 py-16 sm:py-20">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end"><div class="max-w-2xl"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Travel services</span><h2 class="my-2 text-3xl font-extrabold leading-tight text-brand-navy sm:text-4xl">Choose the journey you have in mind</h2><p class="text-slate-600">Tell us what you need. The team can review your request and contact you with the next steps.</p></div><a class="whitespace-nowrap font-bold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/services')) ?>">All services →</a></div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
      <?php foreach ($homepage['services'] as $service): ?>
        <a class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg" href="<?= e(app_url($servicePath((string) $service['slug']))) ?>">
          <span class="mb-4 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true"><?= e((string) $service['icon']) ?></span><h3 class="font-bold text-brand-navy group-hover:text-brand-red"><?= e((string) $service['name']) ?></h3><p class="my-2 text-sm text-slate-600"><?= e((string) $service['description']) ?></p><span class="text-sm font-bold text-brand-red">View service →</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="destinations" class="py-16 sm:py-20">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end"><div class="max-w-2xl"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Explore nearby</span><h2 class="my-2 text-3xl font-extrabold leading-tight text-brand-navy sm:text-4xl">Destinations worth discovering</h2><p class="text-slate-600">Published destinations are shown when the business adds confirmed travel information.</p></div><a class="font-bold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/destinations')) ?>">All destinations →</a></div>
    <?php if ($homepage['destinations'] === []): ?><div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-7 text-slate-600"><?= $homepage['database_available'] ? 'Destination details will appear here after the business confirms and publishes them.' : 'Destination listings are temporarily unavailable. Please check again later.' ?></div><?php else: ?>
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($homepage['destinations'] as $destination): ?>
          <?php $destinationImage = $publicImage(isset($destination['image_path']) ? (string) $destination['image_path'] : null); ?>
          <a class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg" href="<?= e(app_url('/destinations/' . rawurlencode((string) $destination['slug']))) ?>">
            <?php if ($destinationImage !== null): ?><img class="h-52 w-full object-cover" src="<?= e($destinationImage) ?>" alt="<?= e((string) $destination['name']) ?>" loading="lazy"><?php else: ?><div class="grid h-52 place-items-center bg-gradient-to-br from-blue-100 to-blue-300 text-sm font-semibold text-brand-navy">Photo coming soon</div><?php endif; ?>
            <div class="p-5"><h3 class="font-bold text-brand-navy"><?= e((string) $destination['name']) ?></h3><p class="mt-1 text-sm text-slate-600"><?= e((string) ($destination['description'] ?? 'Ask the team about travel options for this destination.')) ?></p><span class="mt-4 inline-flex text-sm font-bold text-brand-red">Explore destination →</span></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section id="packages" class="bg-brand-sky py-16 sm:py-20">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end"><div class="max-w-2xl"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Tour &amp; holiday packages</span><h2 class="my-2 text-3xl font-extrabold leading-tight text-brand-navy sm:text-4xl">Find a trip that fits your plans</h2><p class="text-slate-600">Current package descriptions, prices and availability are managed by <?= e($siteName) ?>.</p></div><a class="font-bold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/packages')) ?>">All packages →</a></div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <?php if ($homepage['packages'] === []): ?>
        <article class="flex min-h-52 flex-col rounded-2xl border border-slate-200 bg-white p-6"><span class="self-start rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold text-brand-navy">Tour package</span><h3 class="mb-2 mt-4 text-lg font-bold text-brand-navy">Tour options will be listed here</h3><p class="mb-4 text-sm text-slate-600">Approved tour details will appear once they are added by the business.</p><div class="mt-auto border-t border-slate-200 pt-3 text-xs text-slate-500">Contact the team for current price and availability.</div></article>
        <article class="flex min-h-52 flex-col rounded-2xl border border-slate-200 bg-white p-6"><span class="self-start rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold text-brand-navy">Holiday</span><h3 class="mb-2 mt-4 text-lg font-bold text-brand-navy">Holiday options will be listed here</h3><p class="mb-4 text-sm text-slate-600">Published holidays will include details confirmed by the business.</p><div class="mt-auto border-t border-slate-200 pt-3 text-xs text-slate-500">Contact the team for current price and availability.</div></article>
      <?php else: ?>
        <?php foreach ($homepage['packages'] as $package): ?>
          <article class="flex min-h-52 flex-col rounded-2xl border border-slate-200 bg-white p-6"><span class="self-start rounded-full bg-brand-sky px-3 py-1 text-xs font-extrabold text-brand-navy"><?= ($package['package_type'] ?? '') === 'holiday' ? 'Holiday package' : 'Tour package' ?></span><h3 class="mb-2 mt-4 text-lg font-bold text-brand-navy"><?= e((string) $package['title']) ?></h3><?php if (trim((string) ($package['destination_name'] ?? '')) !== ''): ?><p class="text-sm text-slate-600"><?= e((string) $package['destination_name']) ?></p><?php endif; ?><p class="mb-4 mt-1 text-sm text-slate-600"><?= e((string) ($package['summary'] ?? 'Ask the team for package details.')) ?></p><div class="mt-auto border-t border-slate-200 pt-3 text-xs text-slate-500"><?php if (is_numeric($package['price'] ?? null) && (float) $package['price'] > 0): ?><?= e((string) ($package['currency'] ?? 'BDT')) ?> <?= e(number_format((float) $package['price'], 2)) ?> · <?php endif; ?><a class="font-bold text-brand-red" href="<?= e(app_url('/packages/' . rawurlencode((string) $package['slug']))) ?>">View details →</a></div></article>
        <?php endforeach; ?>
      <?php endif; ?>
      <article class="flex min-h-52 flex-col rounded-2xl bg-brand-navy p-6 text-white"><span class="self-start rounded-full bg-white/10 px-3 py-1 text-xs font-extrabold text-blue-100">Custom inquiry</span><h3 class="mb-2 mt-4 text-lg font-bold">Build a trip around your dates</h3><p class="mb-4 text-sm text-blue-100">Share your preferred destination and travel dates to start a conversation.</p><a class="mt-auto font-bold text-white hover:underline" href="<?= e(app_url('/contact-us')) ?>">Contact the team →</a></article>
    </div>
  </div>
</section>

<section class="py-16 sm:py-20">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mb-8 max-w-2xl"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Why choose us</span><h2 class="my-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">Helpful service at every step</h2><p class="text-slate-600">Clear information and direct communication help customers make informed travel plans.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="mb-4 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true">⌖</span><h3 class="font-bold text-brand-navy">Local knowledge</h3><p class="mt-2 text-sm text-slate-600">Based in <?= e($address) ?>.</p></article>
      <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="mb-4 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true">♡</span><h3 class="font-bold text-brand-navy">Personal support</h3><p class="mt-2 text-sm text-slate-600">The team follows up on submitted inquiries.</p></article>
      <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="mb-4 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true">▤</span><h3 class="font-bold text-brand-navy">Clear requests</h3><p class="mt-2 text-sm text-slate-600">Share dates, routes and passenger needs in one place.</p></article>
      <article class="rounded-2xl border border-slate-200 bg-white p-6"><span class="mb-4 grid size-12 place-items-center rounded-2xl bg-brand-sky text-2xl text-brand-navy" aria-hidden="true">✓</span><h3 class="font-bold text-brand-navy">Honest updates</h3><p class="mt-2 text-sm text-slate-600">Ticket issuance and availability are confirmed directly by the team.</p></article>
    </div>
  </div>
</section>

<section class="bg-slate-50 py-16 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mb-8"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">How booking inquiries work</span><h2 class="my-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">From request to follow-up</h2></div><div class="grid gap-5 md:grid-cols-3"><article class="border-l-2 border-blue-200 p-5"><span class="text-xs font-black tracking-widest text-brand-red">STEP 01</span><h3 class="my-2 font-bold text-brand-navy">Choose a service</h3><p class="text-sm text-slate-600">Select a ticketing, tour or holiday package inquiry.</p></article><article class="border-l-2 border-blue-200 p-5"><span class="text-xs font-black tracking-widest text-brand-red">STEP 02</span><h3 class="my-2 font-bold text-brand-navy">Share your details</h3><p class="text-sm text-slate-600">Provide the travel information and a way to reach you.</p></article><article class="border-l-2 border-blue-200 p-5"><span class="text-xs font-black tracking-widest text-brand-red">STEP 03</span><h3 class="my-2 font-bold text-brand-navy">Our team follows up</h3><p class="text-sm text-slate-600">A team member reviews the request and discusses confirmation.</p></article></div></div></section>

<section id="testimonials" class="py-16 sm:py-20"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mb-8"><span class="text-xs font-extrabold uppercase tracking-[.13em] text-brand-red">Customer stories</span><h2 class="my-2 text-3xl font-extrabold text-brand-navy sm:text-4xl">Experiences from our travelers</h2></div>
  <?php if ($homepage['testimonials'] === []): ?><div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-7 text-slate-600"><?= $homepage['database_available'] ? 'Verified customer testimonials will appear here after the business provides and approves them.' : 'Customer stories are temporarily unavailable.' ?></div><?php else: ?><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><?php foreach ($homepage['testimonials'] as $testimonial): ?><article class="rounded-2xl border border-slate-200 bg-white p-6"><p class="text-slate-700">“<?= e((string) $testimonial['quote']) ?>”</p><h3 class="mt-4 font-bold text-brand-navy"><?= e((string) $testimonial['customer_display_name']) ?></h3><?php if (is_numeric($testimonial['rating'] ?? null)): ?><p class="mt-1 text-sm text-slate-500"><?= e((string) $testimonial['rating']) ?> / 5 verified rating</p><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
</div></section>

<section class="bg-slate-50 py-10 sm:py-14"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-brand-navy p-7 text-white sm:flex-row sm:items-center sm:p-9"><div><span class="text-xs font-extrabold uppercase tracking-[.13em] text-rose-200">Plan your next journey</span><h2 class="mb-2 mt-1 text-2xl font-extrabold sm:text-3xl">Start with a booking inquiry</h2><p class="text-blue-100">The team will contact you to discuss availability and confirmation.</p></div><a class="inline-flex shrink-0 rounded-full bg-brand-red px-5 py-3 font-bold text-white" href="<?= e(app_url('/services')) ?>">Choose a service →</a></div></div></section>

<section id="contact" class="py-14 sm:py-16"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex flex-col items-start justify-between gap-6 rounded-3xl bg-brand-navy p-7 text-white sm:flex-row sm:items-center sm:p-9"><div><span class="text-xs font-extrabold uppercase tracking-[.13em] text-rose-200">Have a question?</span><h2 class="mb-2 mt-1 text-2xl font-extrabold sm:text-3xl">Contact <?= e($siteName) ?></h2><p class="text-blue-100"><?= e($address) ?></p><?php if ($phoneHref !== ''): ?><p class="mt-2"><a class="text-white hover:underline" href="tel:<?= e($phoneHref) ?>"><?= e($phoneDisplay) ?></a></p><?php endif; ?><?php if ($email !== ''): ?><p><a class="text-white hover:underline" href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p><?php endif; ?><?php if ($phoneHref === '' && $whatsappUrl === '' && $email === ''): ?><p class="mt-2 text-sm text-blue-100">Phone, WhatsApp and email details will be added after the business confirms them.</p><?php endif; ?></div><div class="flex flex-wrap gap-3"><?php if ($whatsappUrl !== ''): ?><a class="rounded-full bg-brand-red px-5 py-3 font-bold text-white" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer">Chat on WhatsApp →</a><?php elseif ($phoneHref !== ''): ?><a class="rounded-full bg-brand-red px-5 py-3 font-bold text-white" href="tel:<?= e($phoneHref) ?>">Call the office →</a><?php elseif ($email !== ''): ?><a class="rounded-full bg-brand-red px-5 py-3 font-bold text-white" href="mailto:<?= e($email) ?>">Email the office →</a><?php endif; ?><a class="rounded-full border border-white/60 px-5 py-3 font-bold text-white" href="<?= e(app_url('/contact-us')) ?>">Contact page</a></div></div></div></section>
