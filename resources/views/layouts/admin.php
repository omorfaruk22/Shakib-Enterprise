<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Admin | SAKIB ENTERPRISE') ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {'brand-navy':'#102B70','brand-red':'#D52D35','brand-light':'#EAF2FC','brand-dark':'#0A1F4D'},
      fontFamily: { sans: ['Inter','sans-serif'], display: ['Poppins','Inter','sans-serif'] }
    }
  }
}
</script>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="bg-gray-50 text-gray-800">
<div class="flex min-h-screen">
  <?php require __DIR__ . '/../components/admin-sidebar.php'; ?>
  <div class="flex-1 flex flex-col lg:ml-64">
    <?php require __DIR__ . '/../components/admin-topbar.php'; ?>
    <main class="flex-1 p-3 md:p-6 pt-16 md:pt-6 fade-in">
      <?php if ($msg = flash('success')): ?><div class="flash-msg mb-5 bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('error')): ?><div class="flash-msg mb-5 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?= $content ?? '' ?>
    </main>
  </div>
</div>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
