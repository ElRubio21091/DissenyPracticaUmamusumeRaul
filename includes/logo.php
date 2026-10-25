<?php if (asset_exists('assets/img/logo.png')): ?>
    <img src="assets/img/logo.png" alt="Umamusume: Pretty Derby" class="<?= $logoClass ?? 'h-14' ?> w-auto">
<?php else: ?>
    <span class="text-3xl font-extrabold italic text-brand-pink">Umamusume <span class="block text-lg">Pretty Derby</span></span>
<?php endif; ?>
