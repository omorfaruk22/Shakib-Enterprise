<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title ?? 'Login | SAKIB ENTERPRISE') ?></title>
<link rel="icon" type="image/svg+xml" href="<?= asset('assets/images/favicon.svg') ?>">
<meta name="theme-color" content="#102B70">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{'brand-navy':'#102B70','brand-red':'#D52D35','brand-light':'#EAF2FC'},fontFamily:{sans:['Inter','sans-serif'],display:['Poppins','Inter','sans-serif']}}}}</script>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="bg-brand-light min-h-screen flex flex-col">
<?php require __DIR__ . '/../components/header.php'; ?>
<main class="flex-1 flex items-center justify-center p-4 py-12">
<div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-6 md:p-8 fade-in">
  <div class="text-center mb-6">
    <div class="w-16 h-16 mx-auto bg-brand-navy text-white rounded-xl flex items-center justify-center font-display font-extrabold text-2xl mb-3"><i class="fa-solid fa-user"></i></div>
    <?= $content ?? '' ?>
  </div>
</div>
</main>
<?php require __DIR__ . '/../components/footer.php'; ?>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
