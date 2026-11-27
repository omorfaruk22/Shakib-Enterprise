<?php $_activeNav = 'tours'; ?>
<?php partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'Tour & Travel']]]); ?>
<section class="py-12 md:py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid md:grid-cols-[1fr_auto] gap-4 items-end mb-8">
      <div>
        <h2 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Tour & Travel Packages</h2>
        <p class="text-gray-600 mt-1">Browse our curated tour packages across Bangladesh's top destinations.</p>
      </div>
      <form method="get" class="flex gap-2">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search tours..." class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-navy/20 focus:border-brand-navy">
        <button class="btn-primary px-4 rounded-lg font-semibold"><i class="fa-solid fa-search"></i></button>
      </form>
    </div>
    <?php if (empty($packages)): ?>
      <div class="text-center py-16 text-gray-500"><i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i><p>No tour packages available yet. Please check back soon.</p></div>
    <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($packages as $p): ?>
        <?php partial('package-card', ['package' => $p, 'type' => 'tour']); ?>
      <?php endforeach; ?>
    </div>
    <?php partial('pagination', ['page'=>$page ?? 1,'pages'=>$pages ?? 1]); ?>
    <?php endif; ?>
  </div>
</section>
