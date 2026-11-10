<?php
/**
 * Autor: Raul Rubio - Pt04
 * index.php (Home / Characters)
 * Página pública: todos ven todos los personajes (paginados de 10 en 10).
 * Solo el autor de cada personaje ve los botones de editar y borrar.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/personajes.php';

// Toda la lógica está en el controlador; aquí solo se pinta
$d = datosHome();
$personajes   = $d['personajes'];
$pagina       = $d['pagina'];
$totalPaginas = $d['totalPaginas'];
$busqueda     = $d['busqueda'];

// Estado de los botones Anterior / Següent
$hayAnterior  = $pagina > 1;
$haySiguiente = $pagina < $totalPaginas;

/** URL de una página conservando la búsqueda. */
function urlPagina(int $n, string $busqueda): string
{
    $params = ['pagina' => $n];
    if ($busqueda !== '') {
        $params['q'] = $busqueda;
    }
    return 'index.php?' . http_build_query($params);
}

$title = 'Characters · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main class="mx-auto w-full max-w-7xl flex-1 px-4 pb-8 pt-10 xl:max-w-none xl:px-[8.8vw] xl:pt-[4.75vw]">
    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <span class="hidden w-56 sm:block"></span>
        <h1 class="text-[length:clamp(26px,1.878vw,48px)] font-bold leading-none">CHARACTERS</h1>

        <form action="index.php" method="get" class="flex w-56 items-center gap-3">
            <label for="q" class="sr-only">Buscar personaje</label>
            <input id="q" name="q" type="search" placeholder="Search" value="<?= e($busqueda) ?>"
                   class="w-full rounded-full border border-black bg-white px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink">
            <button type="submit" aria-label="Buscar"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-black bg-white hover:bg-brand-pink/20">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
            </button>
        </form>
    </div>

    <h2 class="mt-6 text-[length:clamp(22px,1.565vw,40px)] font-bold leading-none xl:mt-[1.88vw]">UMAMUSUME</h2>

    <?php if (empty($personajes)): ?>
        <p class="mt-10 text-center text-sm font-bold">
            <?php if ($busqueda !== ''): ?>
                No hay personajes que coincidan con «<?= e($busqueda) ?>».
                <a href="index.php" class="text-brand-pink hover:underline">Ver todos</a>
            <?php else: ?>
                Todavía no hay personajes.
                <a href="login.php" class="text-brand-pink hover:underline">Inicia sesión</a> para crear el primero.
            <?php endif; ?>
        </p>
    <?php else: ?>

    <!-- DIV 1: los personajes de esta página -->
    <div id="articles">
        <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-8 sm:grid-cols-3 lg:grid-cols-5 xl:mt-[2.09vw] xl:grid-cols-[repeat(5,9.8vw)] xl:gap-x-[8.8vw] xl:gap-y-[2.14vw]">
            <?php foreach ($personajes as $c): ?>
                <li>
                    <a href="personaje.php?id=<?= (int) $c['id'] ?>" class="mx-auto block w-full max-w-[160px] xl:mx-0 xl:max-w-none">
                        <div class="aspect-[3/4] overflow-hidden border border-black bg-white">
                            <?php if (asset_exists($c['imatge'])): ?>
                                <img src="<?= e($c['imatge']) ?>" alt="<?= e($c['titol']) ?>" loading="lazy" class="h-full w-full object-cover object-top">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-b from-pink-100 to-purple-200 text-5xl font-bold text-brand-pink"
                                     role="img" aria-label="<?= e($c['titol']) ?>"><?= e(mb_substr($c['titol'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="border border-t-0 border-black bg-brand-pink px-1 py-1 text-center text-white xl:py-[0.15vw]">
                            <p class="truncate text-[length:clamp(11px,0.835vw,18px)] font-bold leading-tight"><?= e($c['titol']) ?></p>
                            <p class="truncate text-[length:clamp(10px,0.73vw,16px)] font-bold leading-tight">VA: <?= e($c['va']) ?></p>
                        </div>
                    </a>

                    <?php if (estaLogueado() && (int) $c['usuari_id'] === usuarioId()): ?>
                        <!-- Solo el autor ve estas acciones -->
                        <div class="mx-auto mt-1 flex w-full max-w-[160px] items-center justify-center gap-2 text-xs font-bold xl:mx-0 xl:max-w-none">
                            <a href="personaje_form.php?id=<?= (int) $c['id'] ?>" class="rounded-full border border-black bg-white px-3 py-0.5 hover:bg-brand-pink/20">Edit</a>
                            <form action="personaje_borrar.php" method="post" onsubmit="return confirm('¿Seguro que quieres borrar este personaje?');">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="rounded-full border border-black bg-white px-3 py-0.5 text-red-600 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- DIV 2: estado de los botones Anterior / Següent -->
    <div id="paginacio" class="mt-10 flex items-center justify-center gap-4 text-sm font-bold">
        <?php if ($hayAnterior): ?>
            <a href="<?= e(urlPagina($pagina - 1, $busqueda)) ?>" class="rounded-full border border-black bg-brand-pink px-5 py-1.5 text-white hover:brightness-110">&laquo; Anterior</a>
        <?php else: ?>
            <span aria-disabled="true" class="cursor-not-allowed rounded-full border border-gray-400 bg-gray-200 px-5 py-1.5 text-gray-500">&laquo; Anterior</span>
        <?php endif; ?>

        <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>

        <?php if ($haySiguiente): ?>
            <a href="<?= e(urlPagina($pagina + 1, $busqueda)) ?>" class="rounded-full border border-black bg-brand-pink px-5 py-1.5 text-white hover:brightness-110">Següent &raquo;</a>
        <?php else: ?>
            <span aria-disabled="true" class="cursor-not-allowed rounded-full border border-gray-400 bg-gray-200 px-5 py-1.5 text-gray-500">Següent &raquo;</span>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
