<?php
declare(strict_types=1);
$pageUrl = static fn (int $number): string => app_url('/admin/customers?' . http_build_query(['q' => $search, 'page' => $number]));
?>
<div><h1 class="text-3xl font-extrabold text-brand-navy">Customers</h1><p class="mt-2 text-slate-600"><?= e_value((string) $result['total']) ?> customer records.</p></div>
<form class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row" action="<?= e(app_url('/admin/customers')) ?>" method="get">
  <label class="flex-1"><span class="block text-xs font-bold text-slate-600">Search name, phone or email</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="q" type="search" maxlength="120" value="<?= e_value($search) ?>"></label>
  <div class="flex items-end gap-2"><button class="rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white" type="submit">Search</button><a class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold" href="<?= e(app_url('/admin/customers')) ?>">Clear</a></div>
</form>
<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <?php if ($result['items'] === []): ?><p class="p-10 text-center text-sm text-slate-500">No customers found.</p><?php else: ?>
    <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Phone</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Inquiries</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($result['items'] as $customer): ?><tr><td class="px-5 py-4 font-bold text-brand-navy"><?= e_value($customer['full_name']) ?></td><td class="px-5 py-4"><?= e_value($customer['phone']) ?></td><td class="px-5 py-4"><?= e_value($customer['email'] ?? '—') ?></td><td class="px-5 py-4"><?= e_value((string) $customer['booking_count']) ?></td><td class="px-5 py-4"><a class="font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/customers/' . (int) $customer['id'])) ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div>
  <?php endif; ?>
</section>
<?php if ($result['pages'] > 1): ?><nav class="mt-5 flex justify-between text-sm"><span>Page <?= e_value((string) $result['page']) ?> of <?= e_value((string) $result['pages']) ?></span><span class="flex gap-2"><?php if ($result['page'] > 1): ?><a class="rounded-lg border bg-white px-4 py-2" href="<?= e($pageUrl($result['page'] - 1)) ?>">Previous</a><?php endif; ?><?php if ($result['page'] < $result['pages']): ?><a class="rounded-lg border bg-white px-4 py-2" href="<?= e($pageUrl($result['page'] + 1)) ?>">Next</a><?php endif; ?></span></nav><?php endif; ?>
