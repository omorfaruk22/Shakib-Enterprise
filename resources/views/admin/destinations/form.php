<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl text-brand-navy"><?= $destination ? 'Edit' : 'Add' ?> Destination</h1><a href="<?= url('/admin/destinations') ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a></div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl"><form method="POST" action="<?= $destination ? url('/admin/destinations/'.$destination['id']) : url('/admin/destinations') ?>" enctype="multipart/form-data" class="space-y-4"><?= csrf_field() ?>
<?php partial('input', ['name'=>'name','label'=>'Destination Name','required'=>true,'value'=>$destination['name'] ?? '']); ?>
<?php partial('textarea', ['name'=>'description','label'=>'Description','rows'=>4,'value'=>$destination['description'] ?? '']); ?>
<?php partial('input', ['name'=>'image_url','label'=>'Image URL (or upload below)','value'=>$destination['image'] ?? '']); ?>
<div><label class="block text-sm font-semibold text-gray-700 mb-1.5">Upload Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm"></div>
<div class="grid md:grid-cols-3 gap-4"><?php partial('input', ['name'=>'sort_order','label'=>'Sort Order','type'=>'number','value'=>$destination['sort_order'] ?? 0]); ?>
<div class="flex items-center gap-2 mt-6"><input type="checkbox" name="is_featured" id="f" <?= !empty($destination['is_featured'])?'checked':'' ?> class="w-4 h-4"><label for="f" class="text-sm font-semibold">Featured</label></div>
<div class="flex items-center gap-2 mt-6"><input type="checkbox" name="is_active" id="a" <?= !$destination || !empty($destination['is_active'])?'checked':'' ?> class="w-4 h-4"><label for="a" class="text-sm font-semibold">Active</label></div></div>
<button class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm"><i class="fa-solid fa-save mr-1"></i> Save</button></form></div>
