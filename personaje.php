<?php
/**
 * Autor: Raul Rubio - Pt04
 * personaje.php
 * Detalle público de un personaje: carrusel de outfits, VA, frase, información general,
 * descripción y, solo para el autor, los botones de editar y borrar.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/personajes.php';

$p            = personajeOHome((int) ($_GET['id'] ?? 0));
$esAutor      = esAutor($p);
$diapositivas = diapositivasPersonaje($p);

// Datos de "General Info": solo se pintan los que tienen valor
$info = [];
if (!empty($p['anniversary'])) {
    $info['Birthday'] = $p['anniversary'];
}
if (!empty($p['height'])) {
    $info['Height'] = (int) $p['height'] . ' cm';
}
if (!empty($p['weight'])) {
    $info['Weight'] = $p['weight'];
}

$title = $p['titol'] . ' · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main id="main" tabindex="-1" class="grid w-full flex-1 md:grid-cols-[minmax(18rem,35%)_1fr]">

    <!-- Columna izquierda: carrusel de outfits -->
    <section id="outfits" aria-roledescription="carousel" aria-label="Outfits of <?= e($p['titol']) ?>"
             class="border-b border-brand-blue/50 px-6 pb-10 pt-10 md:border-b-0 md:border-r md:pt-28">

        <?php if ($diapositivas): ?>
            <?php foreach ($diapositivas as $i => $d): ?>
                <figure data-slide data-name="<?= e($d['nom'] ?? $p['titol']) ?>" class="mx-auto w-full max-w-[16rem]">
                    <div class="aspect-[3/4] overflow-hidden border border-black bg-white">
                        <img src="<?= e($d['imatge']) ?>" alt="<?= e($p['titol']) ?>" class="h-full w-full object-contain object-bottom">
                    </div>
                    <?php if ($d['nom']): ?>
                        <figcaption class="mt-4 text-center text-base font-bold"><?= e($d['nom']) ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>

            <?php if (count($diapositivas) > 1): ?>
                <!-- Los controles están ocultos hasta que carga el JS: sin JS se ven todos los outfits en columna -->
                <div id="outfit-controls" hidden>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <button type="button" data-prev aria-label="Previous outfit"
                                class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-brand-pink bg-white text-brand-pink hover:bg-brand-pink hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                        </button>

                        <ul class="flex items-center">
                            <?php foreach ($diapositivas as $i => $d): ?>
                                <li>
                                    <!-- Botón de 24x24 px (tamaño mínimo WCAG 2.5.8) con un punto visual más pequeño dentro -->
                                    <button type="button" data-dot class="group flex h-6 w-6 items-center justify-center rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-black"
                                            aria-label="Show outfit <?= $i + 1 ?> of <?= count($diapositivas) ?><?= $d['nom'] ? ': ' . e($d['nom']) : '' ?>">
                                        <span class="block h-3.5 w-3.5 rounded-full border-2 border-brand-pink bg-white group-aria-[current=true]:bg-brand-pink" aria-hidden="true"></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <button type="button" data-next aria-label="Next outfit"
                                class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-brand-pink bg-white text-brand-pink hover:bg-brand-pink hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
                <!-- Anuncia el cambio de outfit a los lectores de pantalla -->
                <p id="outfit-status" class="sr-only" role="status" aria-live="polite"></p>
            <?php endif; ?>

        <?php else: ?>
            <div class="mx-auto flex aspect-[3/4] w-full max-w-[16rem] items-center justify-center border border-black bg-gradient-to-b from-pink-100 to-purple-200 text-7xl font-bold text-brand-pink"
                 role="img" aria-label="<?= e($p['titol']) ?>"><?= e(mb_substr($p['titol'], 0, 1)) ?></div>
        <?php endif; ?>
    </section>

    <!-- Columna derecha: datos del personaje -->
    <article class="px-6 pb-12 pt-8 md:px-12 md:pt-28">
        <h1 class="text-2xl font-bold text-brand-pink sm:text-3xl"><?= e($p['titol']) ?></h1>
        <p class="mt-3 text-lg font-bold">VA: <?= e($p['va']) ?></p>

        <?php if (!empty($p['frase'])): ?>
            <p class="mt-5 max-w-md text-xl leading-snug"><?= e($p['frase']) ?></p>
        <?php endif; ?>

        <?php if ($info): ?>
            <section aria-labelledby="general-info" class="mt-6 border-t border-brand-blue/50 pt-4">
                <h2 id="general-info" class="text-xl font-bold text-brand-pink">General Info</h2>
                <dl class="mt-3 space-y-3">
                    <?php foreach ($info as $etiqueta => $valor): ?>
                        <div class="flex flex-wrap gap-x-2">
                            <dt><?= e($etiqueta) ?>:</dt>
                            <dd><?= e($valor) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>
        <?php endif; ?>

        <section aria-labelledby="description" class="mt-6 border-t border-brand-blue/50 pt-4">
            <h2 id="description" class="text-xl font-bold text-brand-pink">Description</h2>
            <!-- nl2br respeta los saltos de línea; e() evita XSS -->
            <p class="mt-3 max-w-2xl text-sm leading-relaxed sm:text-base"><?= nl2br(e($p['cos'])) ?></p>
            <p class="mt-4 text-sm text-gray-600">Created by <strong><?= e($p['autor']) ?></strong></p>
        </section>

        <?php if ($esAutor): ?>
            <!-- Solo el autor ve estas acciones -->
            <div class="mt-8 flex flex-wrap items-center gap-3 text-sm font-bold">
                <a href="personaje_form.php?id=<?= (int) $p['id'] ?>" class="rounded-full border border-black bg-brand-pink px-5 py-1.5 text-white hover:brightness-110">Edit</a>
                <form action="personaje_borrar.php" method="post" onsubmit="return confirm('Are you sure you want to delete this character?');">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="rounded-full border border-red-600 bg-white px-5 py-1.5 text-red-600 hover:bg-red-50">Delete</button>
                </form>
            </div>
        <?php endif; ?>
    </article>
</main>

<script>
// Carrusel de outfits (sin librerías). Solo se activa si hay 2 o más outfits.
(function () {
    var root = document.getElementById('outfits');
    var controls = document.getElementById('outfit-controls');
    if (!root || !controls) { return; }

    var slides = root.querySelectorAll('[data-slide]');
    var dots   = root.querySelectorAll('[data-dot]');
    var status = document.getElementById('outfit-status');
    var actual = 0;

    function mostrar(n, anunciar) {
        actual = (n + slides.length) % slides.length;   // da la vuelta al llegar al final
        for (var i = 0; i < slides.length; i++) {
            slides[i].hidden = (i !== actual);
            if (i === actual) { dots[i].setAttribute('aria-current', 'true'); }
            else { dots[i].removeAttribute('aria-current'); }
        }
        if (anunciar) {
            status.textContent = 'Outfit ' + (actual + 1) + ' of ' + slides.length + ': ' + slides[actual].getAttribute('data-name');
        }
    }

    controls.hidden = false;
    root.querySelector('[data-prev]').addEventListener('click', function () { mostrar(actual - 1, true); });
    root.querySelector('[data-next]').addEventListener('click', function () { mostrar(actual + 1, true); });
    for (var i = 0; i < dots.length; i++) {
        (function (n) { dots[n].addEventListener('click', function () { mostrar(n, true); }); })(i);
    }
    mostrar(0, false);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
