<?php $_activeNav = 'home';
$heroImg = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600&auto=format&fit=crop';
?>
<!-- HERO -->
<section class="relative h-[560px] md:h-[640px] overflow-hidden">
  <img src="<?= $heroImg ?>" alt="Cox's Bazar Sea Beach" class="absolute inset-0 w-full h-full object-cover" loading="eager">
  <div class="absolute inset-0 hero-overlay"></div>
  <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">
    <div class="max-w-2xl text-white fade-in">
      <span class="inline-block bg-brand-red/90 text-white text-xs font-bold tracking-widest uppercase px-3 py-1 rounded-full mb-5"><?= htmlspecialchars(setting('tagline','Your Trusted Travel Partner')) ?></span>
      <h1 class="font-display font-extrabold text-4xl md:text-6xl leading-tight mb-5"><?= htmlspecialchars(setting('hero_title','Explore the World with SAKIB ENTERPRISE')) ?></h1>
      <p class="text-lg text-white/90 mb-8 leading-relaxed max-w-xl"><?= htmlspecialchars(setting('hero_description','Book air tickets, ship tickets, bus tickets and curated tour & holiday packages in Cox\'s Bazar and across Bangladesh. We make travel easy and reliable.')) ?></p>
      <div class="flex flex-wrap gap-3">
        <a href="<?= url('/tours') ?>" class="btn-accent px-6 py-3.5 rounded-lg font-semibold shadow-brand inline-flex items-center gap-2"><i class="fa-solid fa-compass"></i> Explore Tours</a>
        <a href="<?= url('/contact') ?>" class="bg-white/10 backdrop-blur border-2 border-white/40 text-white px-6 py-3.5 rounded-lg font-semibold hover:bg-white hover:text-brand-navy transition inline-flex items-center gap-2"><i class="fa-solid fa-phone"></i> Contact Us</a>
      </div>
    </div>
  </div>
</section>

<!-- QUICK SEARCH / SERVICE SHORTCUTS -->
<section class="relative -mt-12 z-10">
  <div class="max-w-7xl mx-auto px-4">
    <div class="bg-white rounded-2xl shadow-brand p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php
      $quick = [
        ['/air-ticket','fa-solid fa-plane','Air Ticket','Domestic & International'],
        ['/ship-ticket','fa-solid fa-ship','Ship Ticket','Cox\'s Bazar Routes'],
        ['/bus-ticket','fa-solid fa-bus','Bus Ticket','Countrywide'],
        ['/tours','fa-solid fa-map-location-dot','Tour Packages','Cox\'s Bazar & Beyond'],
      ];
      foreach ($quick as $q): ?>
        <a href="<?= url($q[0]) ?>" class="flex flex-col items-center p-4 rounded-xl hover:bg-brand-light transition text-center">
          <div class="w-14 h-14 rounded-full bg-brand-light text-brand-navy flex items-center justify-center text-xl mb-3 group-hover:bg-brand-navy group-hover:text-white"><i class="<?= $q[1] ?>"></i></div>
          <div class="font-bold text-brand-navy"><?= $q[2] ?></div>
          <div class="text-xs text-gray-500 mt-1"><?= $q[3] ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="py-16 md:py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Our Services','title'=>setting('homepage_services_title','Our Services'),'subtitle'=>setting('homepage_services_subtitle','From tickets to tours — we handle every part of your journey.'),'accent'=>true]); ?>
    <?php if (empty($services)): ?>
      <?php partial('empty-state', ['icon'=>'fa-solid fa-briefcase','title'=>'Services coming soon','description'=>'Add services from Admin → Services menu.']); ?>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($services as $s): ?>
        <?php partial('service-card', ['service' => $s, 'url' => '/' . $s['slug']]); ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- DESTINATIONS -->
<section class="py-16 md:py-20 bg-brand-light">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Popular Destinations','title'=>setting('homepage_destinations_title','Where Do You Want to Go?'),'subtitle'=>setting('homepage_destinations_subtitle','Discover beautiful destinations with our curated tours.'),'accent'=>true]); ?>
    <?php if (empty($destinations)): ?>
      <?php partial('empty-state', ['icon'=>'fa-solid fa-location-dot','title'=>'No destinations yet','description'=>'Add destinations from Admin → Destinations menu.']); ?>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($destinations as $d): ?>
        <?php partial('destination-card', ['destination' => $d]); ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10"><a href="<?= url('/tours') ?>" class="btn-primary px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2">View All Tours <i class="fa-solid fa-arrow-right"></i></a></div>
    <?php endif; ?>
  </div>
</section>

<!-- TOUR PACKAGES -->
<section class="py-16 md:py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Featured Tours','title'=>setting('homepage_tours_title','Tour Packages'),'subtitle'=>setting('homepage_tours_subtitle','Carefully designed tour packages with comfort and safety.'),'accent'=>true]); ?>
    <?php if (empty($tours)): ?>
      <?php partial('empty-state', ['icon'=>'fa-solid fa-route','title'=>'No tours yet','description'=>'Add tour packages from Admin → Tour Packages menu.']); ?>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($tours as $t): ?>
        <?php partial('package-card', ['package' => $t, 'type' => 'tour']); ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- HOLIDAY PACKAGES -->
<section class="py-16 md:py-20 bg-brand-light">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Holiday Packages','title'=>setting('homepage_holidays_title','Holiday Packages'),'subtitle'=>setting('homepage_holidays_subtitle','Relax and unwind on our premium holiday packages.'),'accent'=>true]); ?>
    <?php if (empty($holidays)): ?>
      <?php partial('empty-state', ['icon'=>'fa-solid fa-umbrella-beach','title'=>'No holidays yet','description'=>'Add holiday packages from Admin → Holiday Packages menu.']); ?>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($holidays as $h): ?>
        <?php partial('package-card', ['package' => $h, 'type' => 'holiday']); ?>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10"><a href="<?= url('/holiday-packages') ?>" class="btn-accent px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2">View All Holidays <i class="fa-solid fa-arrow-right"></i></a></div>
    <?php endif; ?>
  </div>
</section>

<!-- COMPANY STATS -->
<?php if (!empty($companyStats)): ?>
<section class="py-12 bg-brand-navy text-white">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
      <?php foreach ($companyStats as $s): ?>
        <div class="text-center">
          <div class="text-3xl md:text-4xl font-display font-extrabold text-brand-accent mb-1"><?= htmlspecialchars($s['number']) ?></div>
          <div class="flex items-center justify-center gap-2 text-white/80 text-sm">
            <i class="<?= htmlspecialchars($s['icon']) ?>"></i>
            <span><?= htmlspecialchars($s['label']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- WHY CHOOSE US -->
<section class="py-16 md:py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Why Choose Us','title'=>setting('homepage_whyus_title','Why Choose Us'),'subtitle'=>setting('homepage_whyus_subtitle','We are committed to making your trip safe, comfortable and memorable.'),'accent'=>true]); ?>
    <?php if (empty($whyUsCards)): ?>
      <?php partial('empty-state', ['icon'=>'fa-solid fa-star','title'=>'Why Us cards coming soon','description'=>'Add cards from Admin → Why Us Cards menu.']); ?>
    <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($whyUsCards as $w): ?>
        <div class="p-6 rounded-2xl bg-brand-light">
          <div class="w-12 h-12 rounded-xl bg-brand-navy text-white flex items-center justify-center text-lg mb-4"><i class="<?= htmlspecialchars($w['icon']) ?>"></i></div>
          <h3 class="font-display font-bold text-lg text-brand-navy mb-2"><?= htmlspecialchars($w['title']) ?></h3>
          <p class="text-sm text-gray-600 leading-relaxed"><?= htmlspecialchars($w['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- BOOKING PROCESS -->
<section class="py-16 md:py-20 bg-brand-navy text-white">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Simple Process','title'=>setting('homepage_process_title','Our Booking Process'),'subtitle'=>setting('homepage_process_subtitle','Inquiry → Contact → Confirm — three easy steps to your journey.'),'accent'=>true]); ?>
    <?php if (empty($processSteps)): ?>
      <p class="text-center text-white/60 py-8">Process steps will appear here once added from Admin.</p>
    <?php else: ?>
    <div class="grid md:grid-cols-<?= min(3, count($processSteps)) ?> gap-6">
      <?php foreach ($processSteps as $s): ?>
        <div class="relative p-6 rounded-2xl bg-white/5 border border-white/10">
          <div class="w-12 h-12 rounded-full bg-brand-red text-white flex items-center justify-center font-display font-extrabold text-xl mb-4"><?= (int)$s['step_number'] ?></div>
          <h3 class="font-display font-bold text-xl mb-2"><?= htmlspecialchars($s['title']) ?></h3>
          <p class="text-white/80 text-sm leading-relaxed"><?= htmlspecialchars($s['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- TESTIMONIALS -->
<?php if (!empty($testimonials)): ?>
<section class="py-16 md:py-20 bg-brand-light">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Testimonials','title'=>setting('homepage_testimonials_title','What Our Customers Say'),'subtitle'=>setting('homepage_testimonials_subtitle','Real stories from travelers who chose SAKIB ENTERPRISE.'),'accent'=>true]); ?>
    <?php if (!empty($testimonials)): ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($testimonials as $t): ?>
        <?php partial('testimonial-card', ['testimonial' => $t]); ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-14 bg-brand-red text-white">
  <div class="max-w-5xl mx-auto px-4 text-center">
    <h2 class="font-display font-extrabold text-3xl md:text-4xl mb-3"><?= htmlspecialchars(setting('homepage_cta_title','Ready to Plan Your Next Trip?')) ?></h2>
    <p class="text-white/90 mb-6 max-w-xl mx-auto"><?= htmlspecialchars(setting('homepage_cta_text','Send us an inquiry or talk to us directly — we reply fast.')) ?></p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?= url('/contact') ?>" class="bg-white text-brand-red px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 inline-flex items-center gap-2"><i class="fa-solid fa-paper-plane"></i> Send Inquiry</a>
      <?php if (setting('whatsapp')): ?>
        <a href="<?= whatsapp_url('Hello! I want to plan a trip.') ?>" target="_blank" rel="noopener" class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 inline-flex items-center gap-2"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
</section>
