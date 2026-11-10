<?php
/**
 * Autor: Raul Rubio - Pt04
 * header.php
 * Barra superior. Cambia según haya sesión iniciada o no.
 */
?>
<header class="relative z-20 bg-brand-blue">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-2 px-4 py-2 sm:min-h-14 sm:flex-row sm:justify-end sm:py-2">
        <!-- El logo sobresale por debajo de la barra azul en pantallas sm o mayores -->
        <a href="index.php" aria-label="Inicio" class="block sm:absolute sm:left-1 sm:top-0">
            <?php $logoClass = 'h-auto !w-60 sm:!w-[clamp(260px,31.9vw,720px)]'; include __DIR__ . '/logo.php'; ?>
        </a>

        <nav class="flex flex-wrap items-center justify-center gap-3 text-sm font-bold text-white">
            <a href="#" class="rounded-full border border-black bg-brand-pink px-6 py-2 hover:brightness-110">Fan letters</a>
            <a href="index.php" class="rounded-full border border-black bg-brand-pink px-8 py-2 hover:brightness-110">Home</a>

            <?php if (estaLogueado()): ?>
                <a href="personaje_form.php" class="rounded-full border border-black bg-brand-pink px-6 py-2 hover:brightness-110">New character</a>
                <span class="px-2" title="Sesión iniciada"><?= e(usuarioNombre()) ?></span>
                <!-- Logout por POST + CSRF: un simple enlace permitiría cerrar sesión desde otra web -->
                <form action="logout.php" method="post">
                    <?= campoCsrf() ?>
                    <button type="submit" class="rounded-full border border-black bg-brand-pink px-6 py-2 font-bold hover:brightness-110">Logout</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="rounded-full border border-black bg-brand-pink px-6 py-2 hover:brightness-110">Login/Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
