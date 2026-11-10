<?php
/**
 * Autor: Raul Rubio - Pt04
 * personaje.php
 * Detalle público de un personaje (nombre, VA, descripción y autor).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/personajes.php';

$p = personajeOHome((int) ($_GET['id'] ?? 0));
$esAutor = esAutor($p);

$title = $p['titol'] . ' · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main class="mx-auto w-full max-w-4xl flex-1 px-4 pb-10 pt-10 sm:pt-16">
    <div class="grid gap-8 md:grid-cols-[240px_1fr]">
        <div class="mx-auto w-full max-w-[240px]">
            <div class="aspect-[3/4] overflow-hidden border border-black bg-white">
                <?php if (asset_exists($p['imatge'])): ?>
                    <img src="<?= e($p['imatge']) ?>" alt="<?= e($p['titol']) ?>" class="h-full w-full object-cover object-top">
                <?php else: ?>
                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-b from-pink-100 to-purple-200 text-7xl font-bold text-brand-pink"
                         role="img" aria-label="<?= e($p['titol']) ?>"><?= e(mb_substr($p['titol'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="border border-t-0 border-black bg-brand-pink px-2 py-1 text-center text-sm font-bold text-white">VA: <?= e($p['va']) ?></div>
        </div>

        <article>
            <h1 class="text-3xl font-bold"><?= e($p['titol']) ?></h1>
            <p class="mt-1 text-xs text-gray-600">Creado por <strong><?= e($p['autor']) ?></strong></p>
            <!-- nl2br respeta los saltos de línea; e() evita XSS -->
            <p class="mt-6 leading-relaxed"><?= nl2br(e($p['cos'])) ?></p>

            <div class="mt-8 flex flex-wrap items-center gap-3 text-sm font-bold">
                <a href="index.php" class="rounded-full border border-black bg-white px-5 py-1.5 hover:bg-brand-pink/20">&laquo; Back</a>
                <?php if ($esAutor): ?>
                    <a href="personaje_form.php?id=<?= (int) $p['id'] ?>" class="rounded-full border border-black bg-brand-pink px-5 py-1.5 text-white hover:brightness-110">Edit</a>
                    <form action="personaje_borrar.php" method="post" onsubmit="return confirm('¿Seguro que quieres borrar este personaje?');">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="rounded-full border border-red-600 bg-white px-5 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
