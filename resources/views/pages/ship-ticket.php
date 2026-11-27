<?php $_activeNav = 'ship'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Ship Ticket']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-start">
    <div>
      <span class="inline-block bg-brand-light text-brand-navy font-semibold text-xs tracking-widest uppercase px-3 py-1 rounded-full mb-4">Ship / Launch Ticket</span>
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-4">Ship Ticket Booking Service</h1>
      <p class="text-gray-600 leading-relaxed mb-5">Book comfortable ship / launch tickets for popular river routes including Dhaka to Cox's Bazar / Barisal / Khulna and more. We offer cabin, deck and AC class tickets from all reputable operators.</p>
      <?php if (!empty($routes)): ?>
      <h3 class="font-display font-bold text-xl text-brand-navy mb-4 mt-6">Available Routes</h3>
      <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <?php foreach ($routes as $r): ?>
        <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <div class="font-semibold text-brand-navy"><?= htmlspecialchars($r['route_name']) ?></div>
            <?php if (!empty($r['is_active'])): ?><span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">Active</span><?php endif; ?>
          </div>
          <div class="text-sm text-gray-600 flex items-center gap-2"><i class="fa-solid fa-arrow-right-arrow-left text-brand-red"></i> <?= htmlspecialchars($r['from_location']) ?> <i class="fa-solid fa-arrow-right text-xs text-gray-400"></i> <?= htmlspecialchars($r['to_location']) ?></div>
          <?php if (!empty($r['duration'])): ?><div class="text-xs text-gray-500 mt-1"><i class="fa-regular fa-clock mr-1"></i> <?= htmlspecialchars($r['duration']) ?></div><?php endif; ?>
          <?php if (!empty($r['schedule'])): ?><div class="text-xs text-gray-500"><i class="fa-regular fa-calendar mr-1"></i> <?= htmlspecialchars($r['schedule']) ?></div><?php endif; ?>
          <?php if (!empty($r['price_note'])): ?><div class="text-xs text-gray-500"><i class="fa-solid fa-tag mr-1"></i> <?= htmlspecialchars($r['price_note']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="bg-brand-light rounded-xl p-5 text-sm text-gray-700">
        <i class="fa-solid fa-circle-info text-brand-navy mr-2"></i><strong>Note:</strong> Final ticket price depends on operator, class, and availability. Our agent will confirm all details after your inquiry.
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-brand p-6 md:p-8 border border-gray-100 sticky top-24">
      <h3 class="font-display font-bold text-xl text-brand-navy mb-1">Ship Ticket Inquiry</h3>
      <p class="text-sm text-gray-500 mb-5">Submit your details — we will call you back.</p>
      <form method="POST" action="<?= url('/ship-ticket/inquiry') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="service_type" value="ship">
        <div class="honeypot" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="website" tabindex="-1"></div>
        <?php
        $routeOpts = [];
        foreach ($routes as $r) $routeOpts[$r['route_name']] = $r['from_location'] . ' → ' . $r['to_location'] . ' (' . $r['route_name'] . ')';
        ?>
        <?php partial('select', ['name'=>'route_name','label'=>'Route','options'=>$routeOpts,'required'=>true]); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'departure','label'=>'From','placeholder'=>'Departure','required'=>true]); ?>
          <?php partial('input', ['name'=>'destination','label'=>'To','placeholder'=>'Destination','required'=>true]); ?>
        </div>
        <?php partial('input', ['name'=>'journey_date','label'=>'Journey Date','type'=>'date','required'=>true]); ?>
        <?php partial('select', ['name'=>'passenger_count','label'=>'Passengers','options'=>array_combine(range(1,10),range(1,10)),'required'=>true,'value'=>1]); ?>
        <hr class="border-gray-100">
        <?php partial('input', ['name'=>'customer_name','label'=>'Your Name','required'=>true]); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'customer_phone','label'=>'Phone','type'=>'tel','required'=>true,'placeholder'=>'01XXXXXXXXX']); ?>
          <?php partial('input', ['name'=>'customer_email','label'=>'Email (optional)','type'=>'email','required'=>false]); ?>
        </div>
        <?php partial('textarea', ['name'=>'message','label'=>'Notes (cabin class, etc.)','rows'=>3]); ?>
        <button type="submit" class="w-full btn-accent py-3 rounded-lg font-semibold shadow-brand"><i class="fa-solid fa-paper-plane mr-2"></i> Submit Inquiry</button>
      </form>
    </div>
  </div>
</section>
