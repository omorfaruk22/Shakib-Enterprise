<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title ?? setting('meta_title', setting('business_name', 'SAKIB ENTERPRISE'))) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDescription ?? setting('meta_description', 'SAKIB ENTERPRISE — Your Trusted Travel Partner in Cox\'s Bazar. Air tickets, ship tickets, bus tickets, tours and holiday packages.')) ?>">
<meta name="theme-color" content="#102B70">
<link rel="canonical" href="<?= htmlspecialchars($canonical ?? url($_SERVER['REQUEST_URI'] ?? '/')) ?>">
<meta property="og:title" content="<?= htmlspecialchars($title ?? setting('business_name','SAKIB ENTERPRISE')) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? setting('meta_description','Your Trusted Travel Partner')) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($canonical ?? url('/')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        'brand-navy':'#102B70','brand-red':'#D52D35','brand-light':'#EAF2FC',
        'brand-dark':'#0A1F4D','brand-gray':'#F8FAFC'
      },
      fontFamily: {
        sans: ['Inter','system-ui','sans-serif'],
        display: ['Poppins','Inter','sans-serif']
      },
      boxShadow: {
        'brand':'0 10px 30px -10px rgba(16,43,112,0.25)'
      }
    }
  }
}
</script>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body class="bg-white text-gray-800 antialiased">
<?php require __DIR__ . '/../components/header.php'; ?>
<main><?= $content ?? '' ?></main>
<?php require __DIR__ . '/../components/footer.php'; ?>
<?php if (setting('whatsapp')): require __DIR__ . '/../components/whatsapp-float.php'; endif; ?>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
