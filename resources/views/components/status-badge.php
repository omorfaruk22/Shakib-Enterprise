<?php $s = $status ?? 'Pending'; ?>
<span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold status-<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></span>
