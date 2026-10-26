<header class="relative z-20 bg-brand-blue">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-2 px-4 py-2 sm:h-14 sm:flex-row sm:justify-end sm:py-0">
        <!-- El logo sobresale por debajo de la barra azul en pantallas sm o mayores -->
        <a href="index.php" aria-label="Inicio" class="block sm:absolute sm:left-1 sm:top-0">
            <?php $logoClass = 'h-auto !w-60 sm:!w-[clamp(260px,31.9vw,720px)]'; include __DIR__ . '/logo.php'; ?>
        </a>

        <nav class="flex flex-wrap items-center justify-center gap-3 text-sm font-bold text-white">
            <a href="#" class="rounded-full border border-black bg-brand-pink px-6 py-2 hover:brightness-110">Fan letters</a>
            <a href="index.php" class="rounded-full border border-black bg-brand-pink px-8 py-2 hover:brightness-110">Home</a>
            <a href="login.php" class="rounded-full border border-black bg-brand-pink px-6 py-2 hover:brightness-110">Login/Register</a>
        </nav>
    </div>
</header>
