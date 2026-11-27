<?php $_activeNav = 'tours'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Tours','url'=>'/tours'],['label'=>$package['title'] ?? 'Tour Details']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-3 gap-10 items-start">
    <div class="lg:col-span-2">
      <?php $img = $package['image'] ?? 'https://images.unsplash.com/photo-1530789253388-582c481c54b0?w=1200&auto=format&fit=crop';
      if ($img && !str_starts_with($img,'http') && !str_starts_with($img,'/')) $img = asset($img); ?>
      <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($package['title'] ?? '') ?>" class="w-full rounded-2xl aspect-[16/9] object-cover shadow-brand mb-6">
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <?php if (!empty($package['is_featured'])): ?><span class="bg-brand-red text-white text-xs font-bold px-3 py-1 rounded-full">Featured</span><?php endif; ?>
        <?php if (!empty($package['destination_name'])): ?><span class="text-sm text-gray-600"><i class="fa-solid fa-location-dot text-brand-red mr-1"></i><?= htmlspecialchars($package['destination_name']) ?></span><?php endif; ?>
        <span class="text-sm text-gray-600"><i class="fa-regular fa-clock text-brand-red mr-1"></i><?= (int)($package['duration_days'] ?? 3) ?> Days / <?= (int)($package['duration_nights'] ?? 2) ?> Nights</span>
      </div>
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-4"><?= htmlspecialchars($package['title'] ?? '') ?></h1>
      <div class="prose max-w-none text-gray-700 leading-relaxed mb-6">
        <?php if (!empty($package['short_description'])): ?><p class="text-lg text-gray-600 mb-4"><?= htmlspecialchars($package['short_description']) ?></p><?php endif; ?>
        <?php if (!empty($package['description'])): ?><div class="whitespace-pre-line"><?= htmlspecialchars($package['description']) ?></div><?php endif; ?>
      </div>
      <?php if (!empty($package['itinerary'])): ?>
      <div class="mb-6">
        <h3 class="font-display font-bold text-xl text-brand-navy mb-3"><i class="fa-solid fa-route text-brand-red mr-2"></i> Itinerary</h3>
        <div class="bg-white border border-gray-100 rounded-xl p-5 whitespace-pre-line text-gray-700 leading-relaxed"><?= htmlspecialchars($package['itinerary']) ?></div>
      </div>
      <?php endif; ?>
      <div class="grid md:grid-cols-2 gap-4">
        <?php if (!empty($package['inclusions'])): ?>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5">
          <h4 class="font-bold text-green-800 mb-3"><i class="fa-solid fa-check-circle mr-2"></i> Inclusions</h4>
          <div class="text-sm text-green-900/80 whitespace-pre-line"><?= htmlspecialchars($package['inclusions']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($package['exclusions'])): ?>
        <div class="bg-red-50 border border-red-100 rounded-xl p-5">
          <h4 class="font-bold text-red-800 mb-3"><i class="fa-solid fa-xmark-circle mr-2"></i> Exclusions</h4>
          <div class="text-sm text-red-900/80 whitespace-pre-line"><?= htmlspecialchars($package['exclusions']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="lg:col-span-1">
      <div class="bg-white rounded-2xl shadow-brand p-6 border border-gray-100 sticky top-24">
        <div class="text-sm text-gray-500 mb-1">Starting from</div>
        <div class="font-display font-extrabold text-4xl text-brand-red mb-1"><?= format_price((float)($package['price'] ?? 0)) ?></div>
        <div class="text-xs text-gray-500 mb-5">per person (approx.)</div>
        <h3 class="font-display font-bold text-lg text-brand-navy mb-3">Book This Tour</h3>
        <form method="POST" action="<?= url('/tours/' . ($package['slug'] ?? '') . '/inquiry') ?>" class="space-y-3">
          <?= csrf_field() ?>
          <input type="hidden" name="service_type" value="tour">
          <input type="hidden" name="package_id" value="<?= (int)($package['id'] ?? 0) ?>">
          <div class="honeypot" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="website" tabindex="-1"></div>
          <?php partial('input', ['name'=>'journey_date','label'=>'Preferred Travel Date','type'=>'date','required'=>false]); ?>
          <?php partial('select', ['name'=>'passenger_count','label'=>'Number of Travelers','options'=>array_combine(range(1,20),range(1,20)),'required'=>true,'value'=>2]); ?>
          <hr class="border-gray-100">
          <?php partial('input', ['name'=>'customer_name','label'=>'Your Name','required'=>true]); ?>
          <?php partial('input', ['name'=>'customer_phone','label'=>'Phone','type'=>'tel','required'=>true,'placeholder'=>'01XXXXXXXXX']); ?>
          <?php partial('input', ['name'=>'customer_email','label'=>'Email (optional)','type'=>'email','required'=>false]); ?>
          <?php partial('textarea', ['name'=>'message','label'=>'Special Requests','rows'=>2]); ?>
          <button type="submit" class="w-full btn-accent py-3 rounded-lg font-semibold"><i class="fa-solid fa-paper-plane mr-2"></i> Send Booking Inquiry</button>
        </form>
        <?php if (setting('whatsapp')): ?>
          <a href="<?= whatsapp_url('Hello! I am interested in the tour package: ' . ($package['title'] ?? '')) ?>" target="_blank" rel="noopener" class="mt-3 w-full inline-flex items-center justify-center gap-2 bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600"><i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp</a>
        <?php endif; ?>
      </div>
      <?php if (!empty($related)): ?>
      <div class="mt-6">
        <h4 class="font-display font-bold text-lg text-brand-navy mb-3">Related Packages</h4>
        <div class="space-y-3">
          <?php foreach (array_slice($related,0,3) as $r): ?>
            <a href="<?= url('/tours/'.$r['slug']) ?>" class="flex gap-3 p-3 rounded-xl border border-gray-100 hover:bg-brand-light transition">
              <?php $rimg = $r['image'] ?? 'https://images.unsplash.com/photo-1530789253388-582c481c54b0?w=300'; if ($rimg && !str_starts_with($rimg,'http') && !str_starts_with($rimg,'/')) $rimg = asset($rimg); ?>
              <img src="<?= htmlspecialchars($rimg) ?>" alt="" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-brand-navy text-sm line-clamp-2"><?= htmlspecialchars($r['title']) ?></div>
                <div class="text-brand-red font-bold text-sm mt-1"><?= format_price((float)$r['price']) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
