<?php
/**
 * Autor: Raul Rubio - Pt04
 * personaje_form.php
 * Un único formulario para crear (sin ?id) y editar (?id=N) un personaje.
 * Solo accesible con sesión iniciada; solo el autor puede editar.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/personajes.php';

// El id viene por GET al abrir el formulario y por un campo oculto al enviarlo
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

$estado   = procesarFormularioPersonaje($id);
$errores  = $estado['errores'];
$datos    = $estado['datos'];
$editando = $estado['editando'];

$title = ($editando ? 'Edit character' : 'New character') . ' · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-full border border-black bg-white px-5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink';
?>

<main class="mx-auto w-full max-w-xl flex-1 px-4 pb-10 pt-10 sm:pt-16">
    <h1 class="text-center text-2xl font-bold"><?= $editando ? 'EDIT CHARACTER' : 'NEW CHARACTER' ?></h1>

    <form action="personaje_form.php" method="post" enctype="multipart/form-data" class="mt-8 space-y-4" novalidate>
        <?= campoCsrf() ?>
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php endif; ?>

        <?php if (!empty($errores['general'])): ?>
            <p class="rounded-2xl border border-red-600 bg-red-100 px-4 py-2 text-xs font-bold text-red-800" role="alert"><?= e($errores['general']) ?></p>
        <?php endif; ?>

        <div>
            <label for="titol" class="mb-1 block px-3 text-xs font-bold">Name</label>
            <input id="titol" name="titol" type="text" maxlength="100" value="<?= e($datos['titol']) ?>" class="<?= $inputClass ?>">
            <?= errorCampo($errores, 'titol') ?>
        </div>
        <div>
            <label for="va" class="mb-1 block px-3 text-xs font-bold">Voice actor (VA)</label>
            <input id="va" name="va" type="text" maxlength="100" value="<?= e($datos['va']) ?>" class="<?= $inputClass ?>">
            <?= errorCampo($errores, 'va') ?>
        </div>
        <div>
            <label for="cos" class="mb-1 block px-3 text-xs font-bold">Description</label>
            <textarea id="cos" name="cos" rows="6" maxlength="5000"
                      class="w-full rounded-3xl border border-black bg-white px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink"><?= e($datos['cos']) ?></textarea>
            <?= errorCampo($errores, 'cos') ?>
        </div>
        <div>
            <label for="imatge" class="mb-1 block px-3 text-xs font-bold">Image (JPG, PNG or WEBP, max 2 MB)</label>
            <?php if ($editando && asset_exists($datos['imatge'] ?? null)): ?>
                <img src="<?= e($datos['imatge']) ?>" alt="Imagen actual" class="mb-2 ml-3 h-24 w-auto border border-black">
                <p class="mb-1 px-3 text-[11px] text-gray-600">Sube otra solo si quieres cambiarla.</p>
            <?php endif; ?>
            <input id="imatge" name="imatge" type="file" accept="image/jpeg,image/png,image/webp"
                   class="w-full rounded-full border border-black bg-white px-5 py-2 text-sm file:mr-3 file:rounded-full file:border-0 file:bg-brand-pink file:px-4 file:py-1 file:text-xs file:font-bold file:text-white">
            <?= errorCampo($errores, 'imatge') ?>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit" class="rounded-full bg-brand-pink px-8 py-2 text-sm font-bold text-white hover:brightness-110"><?= $editando ? 'Save changes' : 'Create' ?></button>
            <a href="index.php" class="text-xs font-bold text-brand-pink hover:underline">Cancel</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
