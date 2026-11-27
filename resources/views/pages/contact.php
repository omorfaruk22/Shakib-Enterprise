<?php $_activeNav = 'contact'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Contact Us']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center max-w-2xl mx-auto mb-10">
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-3">Get In Touch</h1>
      <p class="text-gray-600">Have a question or want to book a service? Reach out through any of the channels below or send us a message.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-4 mb-10">
      <?php if (setting('address')): ?>
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center">
        <div class="w-14 h-14 rounded-full bg-brand-light text-brand-navy flex items-center justify-center text-xl mx-auto mb-3"><i class="fa-solid fa-location-dot"></i></div>
        <h3 class="font-bold text-brand-navy mb-1">Office Address</h3>
        <p class="text-sm text-gray-600"><?= htmlspecialchars(setting('address')) ?></p>
      </div>
      <?php endif; ?>
      <?php if (setting('phone')): ?>
      <a href="tel:<?= htmlspecialchars(setting('phone')) ?>" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center block hover:shadow-brand transition">
        <div class="w-14 h-14 rounded-full bg-brand-light text-brand-navy flex items-center justify-center text-xl mx-auto mb-3"><i class="fa-solid fa-phone"></i></div>
        <h3 class="font-bold text-brand-navy mb-1">Call Us</h3>
        <p class="text-sm text-gray-600"><?= htmlspecialchars(setting('phone')) ?></p>
      </a>
      <?php endif; ?>
      <?php if (setting('whatsapp')): ?>
      <a href="<?= whatsapp_url('Hello! I have a question about your travel services.') ?>" target="_blank" rel="noopener" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center block hover:shadow-brand transition">
        <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl mx-auto mb-3"><i class="fa-brands fa-whatsapp"></i></div>
        <h3 class="font-bold text-brand-navy mb-1">WhatsApp</h3>
        <p class="text-sm text-gray-600">Chat with us instantly</p>
      </a>
      <?php endif; ?>
      <?php if (setting('email')): ?>
      <a href="mailto:<?= htmlspecialchars(setting('email')) ?>" class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center block hover:shadow-brand transition">
        <div class="w-14 h-14 rounded-full bg-brand-light text-brand-navy flex items-center justify-center text-xl mx-auto mb-3"><i class="fa-solid fa-envelope"></i></div>
        <h3 class="font-bold text-brand-navy mb-1">Email Us</h3>
        <p class="text-sm text-gray-600"><?= htmlspecialchars(setting('email')) ?></p>
      </a>
      <?php endif; ?>
      <?php if (setting('opening_hours')): ?>
      <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center">
        <div class="w-14 h-14 rounded-full bg-brand-light text-brand-navy flex items-center justify-center text-xl mx-auto mb-3"><i class="fa-regular fa-clock"></i></div>
        <h3 class="font-bold text-brand-navy mb-1">Opening Hours</h3>
        <p class="text-sm text-gray-600"><?= htmlspecialchars(setting('opening_hours')) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 items-start">
      <div class="bg-white rounded-2xl shadow-brand p-6 md:p-8 border border-gray-100">
        <h2 class="font-display font-bold text-2xl text-brand-navy mb-5">Send us a Message</h2>
        <form method="POST" action="<?= url('/contact') ?>" class="space-y-4">
          <?= csrf_field() ?>
          <div class="honeypot" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="website" tabindex="-1"></div>
          <?php partial('input', ['name'=>'name','label'=>'Your Name','required'=>true]); ?>
          <div class="grid md:grid-cols-2 gap-4">
            <?php partial('input', ['name'=>'phone','label'=>'Phone','type'=>'tel','required'=>true,'placeholder'=>'01XXXXXXXXX']); ?>
            <?php partial('input', ['name'=>'email','label'=>'Email','type'=>'email','required'=>false]); ?>
          </div>
          <?php partial('input', ['name'=>'subject','label'=>'Subject','required'=>true,'placeholder'=>'How can we help you?']); ?>
          <?php partial('textarea', ['name'=>'message','label'=>'Message','required'=>true,'rows'=>5,'placeholder'=>'Write your message here...']); ?>
          <button type="submit" class="btn-accent px-6 py-3 rounded-lg font-semibold shadow-brand"><i class="fa-solid fa-paper-plane mr-2"></i> Send Message</button>
        </form>
      </div>
      <div>
        <?php if (setting('maps_embed')): ?>
          <div class="rounded-2xl overflow-hidden shadow-brand border border-gray-100 h-80">
            <iframe src="<?= htmlspecialchars(setting('maps_embed')) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
          </div>
        <?php else: ?>
          <div class="rounded-2xl bg-brand-light h-80 flex items-center justify-center text-gray-500 text-sm">
            <div class="text-center"><i class="fa-solid fa-map-location-dot text-4xl mb-3 text-brand-navy/30"></i><p>Map will appear here once<br>configured from Admin Settings.</p></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
