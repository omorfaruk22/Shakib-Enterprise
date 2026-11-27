<?php
declare(strict_types=1);

$pageHeading = 'Request ' . strtolower((string) $bookingType['label']);
$pageEyebrow = 'Booking inquiry';
$pageLead = 'Share your travel details. The team will review your request and contact you to confirm availability and next steps.';
require BASE_PATH . '/resources/views/partials/page-heading.php';
$oldValue = static fn (string $name, string $fallback = ''): string => e_value($old[$name] ?? $fallback);
$fieldError = static fn (string $name): string => (string) ($errors[$name] ?? '');
$inputClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-navy focus:ring-4 focus:ring-brand-navy/10';
$labelClass = 'block text-sm font-bold text-slate-700';
$journeyLabel = in_array($bookingType['slug'], ['tour-travel', 'holiday-package'], true) ? 'Preferred start date (optional)' : 'Journey date';
?>
<section class="py-10 sm:py-14">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1.25fr_.75fr] lg:px-8">
    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
      <?php if ($errors !== []): ?><div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"><p class="font-bold">Please review the highlighted details.</p><ul class="mt-2 list-inside list-disc space-y-1"><?php foreach ($errors as $error): ?><li><?= e_value($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <?php if (!$pageService->databaseAvailable()): ?><p class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">The database is temporarily unavailable. You can review this form, but inquiries cannot be saved right now.</p><?php endif; ?>
      <form class="space-y-6" method="post" action="<?= e(app_url('/booking/submit')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="_submission_token" value="<?= e($submissionToken) ?>">
        <input type="hidden" name="service_slug" value="<?= e((string) $bookingType['slug']) ?>">
        <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true"><label for="company-website">Leave this field empty</label><input id="company-website" name="company_website" type="text" tabindex="-1" autocomplete="off"></div>

        <section aria-labelledby="customer-details-title">
          <h2 id="customer-details-title" class="text-xl font-extrabold text-brand-navy">Your account details</h2>
          <div class="mt-4 grid gap-4 rounded-2xl bg-slate-50 p-4 sm:grid-cols-3">
            <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Name</p><p class="mt-1 font-semibold text-slate-800"><?= e_value($authenticatedCustomer['full_name'] ?? '') ?></p></div>
            <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Phone</p><p class="mt-1 font-semibold text-slate-800"><?= e_value($authenticatedCustomer['phone'] ?? '') ?></p></div>
            <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Account email</p><p class="mt-1 break-all font-semibold text-slate-800"><?= e_value($authenticatedCustomer['account_email'] ?? '') ?></p></div>
          </div>
          <p class="mt-2 text-xs leading-5 text-slate-500">This inquiry will be saved to your customer account. Contact the business if your account phone needs to be updated.</p>
        </section>

        <section aria-labelledby="travel-details-title">
          <h2 id="travel-details-title" class="text-xl font-extrabold text-brand-navy">Travel details</h2>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <?php if (in_array($bookingType['slug'], ['air-ticket', 'bus-ticket'], true)): ?>
              <div><label class="<?= $labelClass ?>" for="departure-location">Departure city <span class="text-brand-red">*</span></label><input class="<?= $inputClass ?>" id="departure-location" name="departure_location" value="<?= $oldValue('departure_location') ?>" maxlength="180" required aria-describedby="departure-error"><?php if ($fieldError('departure_location') !== ''): ?><p class="mt-1 text-sm text-red-700" id="departure-error"><?= e_value($fieldError('departure_location')) ?></p><?php endif; ?></div>
              <div><label class="<?= $labelClass ?>" for="destination">Destination <span class="text-brand-red">*</span></label><input class="<?= $inputClass ?>" id="destination" name="destination" value="<?= $oldValue('destination') ?>" maxlength="180" required aria-describedby="destination-error"><?php if ($fieldError('destination') !== ''): ?><p class="mt-1 text-sm text-red-700" id="destination-error"><?= e_value($fieldError('destination')) ?></p><?php endif; ?></div>
            <?php elseif ($bookingType['slug'] === 'ship-ticket'): ?>
              <?php if ($shipRoutes !== []): ?>
                <div class="sm:col-span-2"><label class="<?= $labelClass ?>" for="ship-route-id">Published ship route <span class="font-normal text-slate-500">(optional)</span></label><select class="<?= $inputClass ?>" id="ship-route-id" name="ship_route_id" data-ship-route><option value="">Choose a route or enter it below</option><?php foreach ($shipRoutes as $route): ?><option value="<?= e_value($route['id']) ?>" data-origin="<?= e_value($route['origin']) ?>" data-destination="<?= e_value($route['destination']) ?>"<?= $oldValue('ship_route_id') === (string) $route['id'] ? ' selected' : '' ?>><?= e_value($route['origin']) ?> → <?= e_value($route['destination']) ?></option><?php endforeach; ?></select><?php if ($fieldError('ship_route_id') !== ''): ?><p class="mt-1 text-sm text-red-700"><?= e_value($fieldError('ship_route_id')) ?></p><?php endif; ?></div>
              <?php endif; ?>
              <div><label class="<?= $labelClass ?>" for="departure-location">Origin port <?= $shipRoutes === [] ? '<span class="text-brand-red">*</span>' : '<span class="font-normal text-slate-500">(if entering a different route)</span>' ?></label><input class="<?= $inputClass ?>" id="departure-location" name="departure_location" value="<?= $oldValue('departure_location') ?>" maxlength="180" <?= $shipRoutes === [] ? 'required' : '' ?> aria-describedby="departure-error"><?php if ($fieldError('departure_location') !== ''): ?><p class="mt-1 text-sm text-red-700" id="departure-error"><?= e_value($fieldError('departure_location')) ?></p><?php endif; ?></div>
              <div><label class="<?= $labelClass ?>" for="destination">Destination port <?= $shipRoutes === [] ? '<span class="text-brand-red">*</span>' : '<span class="font-normal text-slate-500">(if entering a different route)</span>' ?></label><input class="<?= $inputClass ?>" id="destination" name="destination" value="<?= $oldValue('destination') ?>" maxlength="180" <?= $shipRoutes === [] ? 'required' : '' ?> aria-describedby="destination-error"><?php if ($fieldError('destination') !== ''): ?><p class="mt-1 text-sm text-red-700" id="destination-error"><?= e_value($fieldError('destination')) ?></p><?php endif; ?></div>
            <?php else: ?>
              <div class="sm:col-span-2"><label class="<?= $labelClass ?>" for="destination">Preferred destination <span class="font-normal text-slate-500">(optional)</span></label><input class="<?= $inputClass ?>" id="destination" name="destination" value="<?= $oldValue('destination', $requestedDestination) ?>" maxlength="180"></div>
              <?php if ($packages !== []): ?><div class="sm:col-span-2"><label class="<?= $labelClass ?>" for="package-slug">Published package <span class="font-normal text-slate-500">(optional)</span></label><select class="<?= $inputClass ?>" id="package-slug" name="package_slug"><option value="">Choose a package or ask about a custom trip</option><?php foreach ($packages as $package): ?><option value="<?= e_value($package['slug']) ?>"<?= $oldValue('package_slug', $requestedPackage) === (string) $package['slug'] ? ' selected' : '' ?>><?= e_value($package['title']) ?><?= trim((string) ($package['destination_name'] ?? '')) !== '' ? ' · ' . e_value($package['destination_name']) : '' ?></option><?php endforeach; ?></select><?php if ($fieldError('package_slug') !== ''): ?><p class="mt-1 text-sm text-red-700"><?= e_value($fieldError('package_slug')) ?></p><?php endif; ?></div><?php endif; ?>
            <?php endif; ?>
            <div><label class="<?= $labelClass ?>" for="journey-date"><?= e_value($journeyLabel) ?><?= $bookingType['date_required'] ? ' *' : '' ?></label><input class="<?= $inputClass ?>" id="journey-date" name="journey_date" type="date" min="<?= e_value(date('Y-m-d')) ?>" value="<?= $oldValue('journey_date') ?>" <?= $bookingType['date_required'] ? 'required' : '' ?> aria-describedby="journey-error"><?php if ($fieldError('journey_date') !== ''): ?><p class="mt-1 text-sm text-red-700" id="journey-error"><?= e_value($fieldError('journey_date')) ?></p><?php endif; ?></div>
            <div><label class="<?= $labelClass ?>" for="return-date">Return date <span class="font-normal text-slate-500">(optional)</span></label><input class="<?= $inputClass ?>" id="return-date" name="return_date" type="date" min="<?= e_value(date('Y-m-d')) ?>" value="<?= $oldValue('return_date') ?>" aria-describedby="return-error"><?php if ($fieldError('return_date') !== ''): ?><p class="mt-1 text-sm text-red-700" id="return-error"><?= e_value($fieldError('return_date')) ?></p><?php endif; ?></div>
            <div><label class="<?= $labelClass ?>" for="passenger-count">Number of travelers <span class="text-brand-red">*</span></label><input class="<?= $inputClass ?>" id="passenger-count" name="passenger_count" type="number" min="1" max="50" step="1" value="<?= $oldValue('passenger_count', '1') ?>" required aria-describedby="passenger-count-error"><?php if ($fieldError('passenger_count') !== ''): ?><p class="mt-1 text-sm text-red-700" id="passenger-count-error"><?= e_value($fieldError('passenger_count')) ?></p><?php endif; ?></div>
            <?php if (in_array($bookingType['slug'], ['air-ticket', 'bus-ticket'], true)): ?>
              <div><label class="<?= $labelClass ?>" for="travel-class">Preferred travel class <span class="font-normal text-slate-500">(optional)</span></label><select class="<?= $inputClass ?>" id="travel-class" name="travel_class"><option value="">No preference</option><?php foreach (['Economy', 'Business', 'First class', 'AC', 'Non-AC'] as $class): ?><option value="<?= e_value($class) ?>"<?= $oldValue('travel_class') === $class ? ' selected' : '' ?>><?= e_value($class) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>
            <div class="sm:col-span-2"><label class="<?= $labelClass ?>" for="passenger-type">Traveler mix <span class="font-normal text-slate-500">(optional)</span></label><input class="<?= $inputClass ?>" id="passenger-type" name="passenger_type" value="<?= $oldValue('passenger_type') ?>" maxlength="80" placeholder="e.g. 2 adults and 1 child"></div>
            <div class="sm:col-span-2"><label class="<?= $labelClass ?>" for="customer-message">Additional details <span class="font-normal text-slate-500">(optional)</span></label><textarea class="<?= $inputClass ?> min-h-32 resize-y" id="customer-message" name="customer_message" maxlength="4000" placeholder="Share any preferences or questions."><?= $oldValue('customer_message') ?></textarea><p class="mt-1 text-xs text-slate-500">Please do not include passport, payment or other sensitive document details.</p><?php if ($fieldError('customer_message') !== ''): ?><p class="mt-1 text-sm text-red-700"><?= e_value($fieldError('customer_message')) ?></p><?php endif; ?></div>
          </div>
        </section>

        <button class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-red px-6 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg sm:w-auto" type="submit">Send booking inquiry <span aria-hidden="true">→</span></button>
        <p class="text-xs leading-5 text-slate-500">This request is an inquiry only. It does not issue a ticket, take payment or confirm a booking. The team will contact you after reviewing it.</p>
      </form>
    </div>

    <aside class="h-fit rounded-3xl bg-brand-navy p-7 text-white sm:p-8">
      <span class="grid size-12 place-items-center rounded-2xl bg-white/10 text-2xl" aria-hidden="true"><?= e_value($service['icon'] ?? '✦') ?></span>
      <h2 class="mt-5 text-2xl font-extrabold">Your <?= e_value(strtolower((string) $bookingType['label'])) ?></h2>
      <p class="mt-3 text-blue-100"><?= e_value($service['intro'] ?? 'Share your travel needs and the team will follow up.') ?></p>
      <ul class="mt-6 space-y-3 text-sm text-blue-100"><li class="flex gap-2"><span aria-hidden="true">✓</span>Use a phone number the team can reach.</li><li class="flex gap-2"><span aria-hidden="true">✓</span>Double-check your route and dates.</li><li class="flex gap-2"><span aria-hidden="true">✓</span>The team confirms current availability with you.</li></ul>
      <?php if ($bookingType['slug'] === 'tour-travel' || $bookingType['slug'] === 'holiday-package'): ?><a class="mt-6 inline-flex font-bold text-white underline decoration-white/50 underline-offset-4 hover:decoration-white" href="<?= e(app_url('/packages')) ?>">Browse published packages</a><?php endif; ?>
    </aside>
  </div>
</section>
