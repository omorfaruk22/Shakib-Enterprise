<?php $_activeNav = 'air'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Air Ticket']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-start">
    <div>
      <span class="inline-block bg-brand-light text-brand-navy font-semibold text-xs tracking-widest uppercase px-3 py-1 rounded-full mb-4">Flight Booking</span>
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-4">Air Ticket Booking Service</h1>
      <p class="text-gray-600 leading-relaxed mb-5">Book domestic and international flights at competitive prices. We offer tickets for all major airlines operating in Bangladesh and abroad. Submit your inquiry and our agent will contact you with the best available fares and options.</p>
      <ul class="space-y-3 text-gray-700 mb-6">
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Domestic flights (Dhaka, Chittagong, Cox's Bazar, Sylhet, Saidpur, Jessore, Rajshahi, Barisal)</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> International flights (India, UAE, Saudi Arabia, Malaysia, Singapore, Thailand, Turkey, etc.)</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> One-way and round-trip options</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Economy, Business and First Class booking</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Fast confirmation and e-ticket delivery</li>
      </ul>
      <div class="bg-brand-light rounded-xl p-5">
        <div class="text-sm text-gray-700 mb-2"><i class="fa-solid fa-circle-info text-brand-navy mr-2"></i><strong>Note:</strong> This is an inquiry form. Final fare, availability and confirmation are provided after our agent contacts you.</div>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-brand p-6 md:p-8 border border-gray-100 sticky top-24">
      <h3 class="font-display font-bold text-xl text-brand-navy mb-1">Air Ticket Inquiry</h3>
      <p class="text-sm text-gray-500 mb-5">Fill in your details — we will call you back.</p>
      <form method="POST" action="<?= url('/air-ticket/inquiry') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="service_type" value="air">
        <div class="honeypot" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <?php partial('select', ['name'=>'trip_type','label'=>'Trip Type','options'=>['oneway'=>'One Way','round'=>'Round Trip'],'value'=>'oneway','required'=>true,'id'=>'trip_type']); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'departure','label'=>'From (Departure)','placeholder'=>'e.g. Dhaka','required'=>true]); ?>
          <?php partial('input', ['name'=>'destination','label'=>'To (Destination)','placeholder'=>'e.g. Cox\'s Bazar','required'=>true]); ?>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'journey_date','label'=>'Journey Date','type'=>'date','required'=>true]); ?>
          <div id="return_date_wrap">
            <?php partial('input', ['name'=>'return_date','label'=>'Return Date (optional)','type'=>'date','required'=>false]); ?>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('select', ['name'=>'passenger_count','label'=>'Passengers','options'=>array_combine(range(1,9),range(1,9)),'required'=>true,'value'=>1]); ?>
          <?php partial('select', ['name'=>'travel_class','label'=>'Travel Class','options'=>['economy'=>'Economy','business'=>'Business','first'=>'First Class'],'required'=>false]); ?>
        </div>
        <hr class="border-gray-100">
        <?php partial('input', ['name'=>'customer_name','label'=>'Your Name','placeholder'=>'Full name','required'=>true]); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'customer_phone','label'=>'Phone Number','type'=>'tel','placeholder'=>'01XXXXXXXXX','required'=>true]); ?>
          <?php partial('input', ['name'=>'customer_email','label'=>'Email (optional)','type'=>'email','placeholder'=>'you@example.com','required'=>false]); ?>
        </div>
        <?php partial('textarea', ['name'=>'message','label'=>'Additional Notes (optional)','rows'=>3,'placeholder'=>'Any special request?']); ?>
        <button type="submit" class="w-full btn-accent py-3 rounded-lg font-semibold shadow-brand"><i class="fa-solid fa-paper-plane mr-2"></i> Submit Inquiry</button>
      </form>
    </div>
  </div>
</section>
