<?php $_activeNav = 'about'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'About Us']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">
    <div>
      <span class="inline-block bg-brand-light text-brand-navy font-semibold text-xs tracking-widest uppercase px-3 py-1 rounded-full mb-4">About Us</span>
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-4"><?= htmlspecialchars($page['title'] ?? 'About SAKIB ENTERPRISE') ?></h1>
      <div class="text-gray-700 leading-relaxed whitespace-pre-line mb-6"><?= htmlspecialchars($page['content'] ?? 'SAKIB ENTERPRISE is a trusted travel agency based in Cox\'s Bazar Sadar, Bangladesh. We specialize in air tickets, ship tickets, bus tickets, and premium tour and holiday packages across Bangladesh. Our mission is to make travel simple, reliable and enjoyable for every customer.') ?></div>
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-brand-light p-5 rounded-xl">
          <div class="font-display font-extrabold text-3xl text-brand-navy mb-1">10+</div>
          <div class="text-sm text-gray-600">Years of Experience</div>
        </div>
        <div class="bg-brand-light p-5 rounded-xl">
          <div class="font-display font-extrabold text-3xl text-brand-navy mb-1">5000+</div>
          <div class="text-sm text-gray-600">Happy Travelers</div>
        </div>
      </div>
    </div>
    <div class="relative">
      <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800&auto=format&fit=crop" alt="Travel" class="rounded-2xl shadow-brand w-full aspect-[4/5] object-cover">
      <div class="absolute -bottom-5 -left-5 bg-brand-red text-white p-5 rounded-xl shadow-brand hidden md:block">
        <div class="font-display font-extrabold text-3xl">24/7</div>
        <div class="text-sm">Travel Support</div>
      </div>
    </div>
  </div>
</section>

<section class="py-12 md:py-16 bg-brand-light">
  <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-6">
    <div class="bg-white p-8 rounded-2xl shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-brand-navy text-white flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-bullseye"></i></div>
      <h3 class="font-display font-bold text-2xl text-brand-navy mb-3">Our Mission</h3>
      <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($mission ?? 'To provide reliable, affordable and high-quality travel services to every customer — making travel in Bangladesh and abroad safe, accessible and memorable.') ?></p>
    </div>
    <div class="bg-white p-8 rounded-2xl shadow-sm">
      <div class="w-12 h-12 rounded-xl bg-brand-red text-white flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-eye"></i></div>
      <h3 class="font-display font-bold text-2xl text-brand-navy mb-3">Our Vision</h3>
      <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($vision ?? 'To become the most trusted travel partner in Bangladesh, known for honesty, quality service and customer care.') ?></p>
    </div>
  </div>
</section>

<section class="py-12 md:py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <?php partial('section-heading', ['eyebrow'=>'Why Choose Us','title'=>'The SAKIB ENTERPRISE Difference','subtitle'=>'Built on trust, transparency and a passion for travel.','accent'=>true]); ?>
    <div class="grid md:grid-cols-3 gap-6">
      <?php
      $why = [
        ['fa-solid fa-handshake','Trusted Locally','Based in Cox\'s Bazar Sadar, we are part of the community we serve.'],
        ['fa-solid fa-user-tie','Expert Team','Experienced travel consultants to help you plan every detail.'],
        ['fa-solid fa-shield','Safe & Secure','We work only with licensed operators and verified partners.'],
        ['fa-solid fa-sack-dollar','Transparent Pricing','No hidden charges — fair prices, quoted clearly.'],
        ['fa-solid fa-headset','Always Available','We are reachable by phone, WhatsApp and in person.'],
        ['fa-solid fa-award','Quality Service','From first inquiry to return trip — quality every step.'],
      ];
      foreach ($why as $w): ?>
      <div class="flex gap-4 p-5 rounded-xl border border-gray-100 hover:border-brand-red/30 hover:bg-brand-light transition">
        <div class="w-12 h-12 rounded-xl bg-brand-light text-brand-navy flex items-center justify-center text-lg flex-shrink-0"><i class="<?= $w[0] ?>"></i></div>
        <div><h4 class="font-bold text-brand-navy mb-1"><?= $w[1] ?></h4><p class="text-sm text-gray-600 leading-relaxed"><?= $w[2] ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
