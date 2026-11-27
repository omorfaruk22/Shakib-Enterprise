<h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy mb-6">Website Settings</h1>
<p class="text-gray-600 mb-6 text-sm"><i class="fa-solid fa-circle-info text-brand-navy mr-1"></i> সবকিছু এই পেজ থেকে পরিবর্তন করা যাবে — কোডে হাত দিতে হবে না। সেভ করার সাথে সাথে ওয়েবসাইটে আপডেট হয়ে যাবে।</p>
<form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data" class="space-y-6"><?= csrf_field() ?>

<!-- TABS (simple) -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-0 z-10 flex flex-wrap gap-2">
  <a href="#tab-business" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold bg-brand-light text-brand-navy">Business</a>
  <a href="#tab-contact" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-100">Contact</a>
  <a href="#tab-home" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-100">Homepage</a>
  <a href="#tab-seo" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-100">SEO</a>
  <a href="#tab-about" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-100">About</a>
  <a href="#tab-advanced" class="tab-link px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-100">Advanced</a>
</div>

<div id="tab-business" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h3 class="font-display font-bold text-lg text-brand-navy mb-4">🏢 Business Information</h3>
  <div class="grid md:grid-cols-2 gap-4">
    <?php partial('input', ['name'=>'business_name','label'=>'Business Name','required'=>true,'value'=>$settings['business_name'] ?? 'SAKIB ENTERPRISE']); ?>
    <?php partial('input', ['name'=>'tagline','label'=>'Tagline','value'=>$settings['tagline'] ?? 'Your Trusted Travel Partner']); ?>
    <?php partial('textarea', ['name'=>'footer_text','label'=>'Footer Text','rows'=>2,'value'=>$settings['footer_text'] ?? '']); ?>
  </div>
</div>

<div id="tab-contact" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h3 class="font-display font-bold text-lg text-brand-navy mb-4">📞 Contact Information</h3>
  <div class="grid md:grid-cols-2 gap-4">
    <?php partial('input', ['name'=>'phone','label'=>'Phone Number','value'=>$settings['phone'] ?? '']); ?>
    <?php partial('input', ['name'=>'whatsapp','label'=>'WhatsApp Number (digits only, with country code, e.g. 8801700000000)','value'=>$settings['whatsapp'] ?? '']); ?>
    <?php partial('input', ['name'=>'email','label'=>'Email','type'=>'email','value'=>$settings['email'] ?? '']); ?>
    <?php partial('input', ['name'=>'address','label'=>'Office Address','value'=>$settings['address'] ?? '']); ?>
    <?php partial('input', ['name'=>'opening_hours','label'=>'Opening Hours','value'=>$settings['opening_hours'] ?? 'Sat–Thu: 9:00 AM – 9:00 PM']); ?>
    <?php partial('input', ['name'=>'maps_embed','label'=>'Google Maps Embed URL (iframe src)','value'=>$settings['maps_embed'] ?? '']); ?>
  </div>
  <h4 class="font-bold text-brand-navy mt-6 mb-3">Social Media Links</h4>
  <div class="grid md:grid-cols-4 gap-4">
    <?php partial('input', ['name'=>'facebook','label'=>'Facebook URL','value'=>$settings['facebook'] ?? '']); ?>
    <?php partial('input', ['name'=>'youtube','label'=>'YouTube URL','value'=>$settings['youtube'] ?? '']); ?>
    <?php partial('input', ['name'=>'instagram','label'=>'Instagram URL','value'=>$settings['instagram'] ?? '']); ?>
    <?php partial('input', ['name'=>'linkedin','label'=>'LinkedIn URL','value'=>$settings['linkedin'] ?? '']); ?>
  </div>
</div>

<div id="tab-home" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
  <h3 class="font-display font-bold text-lg text-brand-navy">🏠 Homepage Content</h3>

  <div class="border rounded-xl p-5 bg-gray-50">
    <h4 class="font-bold text-brand-navy mb-3">Hero Section</h4>
    <?php partial('input', ['name'=>'hero_title','label'=>'Hero Title','value'=>$settings['hero_title'] ?? 'Explore the World with SAKIB ENTERPRISE']); ?>
    <?php partial('textarea', ['name'=>'hero_description','label'=>'Hero Description','rows'=>3,'value'=>$settings['hero_description'] ?? '']); ?>
    <?php partial('input', ['name'=>'hero_cta_text','label'=>'Hero CTA Button Text','value'=>$settings['hero_cta_text'] ?? 'Explore Tours']); ?>
  </div>

  <div class="border rounded-xl p-5 bg-gray-50">
    <h4 class="font-bold text-brand-navy mb-3">Section Titles (সব সেকশনের টাইটেল ও সাবটাইটেল)</h4>
    <?php
    $sections = [
      'homepage_services_title' => 'Services Title',
      'homepage_services_subtitle' => 'Services Subtitle',
      'homepage_destinations_title' => 'Destinations Title',
      'homepage_destinations_subtitle' => 'Destinations Subtitle',
      'homepage_tours_title' => 'Tours Title',
      'homepage_tours_subtitle' => 'Tours Subtitle',
      'homepage_holidays_title' => 'Holidays Title',
      'homepage_holidays_subtitle' => 'Holidays Subtitle',
      'homepage_whyus_title' => 'Why Choose Us Title',
      'homepage_whyus_subtitle' => 'Why Choose Us Subtitle',
      'homepage_process_title' => 'Process Title',
      'homepage_process_subtitle' => 'Process Subtitle',
      'homepage_testimonials_title' => 'Testimonials Title',
      'homepage_testimonials_subtitle' => 'Testimonials Subtitle',
      'homepage_cta_title' => 'Final CTA Title',
      'homepage_cta_text' => 'Final CTA Subtitle',
    ];
    echo '<div class="grid md:grid-cols-2 gap-3">';
    foreach ($sections as $key => $label) {
      if (str_ends_with($key, '_subtitle')) {
        partial('textarea', ['name'=>$key,'label'=>$label,'rows'=>2,'value'=>$settings[$key] ?? '']);
      } else {
        partial('input', ['name'=>$key,'label'=>$label,'value'=>$settings[$key] ?? '']);
      }
    }
    echo '</div>';
    ?>
  </div>

  <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
    <i class="fa-solid fa-lightbulb mr-2"></i> <strong>Services, Destinations, Tour Packages, Holiday Packages, Ship Routes, Testimonials</strong> — এইগুলো বাম সাইডবারের আলাদা মেনু থেকে Add/Edit/Delete করবেন। Settings থেকে নয়।
  </div>
</div>

<div id="tab-seo" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h3 class="font-display font-bold text-lg text-brand-navy mb-4">🔍 SEO (Search Engine Optimization)</h3>
  <div class="grid md:grid-cols-2 gap-4">
    <?php partial('input', ['name'=>'meta_title','label'=>'Default Meta Title','value'=>$settings['meta_title'] ?? '']); ?>
    <?php partial('textarea', ['name'=>'meta_description','label'=>'Default Meta Description','rows'=>2,'value'=>$settings['meta_description'] ?? '']); ?>
  </div>
</div>

<div id="tab-about" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
  <h3 class="font-display font-bold text-lg text-brand-navy mb-2">ℹ️ About Us Page</h3>
  <?php partial('input', ['name'=>'about_page_title','label'=>'About Page Title','value'=>$aboutPage['title'] ?? 'About Us']); ?>
  <?php partial('textarea', ['name'=>'about_page_content','label'=>'About Page Content (পুরো কোম্পানি বিবরণ)','rows'=>10,'value'=>$aboutPage['content'] ?? '']); ?>
  <div class="grid md:grid-cols-2 gap-4">
    <?php partial('textarea', ['name'=>'mission','label'=>'Our Mission','rows'=>3,'value'=>$settings['mission'] ?? '']); ?>
    <?php partial('textarea', ['name'=>'vision','label'=>'Our Vision','rows'=>3,'value'=>$settings['vision'] ?? '']); ?>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <?php partial('input', ['name'=>'about_page_meta_title','label'=>'About Meta Title','value'=>$aboutPage['meta_title'] ?? '']); ?>
    <?php partial('textarea', ['name'=>'about_page_meta_description','label'=>'About Meta Description','rows'=>2,'value'=>$aboutPage['meta_description'] ?? '']); ?>
  </div>
</div>

<div id="tab-advanced" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h3 class="font-display font-bold text-lg text-brand-navy mb-4">⚙️ Advanced Settings</h3>
  <div class="space-y-3">
    <label class="flex items-center gap-2"><input type="checkbox" name="floating_whatsapp_enabled" value="1" <?= ($settings['floating_whatsapp_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm font-semibold">Floating WhatsApp button দেখান</span></label>
    <label class="flex items-center gap-2"><input type="checkbox" name="customer_login_enabled" value="1" <?= ($settings['customer_login_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="w-4 h-4"> <span class="text-sm font-semibold">Customer Login/Register চালু রাখুন</span></label>
  </div>
</div>

<button class="btn-primary px-8 py-3 rounded-lg font-semibold shadow-brand sticky bottom-4"><i class="fa-solid fa-save mr-2"></i> Save All Settings</button>
</form>

<script>
// Simple tab highlighting
document.querySelectorAll('.tab-link').forEach(btn => {
  btn.addEventListener('click', e => {
    document.querySelectorAll('.tab-link').forEach(b => { b.classList.remove('bg-brand-light','text-brand-navy'); b.classList.add('hover:bg-gray-100'); });
    btn.classList.add('bg-brand-light','text-brand-navy');
  });
});
</script>
