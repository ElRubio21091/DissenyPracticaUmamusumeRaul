<div class="relative hidden lg:block">
    <?php if (asset_exists('assets/img/hero.jpg')): ?>
        <img src="assets/img/hero.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <?php else: ?>
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 via-green-500 to-emerald-700" aria-hidden="true"></div>
    <?php endif; ?>
</div>
