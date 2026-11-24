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
$outfits  = $estado['outfits'];   // outfits ya guardados (solo al editar)
$nuevos   = $estado['nuevos'];    // huecos vacíos para añadir outfits

$title = ($editando ? 'Edit character' : 'New character') . ' · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-full border border-black bg-white px-5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink';
$fileClass  = 'w-full rounded-full border border-black bg-white px-5 py-2 text-sm file:mr-3 file:rounded-full file:border-0 file:bg-brand-pink file:px-4 file:py-1 file:text-xs file:font-bold file:text-white';
$fieldsetClass = 'rounded-3xl border border-black px-5 pb-5 pt-2';
$legendClass   = 'px-2 text-sm font-bold text-brand-pink';
?>

<main id="main" tabindex="-1" class="mx-auto w-full max-w-xl flex-1 px-4 pb-10 pt-10 sm:pt-16">
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
            <label for="imatge" class="mb-1 block px-3 text-xs font-bold">Main image, shown on the home page (JPG, PNG or WEBP, max 2 MB)</label>
            <?php if ($editando && asset_exists($datos['imatge'] ?? null)): ?>
                <img src="<?= e($datos['imatge']) ?>" alt="Current image" class="mb-2 ml-3 h-24 w-auto border border-black">
                <p class="mb-1 px-3 text-[11px] text-gray-600">Upload another one only if you want to change it.</p>
            <?php endif; ?>
            <input id="imatge" name="imatge" type="file" accept="image/jpeg,image/png,image/webp" class="<?= $fileClass ?>">
            <?= errorCampo($errores, 'imatge') ?>
        </div>

        <fieldset class="<?= $fieldsetClass ?> space-y-4">
            <legend class="<?= $legendClass ?>">General info (optional)</legend>
            <div>
                <label for="frase" class="mb-1 block px-3 text-xs font-bold">Quote</label>
                <input id="frase" name="frase" type="text" maxlength="255" value="<?= e($datos['frase']) ?>" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'frase') ?>
            </div>
            <div>
                <label for="anniversary" class="mb-1 block px-3 text-xs font-bold">Birthday (for example: May 22)</label>
                <input id="anniversary" name="anniversary" type="text" maxlength="30" value="<?= e($datos['anniversary']) ?>" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'anniversary') ?>
            </div>
            <div>
                <label for="height" class="mb-1 block px-3 text-xs font-bold">Height in cm (50 to 300)</label>
                <input id="height" name="height" type="number" inputmode="numeric" min="50" max="300" value="<?= e((string) $datos['height']) ?>" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'height') ?>
            </div>
            <div>
                <label for="weight" class="mb-1 block px-3 text-xs font-bold">Weight</label>
                <input id="weight" name="weight" type="text" maxlength="150" value="<?= e($datos['weight']) ?>" class="<?= $inputClass ?>">
                <?= errorCampo($errores, 'weight') ?>
            </div>
        </fieldset>

        <fieldset class="<?= $fieldsetClass ?> space-y-5">
            <legend class="<?= $legendClass ?>">Outfits (optional)</legend>
            <p class="px-3 text-xs text-gray-600">Each outfit is a slide in the character's carousel. Images: JPG, PNG or WEBP, max 2 MB each.</p>
            <?= errorCampo($errores, 'outfits') ?>

            <?php foreach ($outfits as $oid => $o): ?>
                <div class="space-y-3 rounded-2xl border border-gray-400 bg-white/60 p-4">
                    <p class="text-sm font-bold">Saved outfit</p>
                    <?php if (asset_exists($o['imatge'])): ?>
                        <img src="<?= e($o['imatge']) ?>" alt="Current image of this outfit" class="h-24 w-auto border border-black bg-white">
                    <?php endif; ?>
                    <div>
                        <label for="outfit-nom-<?= (int) $oid ?>" class="mb-1 block px-3 text-xs font-bold">Outfit name</label>
                        <input id="outfit-nom-<?= (int) $oid ?>" name="outfit_nom[<?= (int) $oid ?>]" type="text" maxlength="100" value="<?= e($o['nom']) ?>" class="<?= $inputClass ?>">
                        <?= errorCampo($errores, 'outfit_nom_' . $oid) ?>
                    </div>
                    <div>
                        <label for="outfit-img-<?= (int) $oid ?>" class="mb-1 block px-3 text-xs font-bold">Replace image (leave empty to keep the current one)</label>
                        <input id="outfit-img-<?= (int) $oid ?>" name="outfit_img[<?= (int) $oid ?>]" type="file" accept="image/jpeg,image/png,image/webp" class="<?= $fileClass ?>">
                        <?= errorCampo($errores, 'outfit_img_' . $oid) ?>
                    </div>
                    <div class="flex items-center gap-2 px-3">
                        <input id="outfit-del-<?= (int) $oid ?>" name="outfit_del[<?= (int) $oid ?>]" type="checkbox" value="1" <?= $o['borrar'] ? 'checked' : '' ?> class="h-5 w-5 accent-brand-pink">
                        <label for="outfit-del-<?= (int) $oid ?>" class="text-xs font-bold">Remove this outfit when saving</label>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($nuevos as $i => $n): ?>
                <div class="space-y-3 rounded-2xl border border-dashed border-gray-500 p-4">
                    <p class="text-sm font-bold">Add new outfit <?= $i + 1 ?></p>
                    <div>
                        <label for="new-nom-<?= $i ?>" class="mb-1 block px-3 text-xs font-bold">Outfit name</label>
                        <input id="new-nom-<?= $i ?>" name="new_nom[<?= $i ?>]" type="text" maxlength="100" value="<?= e($n['nom']) ?>" class="<?= $inputClass ?>">
                        <?= errorCampo($errores, 'new_nom_' . $i) ?>
                    </div>
                    <div>
                        <label for="new-img-<?= $i ?>" class="mb-1 block px-3 text-xs font-bold">Outfit image</label>
                        <input id="new-img-<?= $i ?>" name="new_img[<?= $i ?>]" type="file" accept="image/jpeg,image/png,image/webp" class="<?= $fileClass ?>">
                        <?= errorCampo($errores, 'new_img_' . $i) ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <p class="px-3 text-xs text-gray-600">Need more than <?= OUTFITS_NUEVOS_POR_FORM ?> outfits? Save and edit the character again to add more.</p>
        </fieldset>

        <div class="flex items-center justify-between pt-2">
            <button type="submit" class="rounded-full bg-brand-pink px-8 py-2 text-sm font-bold text-white hover:brightness-110"><?= $editando ? 'Save changes' : 'Create' ?></button>
            <a href="index.php" class="text-xs font-bold text-brand-pink hover:underline">Cancel</a>
        </div>
    </form>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
