<?php
require __DIR__ . '/includes/helpers.php';

$title = 'Register · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';

$inputClass = 'w-full rounded-full border border-black bg-white px-5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink';
?>

<main class="grid flex-1 lg:grid-cols-[35%_1fr]">
    <section class="flex flex-col items-center px-8 py-10">
        <a href="index.php" aria-label="Inicio"><?php $logoClass = 'h-20'; include __DIR__ . '/includes/logo.php'; ?></a>

        <h1 class="mt-10 text-xl font-bold">Welcome new trainer!</h1>

        <!-- Solo maqueta: el botón lleva al Home, no valida nada -->
        <form action="index.php" class="mt-6 w-full max-w-[250px] space-y-4">
            <div>
                <label for="username" class="sr-only">Usuario</label>
                <input id="username" type="text" placeholder="Username" class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="email" class="sr-only">Email</label>
                <input id="email" type="email" placeholder="Email" class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" type="password" placeholder="Password" class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="password_confirm" class="sr-only">Repetir contraseña</label>
                <input id="password_confirm" type="password" placeholder="Repeat password" class="<?= $inputClass ?>">
            </div>

            <?php include __DIR__ . '/includes/social.php'; ?>

            <div class="flex items-center justify-between pt-1">
                <button type="submit" class="rounded-full bg-brand-pink px-6 py-2 text-sm font-bold text-white hover:brightness-110">Register</button>
                <a href="login.php" class="text-xs font-bold text-brand-pink hover:underline">Login</a>
            </div>
        </form>
    </section>

    <?php include __DIR__ . '/includes/auth_hero.php'; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
