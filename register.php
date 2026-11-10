<?php
/**
 * Autor: Raul Rubio - Pt04
 * register.php
 * Formulario de registro (la lógica está en controlador/auth.php).
 * Si hay errores se conservan usuario y email; las contraseñas nunca se devuelven al navegador.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/auth.php';

exigirAnonimo();
$estado  = procesarRegistro();
$errores = $estado['errores'];
$datos   = $estado['datos'];

$title = 'Register · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';

$inputClass = 'w-full rounded-full border border-black bg-white px-5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink';
?>

<main class="grid flex-1 lg:grid-cols-[35%_1fr]">
    <section class="flex flex-col items-center px-8 py-10">
        <a href="index.php" aria-label="Inicio"><?php $logoClass = 'h-20'; include __DIR__ . '/includes/logo.php'; ?></a>

        <h1 class="mt-10 text-xl font-bold">Welcome new trainer!</h1>

        <form action="register.php" method="post" class="mt-6 w-full max-w-[250px] space-y-4" novalidate>
            <?= campoCsrf() ?>

            <?php if (!empty($errores['general'])): ?>
                <p class="rounded-2xl border border-red-600 bg-red-100 px-4 py-2 text-xs font-bold text-red-800" role="alert"><?= e($errores['general']) ?></p>
            <?php endif; ?>

            <div>
                <label for="username" class="sr-only">Usuario</label>
                <input id="username" name="username" type="text" placeholder="Username" maxlength="30"
                       value="<?= e($datos['username']) ?>" autocomplete="username" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'username') ?>
            </div>
            <div>
                <label for="email" class="sr-only">Email</label>
                <input id="email" name="email" type="email" placeholder="Email" maxlength="100"
                       value="<?= e($datos['email']) ?>" autocomplete="email" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'email') ?>
            </div>
            <div>
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="Password"
                       autocomplete="new-password" class="<?= $inputClass ?>">
                <p class="mt-1 px-3 text-[11px] text-gray-600">Mín. 8 caracteres, con mayúscula, minúscula y número.</p>
                <?= errorCampo($errores, 'password') ?>
            </div>
            <div>
                <label for="password_confirm" class="sr-only">Repetir contraseña</label>
                <input id="password_confirm" name="password_confirm" type="password" placeholder="Repeat password"
                       autocomplete="new-password" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'password_confirm') ?>
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
