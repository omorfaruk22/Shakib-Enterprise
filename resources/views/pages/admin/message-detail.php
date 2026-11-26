<?php
declare(strict_types=1);
?>
<a class="text-sm font-semibold text-brand-red hover:underline" href="<?= e(app_url('/admin/messages')) ?>">← Contact messages</a>
<div class="mt-4 grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
  <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <p class="text-xs font-bold uppercase tracking-wider text-slate-500"><?= e_value($message['created_at']) ?> · <?= e_value(ucfirst($message['status'])) ?></p>
    <h1 class="mt-3 text-3xl font-extrabold text-brand-navy"><?= e_value($message['subject'] ?: 'General inquiry') ?></h1>
    <p class="mt-6 whitespace-pre-line leading-7 text-slate-700"><?= e_value($message['message']) ?></p>
  </article>
  <aside class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-extrabold text-brand-navy">Sender</h2>
      <p class="mt-3 font-semibold"><?= e_value($message['name']) ?></p>
      <?php if (($message['phone'] ?? '') !== ''): ?><p class="mt-2 text-sm text-slate-600"><a class="hover:underline" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $message['phone']) ?? '') ?>"><?= e_value($message['phone']) ?></a></p><?php endif; ?>
      <?php if (($message['email'] ?? '') !== ''): ?><p class="mt-2 break-all text-sm text-slate-600"><a class="hover:underline" href="mailto:<?= e($message['email']) ?>"><?= e_value($message['email']) ?></a></p><?php endif; ?>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-extrabold text-brand-navy">Update message</h2>
      <form class="mt-4 space-y-4" action="<?= e(app_url('/admin/messages/' . (int) $message['id'] . '/update')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <label class="block text-sm font-semibold">Status<select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" name="status"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>"<?= $message['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
        <label class="block text-sm font-semibold">Admin note<textarea class="mt-1 min-h-28 w-full rounded-lg border border-slate-300 px-3 py-2" name="admin_note" maxlength="10000"><?= e_value($message['admin_note'] ?? '') ?></textarea></label>
        <button class="w-full rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900" type="submit">Save message</button>
      </form>
      <form class="mt-5 border-t border-slate-200 pt-4" action="<?= e(app_url('/admin/messages/' . (int) $message['id'] . '/delete')) ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><label class="flex gap-2 text-xs text-slate-600"><input class="mt-0.5" name="confirm_delete" type="checkbox" value="1" required><span>I confirm deleting this contact message.</span></label><button class="mt-3 rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50" type="submit">Delete message</button></form>
    </section>
  </aside>
</div>
