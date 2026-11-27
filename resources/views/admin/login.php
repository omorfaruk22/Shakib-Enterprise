<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | SAKIB ENTERPRISE</title>
<meta name="robots" content="noindex,nofollow">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{'brand-navy':'#102B70','brand-red':'#D52D35','brand-light':'#EAF2FC'},fontFamily:{sans:['Inter','sans-serif'],display:['Poppins','Inter','sans-serif']}}}}</script>
<style>
.btn-primary{background:#102B70;color:#fff}.btn-primary:hover{background:#0A1F4D}
.google-btn{border:2px solid #e5e7eb;color:#374151;background:#fff;transition:all .2s}.google-btn:hover{border-color:#dadce0;background:#f8fafc}
</style>
</head>
<body class="bg-brand-light min-h-screen flex items-center justify-center p-4">
<div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
  <div class="text-center mb-6">
    <div class="w-16 h-16 mx-auto bg-brand-navy text-white rounded-xl flex items-center justify-center font-display font-extrabold text-2xl mb-3">SE</div>
    <h1 class="font-display font-extrabold text-2xl text-brand-navy">Admin Login</h1>
    <p class="text-sm text-gray-500 mt-1">SAKIB ENTERPRISE Control Panel</p>
  </div>

  <?php if ($msg = flash('error')): ?><div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"><i class="fa-solid fa-circle-exclamation mr-1.5"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($msg = flash('success')): ?><div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm"><i class="fa-solid fa-check-circle mr-1.5"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <!-- Google-style "Sign in with email" hint -->
  <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-800 flex gap-2">
    <i class="fa-brands fa-google mt-0.5"></i>
    <span>আপনার <strong>@gmail.com</strong> বা অন্য যেকোনো ইমেইল অ্যাড্রেস ও পাসওয়ার্ড দিয়ে লগইন করুন।</span>
  </div>

  <form method="POST" action="<?= url('/admin/login') ?>" class="space-y-4">
    <?= csrf_field() ?>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        <i class="fa-solid fa-envelope text-gray-400 mr-1"></i> Email Address
      </label>
      <input type="email" name="email" required value="<?= htmlspecialchars(old('email')) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="you@gmail.com" autofocus autocomplete="email">
      <p class="text-[11px] text-gray-500 mt-1">Gmail, Yahoo, Outlook বা আপনার কাস্টম ইমেইল — সব সমর্থিত</p>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        <i class="fa-solid fa-lock text-gray-400 mr-1"></i> Password
      </label>
      <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy" placeholder="••••••••" autocomplete="current-password">
    </div>
    <button type="submit" class="w-full btn-primary py-3 rounded-lg font-semibold text-white shadow-brand hover:shadow-lg transition">
      <i class="fa-solid fa-right-to-bracket mr-2"></i>Sign In to Admin Panel
    </button>
  </form>

  <div class="mt-5 pt-4 border-t border-gray-100 space-y-2">
    <p class="text-xs text-gray-500 text-center">
      <i class="fa-solid fa-circle-info mr-1 text-brand-navy"></i>
      পাসওয়ার্ড ভুলে গেলে অন্য Super Admin-এর সঙ্গে যোগাযোগ করুন। নিরাপত্তার জন্য installer দিয়ে existing account reset করা যায় না।
    </p>
    <div class="text-center">
      <a href="<?= url('/') ?>" class="text-xs text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back to website</a>
    </div>
  </div>
</div>
</body>
</html>
