<?php $base = $type === 'holiday' ? '/admin/holiday-packages' : '/admin/tour-packages'; ?>
<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl text-brand-navy"><?= $pkg ? 'Edit' : 'Add' ?> <?= ucfirst($type) ?> Package</h1><a href="<?= url($base) ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a></div>

<?php if (empty($destinations)): ?>
<div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg mb-6 flex items-start gap-3">
  <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
  <div>
    <p class="text-sm text-amber-800 font-semibold">No Destinations yet</p>
    <p class="text-xs text-amber-700 mt-0.5">You must add at least one Destination before creating packages.</p>
    <a href="<?= url('/admin/destinations/create') ?>" class="inline-block mt-2 text-xs bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded font-semibold"><i class="fa-solid fa-plus mr-1"></i> Add Destination First</a>
  </div>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-3xl"><form method="POST" action="<?= $pkg ? url($base.'/'.$pkg['id']) : url($base) ?>" enctype="multipart/form-data" class="space-y-4"><?= csrf_field() ?>
<div class="grid md:grid-cols-2 gap-4"><?php $destOpts = []; foreach ($destinations as $d) $destOpts[$d['id']] = $d['name']; partial('select', ['name'=>'destination_id','label'=>'Destination','required'=>true,'options'=>$destOpts,'value'=>$pkg['destination_id'] ?? '','placeholder'=>'-- Select Destination --']); partial('input', ['name'=>'title','label'=>'Package Title','required'=>true,'value'=>$pkg['title'] ?? '']); ?>
<?php partial('input', ['name'=>'price','label'=>'Price (BDT)','type'=>'number','required'=>true,'value'=>$pkg['price'] ?? 0]); partial('input', ['name'=>'image_url','label'=>'Image URL (or upload below)','value'=>$pkg['image'] ?? '']); ?>
<?php partial('input', ['name'=>'duration_days','label'=>'Days','type'=>'number','value'=>$pkg['duration_days'] ?? 3]); partial('input', ['name'=>'duration_nights','label'=>'Nights','type'=>'number','value'=>$pkg['duration_nights'] ?? 2]); ?></div>
<?php partial('input', ['name'=>'short_description','label'=>'Short Description','value'=>$pkg['short_description'] ?? '']); ?>
<?php partial('textarea', ['name'=>'description','label'=>'Description','rows'=>5,'value'=>$pkg['description'] ?? '']); ?>
<?php partial('textarea', ['name'=>'itinerary','label'=>'Itinerary','rows'=>5,'value'=>$pkg['itinerary'] ?? '']); ?>
<div class="grid md:grid-cols-2 gap-4"><?php partial('textarea', ['name'=>'inclusions','label'=>'Inclusions','rows'=>4,'value'=>$pkg['inclusions'] ?? '']); partial('textarea', ['name'=>'exclusions','label'=>'Exclusions','rows'=>4,'value'=>$pkg['exclusions'] ?? '']); ?></div>
<div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Upload Image (overrides URL)</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm"></div>
<div class="grid md:grid-cols-3 gap-4"><?php partial('input', ['name'=>'sort_order','label'=>'Sort Order','type'=>'number','value'=>$pkg['sort_order'] ?? 0]); ?>
<div class="flex items-center gap-2 mt-6"><input type="checkbox" name="is_featured" id="f" <?= !empty($pkg['is_featured']) ? 'checked' : '' ?> class="w-4 h-4"><label for="f" class="text-sm font-semibold">Featured</label></div>
<div class="flex items-center gap-2 mt-6"><input type="checkbox" name="is_active" id="a" <?= !$pkg || !empty($pkg['is_active']) ? 'checked' : '' ?> class="w-4 h-4"><label for="a" class="text-sm font-semibold">Active</label></div></div>
<button type="submit" class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm" <?= empty($destinations) ? 'disabled style="opacity:0.5;cursor:not-allowed"' : '' ?>><i class="fa-solid fa-save mr-1"></i> Save Package</button></form></div>
