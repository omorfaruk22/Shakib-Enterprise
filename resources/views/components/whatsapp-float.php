<?php $wa = setting('whatsapp'); if ($wa): ?>
<a href="<?= whatsapp_url('Hello! I want to know more about your travel services.') ?>" target="_blank" rel="noopener" class="whatsapp-float" aria-label="WhatsApp">
  <i class="fa-brands fa-whatsapp text-2xl"></i>
</a>
<?php endif; ?>
