<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl text-brand-navy">Message Details</h1><a href="<?= url('/admin/contacts') ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Messages</a></div>
<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h2 class="font-bold text-xl text-brand-navy mb-1"><?= htmlspecialchars($message['subject']) ?></h2>
  <div class="text-sm text-gray-500 mb-4">From <strong><?= htmlspecialchars($message['name']) ?></strong> · <?= htmlspecialchars($message['phone']) ?> · <?= htmlspecialchars($message['email']) ?> · <?= htmlspecialchars(date('d M Y H:i', strtotime($message['created_at']))) ?></div>
  <div class="text-gray-700 leading-relaxed whitespace-pre-line mb-5"><?= htmlspecialchars($message['message']) ?></div>
  <div class="border-t pt-5">
    <h4 class="font-bold text-brand-navy mb-2">Admin Notes</h4>
    <form method="POST" action="<?= url('/admin/contacts/'.$message['id'].'/note') ?>" class="mb-3"><?= csrf_field() ?><textarea name="admin_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= htmlspecialchars($message['admin_notes'] ?? '') ?></textarea><button class="mt-2 btn-primary px-4 py-2 rounded-lg text-sm font-semibold">Save Note</button></form>
  </div>
</div>
<div class="space-y-4">
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <h4 class="font-bold text-brand-navy mb-3">Quick Actions</h4>
    <a href="tel:<?= htmlspecialchars($message['phone']) ?>" class="btn-primary w-full py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 mb-2"><i class="fa-solid fa-phone"></i> Call Customer</a>
    <?php if (setting('whatsapp')): ?><a href="https://wa.me/<?= preg_replace('/\D+/','',setting('whatsapp')) ?>?text=<?= urlencode('Hello! This is SAKIB ENTERPRISE regarding your message: ' . $message['subject']) ?>" target="_blank" class="bg-green-500 text-white w-full py-2 rounded-lg text-sm font-semibold inline-flex items-center justify-center gap-2 mb-2"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
    <form method="POST" action="<?= url('/admin/contacts/'.$message['id'].'/reply') ?>" class="mb-2"><?= csrf_field() ?><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_replied" value="1" <?= !empty($message['is_replied'])?'checked':'' ?> onchange="this.form.submit()"> <span class="font-semibold">Mark as Replied</span></label></form>
    <form method="POST" action="<?= url('/admin/contacts/'.$message['id'].'/read') ?>" class="mb-2"><?= csrf_field() ?><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_read" value="1" <?= !empty($message['is_read'])?'checked':'' ?> onchange="this.form.submit()"> <span class="font-semibold">Mark as Read</span></label></form>
    <form method="POST" action="<?= url('/admin/contacts/'.$message['id'].'/delete') ?>" data-confirm="Delete this message?" class="mt-3"><?= csrf_field() ?><button class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-trash mr-1"></i> Delete Message</button></form>
  </div>
</div>
</div>
