<?php
declare(strict_types=1);

$pageHeading = 'Contact us';
$pageEyebrow = 'Talk with the team';
$pageLead = 'Send a general question or ask about travel services, destinations and packages.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
$errors ??= [];
$old ??= [];
?>
<section class="py-12 sm:py-16">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[.85fr_1.15fr] lg:px-8">
    <div class="rounded-3xl bg-brand-navy p-7 text-white sm:p-9">
      <h2 class="text-2xl font-extrabold">Contact <?= e((string) ($siteSettings['site_name'] ?? 'SAKIB ENTERPRISE')) ?></h2>
      <p class="mt-3 text-blue-100">Share your travel question with the business. The team can confirm current details directly.</p>
      <?php if ($requestedService !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Your inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedService) ?></p><p class="mt-2 text-sm text-blue-100">When you contact the team, include your travel dates, route or destination, and number of travelers.</p></div><?php endif; ?>
      <?php if ($requestedPackage !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Package inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedPackage) ?></p><p class="mt-2 text-sm text-blue-100">Mention your preferred dates and group size when contacting the team.</p></div><?php endif; ?>
      <?php if ($requestedDestination !== ''): ?><div class="mt-6 rounded-2xl bg-white/10 p-5"><p class="text-xs font-bold uppercase tracking-wider text-blue-100">Destination inquiry</p><p class="mt-1 text-lg font-bold"><?= e($requestedDestination) ?></p><p class="mt-2 text-sm text-blue-100">Mention your preferred dates and traveler count when contacting the team.</p></div><?php endif; ?>
      <div class="mt-7 space-y-4">
        <?php if ($phoneHref !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="tel:<?= e($phoneHref) ?>"><span aria-hidden="true">☎</span><span><?= e($phoneDisplay) ?></span></a><?php endif; ?>
        <?php if ($whatsappUrl !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">◉</span><span>Chat on WhatsApp</span></a><?php endif; ?>
        <?php if ($email !== ''): ?><a class="flex items-center gap-3 rounded-xl bg-white/10 p-4 font-semibold hover:bg-white/15" href="mailto:<?= e($email) ?>"><span aria-hidden="true">✉</span><span><?= e($email) ?></span></a><?php endif; ?>
        <?php if ($phoneHref === '' && $whatsappUrl === '' && $email === ''): ?><p class="rounded-xl border border-white/20 p-4 text-sm text-blue-100">Phone, WhatsApp and email details will be added after the business confirms them.</p><?php endif; ?>
      </div>
      <div class="mt-7 rounded-2xl bg-white/10 p-5 text-sm leading-6 text-blue-100">
        <p class="font-bold text-white">Office</p>
        <p class="mt-1"><?= e((string) ($siteSettings['office_address'] ?? "Cox's Bazar Sadar, Bangladesh")) ?></p>
        <?php if (trim((string) ($siteSettings['office_hours'] ?? '')) !== ''): ?><p class="mt-3 whitespace-pre-line"><?= e((string) $siteSettings['office_hours']) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
      <h2 class="text-2xl font-extrabold text-brand-navy">Send a message</h2>
      <p class="mt-2 text-slate-600">Provide a phone number or email address so the team can reply.</p>
      <?php if ($messageSent): ?>
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" role="status">Your message has been received. The team will contact you using the details you provided.</div>
      <?php endif; ?>
      <?php if (isset($errors['_form'])): ?>
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert"><?= e_value($errors['_form']) ?></div>
      <?php endif; ?>
      <form class="mt-6 space-y-5" action="<?= e(app_url('/contact-us')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <div class="hidden" aria-hidden="true"><label for="contact-website">Leave this field empty</label><input id="contact-website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="contact-name">Your name <span class="text-red-600">*</span></label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="contact-name" name="name" type="text" maxlength="160" autocomplete="name" required value="<?= e_value($old['name'] ?? '') ?>">
          <?php if (isset($errors['name'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['name']) ?></p><?php endif; ?>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-bold text-slate-800" for="contact-phone">Phone</label>
            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="contact-phone" name="phone" type="tel" maxlength="40" autocomplete="tel" value="<?= e_value($old['phone'] ?? '') ?>">
            <?php if (isset($errors['phone'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['phone']) ?></p><?php endif; ?>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-800" for="contact-email">Email</label>
            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="contact-email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e_value($old['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['email']) ?></p><?php endif; ?>
          </div>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="contact-subject">Subject</label>
          <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="contact-subject" name="subject" type="text" maxlength="200" value="<?= e_value($old['subject'] ?? '') ?>">
          <?php if (isset($errors['subject'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['subject']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-800" for="contact-message">Message <span class="text-red-600">*</span></label>
          <textarea class="mt-2 min-h-36 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20" id="contact-message" name="message" rows="6" maxlength="5000" required><?= e_value($old['message'] ?? '') ?></textarea>
          <?php if (isset($errors['message'])): ?><p class="mt-2 text-sm text-red-700"><?= e_value($errors['message']) ?></p><?php endif; ?>
        </div>
        <button class="inline-flex w-full items-center justify-center rounded-xl bg-brand-red px-6 py-3.5 font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 sm:w-auto" type="submit">Send message</button>
      </form>
      <p class="mt-5 text-xs leading-5 text-slate-500">Booking inquiries belong on the relevant service page. An inquiry is not a confirmed ticket or booking.</p>
    </div>
  </div>
</section>
