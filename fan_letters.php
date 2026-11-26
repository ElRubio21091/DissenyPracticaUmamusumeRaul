<?php
/**
 * Autor: Raul Rubio - Pt04
 * fan_letters.php
 * Página para mostrar y enviar cartas de fan.
 * De momento es un marcador "Coming soon": la sección aún no está hecha.
 */
require __DIR__ . '/includes/bootstrap.php';   // helpers + sesión (para que el header sepa si estás logueado)

$title = 'Fan letters · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';        // <html>, <head>, Tailwind y mensajes flash
include __DIR__ . '/includes/header.php';      // cabecera con el menú
?>

<main id="main" tabindex="-1" class="flex flex-1 flex-col items-center justify-center px-6 py-16 text-center">
    <h1 class="text-3xl font-bold text-brand-pink">Fan letters</h1>
    <p class="mt-4 text-lg font-bold">Coming soon!</p>
    <p class="mt-2 max-w-md text-sm">
        We are still working on this section. Soon you will be able to read and send letters to your favourite characters.
    </p>
    <a href="index.php"
       class="mt-8 rounded-full bg-brand-pink px-6 py-2 text-sm font-bold text-white hover:brightness-110">
        Back to home
    </a>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>