<?php $_activeNav = 'bus'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Bus Ticket']]]); ?>
<section class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-10 items-start">
    <div>
      <span class="inline-block bg-brand-light text-brand-navy font-semibold text-xs tracking-widest uppercase px-3 py-1 rounded-full mb-4">Bus Ticket Booking</span>
      <h1 class="font-display font-extrabold text-3xl md:text-4xl text-brand-navy mb-4">Bus Ticket Booking Service</h1>
      <p class="text-gray-600 leading-relaxed mb-5">Book bus tickets for all major routes across Bangladesh from trusted operators. We offer AC, Non-AC, and VIP coach tickets at the best available prices.</p>
      <ul class="space-y-3 text-gray-700 mb-6">
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Dhaka → Cox's Bazar, Chittagong, Sylhet, Rajshahi, Khulna, Barisal and more</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> AC / Non-AC / VIP / Sleeper coaches</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Seat selection assistance</li>
        <li class="flex gap-3"><i class="fa-solid fa-check-circle text-brand-red mt-1"></i> Same-day and advance booking</li>
      </ul>
      <div class="bg-brand-light rounded-xl p-5 text-sm text-gray-700">
        <i class="fa-solid fa-circle-info text-brand-navy mr-2"></i> Our agent will share available operators, times and fares with you after receiving your inquiry.
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-brand p-6 md:p-8 border border-gray-100 sticky top-24">
      <h3 class="font-display font-bold text-xl text-brand-navy mb-1">Bus Ticket Inquiry</h3>
      <p class="text-sm text-gray-500 mb-5">Submit your details — we will call you back.</p>
      <form method="POST" action="<?= url('/bus-ticket/inquiry') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="service_type" value="bus">
        <div class="honeypot" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="website" tabindex="-1"></div>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'departure','label'=>'From','placeholder'=>'e.g. Dhaka','required'=>true]); ?>
          <?php partial('input', ['name'=>'destination','label'=>'To','placeholder'=>'e.g. Cox\'s Bazar','required'=>true]); ?>
        </div>
        <?php partial('input', ['name'=>'journey_date','label'=>'Journey Date','type'=>'date','required'=>true]); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('select', ['name'=>'passenger_count','label'=>'Passengers','options'=>array_combine(range(1,10),range(1,10)),'required'=>true,'value'=>1]); ?>
          <?php partial('select', ['name'=>'bus_type','label'=>'Coach Type','options'=>[''=>'Any','ac'=>'AC','nonac'=>'Non-AC','vip'=>'VIP','sleeper'=>'Sleeper'],'required'=>false]); ?>
        </div>
        <hr class="border-gray-100">
        <?php partial('input', ['name'=>'customer_name','label'=>'Your Name','required'=>true]); ?>
        <div class="grid grid-cols-2 gap-3">
          <?php partial('input', ['name'=>'customer_phone','label'=>'Phone','type'=>'tel','required'=>true,'placeholder'=>'01XXXXXXXXX']); ?>
          <?php partial('input', ['name'=>'customer_email','label'=>'Email (optional)','type'=>'email','required'=>false]); ?>
        </div>
        <?php partial('textarea', ['name'=>'message','label'=>'Notes','rows'=>3]); ?>
        <button type="submit" class="w-full btn-accent py-3 rounded-lg font-semibold shadow-brand"><i class="fa-solid fa-paper-plane mr-2"></i> Submit Inquiry</button>
      </form>
    </div>
  </div>
</section>
