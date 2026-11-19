<?php
/**
 * Autor: Raul Rubio - Pt04
 * personajes.php (controlador)
 * Lógica del listado paginado, alta, edición y borrado de personajes (artículos).
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../modelo/personajes.php';
require_once __DIR__ . '/validacions.php';

/** Personajes por página (2 filas de 5, como en el diseño). */
const PERSONAJES_POR_PAGINA = 10;
/** Carpeta (relativa a la raíz web) donde se guardan las imágenes subidas. */
const CARPETA_SUBIDAS = 'uploads/characters/';
/** Máximo de outfits por personaje y huecos vacíos para añadir nuevos en cada envío del formulario. */
const OUTFITS_MAX = 10;
const OUTFITS_NUEVOS_POR_FORM = 3;

/**
 * Prepara los datos de la Home: personajes de la página actual, paginación y búsqueda.
 * Si no hay ningún artículo en toda la BDD y el usuario está logueado,
 * lo manda al formulario de alta para que cree el primero.
 */
function datosHome(): array
{
    try {
        $busqueda = trim($_GET['q'] ?? '');

        // Sin ningún personaje en toda la BDD: redirigimos a crear el primero
        $total = contarPersonajes($busqueda);
        if ($busqueda === '' && $total === 0 && estaLogueado()) {
            flash('error', 'There are no characters yet. Create the first one!');
            redirigir('personaje_form.php');
        }

        $totalPaginas = max(1, (int) ceil($total / PERSONAJES_POR_PAGINA));

        // Página actual: por defecto 1 si falta o no es un entero válido
        $pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT);
        if ($pagina === false || $pagina < 1) {
            $pagina = 1;
        }
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $desplazamiento = ($pagina - 1) * PERSONAJES_POR_PAGINA;

        return [
            'personajes'   => listarPersonajesPaginado($busqueda, PERSONAJES_POR_PAGINA, $desplazamiento),
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'busqueda'     => $busqueda,
            'total'        => $total,
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die('Characters could not be loaded.');
    }
}

/** Obtiene un personaje por id o redirige a la Home si no existe. */
function personajeOHome(int $id): array
{
    try {
        $personaje = obtenerPersonaje($id);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die('The character could not be loaded.');
    }
    if (!$personaje) {
        flash('error', 'The character does not exist.');
        redirigir('index.php');
    }
    return $personaje;
}

/**
 * Diapositivas del carrusel de la pantalla de detalle: un outfit por diapositiva.
 * Si el personaje no tiene outfits (p. ej. los creados desde el formulario),
 * se usa su imagen principal como única diapositiva. Si no hay ninguna imagen, devuelve [].
 * Cada diapositiva: ['nom' => ?string, 'imatge' => string].
 */
function diapositivasPersonaje(array $personaje): array
{
    $diapositivas = [];
    try {
        foreach (obtenerOutfits((int) $personaje['id']) as $o) {
            if (asset_exists($o['imatge'])) {
                $diapositivas[] = ['nom' => $o['nom'], 'imatge' => $o['imatge']];
            }
        }
    } catch (PDOException $e) {
        // Si la tabla outfits no existe todavía no rompemos la página: se usa la imagen principal
        error_log($e->getMessage());
    }

    if (!$diapositivas && asset_exists($personaje['imatge'])) {
        $diapositivas[] = ['nom' => null, 'imatge' => $personaje['imatge']];
    }
    return $diapositivas;
}

/** ¿El usuario logueado es el autor de este personaje? */
function esAutor(array $personaje): bool
{
    return estaLogueado() && (int) $personaje['usuari_id'] === usuarioId();
}

/** Texto de un campo POST (si alguien manda un array o algo raro, se trata como vacío). */
function textoPost(mixed $valor): string
{
    return is_string($valor) ? trim($valor) : '';
}

/**
 * Reorganiza $_FILES['campo'] cuando el input se llama campo[clave]:
 * PHP lo entrega "transpuesto"; aquí se devuelve [clave => ['name'=>..., 'tmp_name'=>..., 'error'=>..., ...]].
 */
function archivosPorClave(string $campo): array
{
    $f = $_FILES[$campo] ?? null;
    if (!$f || !is_array($f['name'] ?? null)) {
        return [];
    }
    $porClave = [];
    foreach (array_keys($f['name']) as $clave) {
        $porClave[$clave] = [
            'name'     => $f['name'][$clave],
            'type'     => $f['type'][$clave] ?? '',
            'tmp_name' => $f['tmp_name'][$clave] ?? '',
            'error'    => $f['error'][$clave] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $f['size'][$clave] ?? 0,
        ];
    }
    return $porClave;
}

/**
 * Gestiona el formulario de crear / editar un personaje (con sus datos de General Info y sus outfits).
 * $id = 0 => alta. $id > 0 => edición (solo si eres el autor).
 * Devuelve ['errores', 'datos', 'editando', 'outfits' (los ya guardados), 'nuevos' (huecos para añadir)].
 * Si se guarda bien, redirige y no vuelve.
 */
function procesarFormularioPersonaje(int $id): array
{
    exigirLogin();

    $editando = $id > 0;
    $datos    = ['titol' => '', 'cos' => '', 'va' => '', 'imatge' => null,
                 'frase' => '', 'anniversary' => '', 'height' => '', 'weight' => ''];
    $outfitsActuales = [];   // id => fila de la BDD (solo los de ESTE personaje)

    if ($editando) {
        $actual = personajeOHome($id);
        if (!esAutor($actual)) {
            flash('error', 'You can only edit your own characters.');
            redirigir('index.php');
        }
        $datos = $actual;
        try {
            foreach (obtenerOutfits($id) as $o) {
                $outfitsActuales[(int) $o['id']] = $o;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    // Lo que pinta el formulario en la sección Outfits
    $vistaOutfits = [];
    foreach ($outfitsActuales as $oid => $o) {
        $vistaOutfits[$oid] = ['id' => $oid, 'nom' => $o['nom'], 'imatge' => $o['imatge'], 'borrar' => false];
    }
    $vistaNuevos = array_fill(0, OUTFITS_NUEVOS_POR_FORM, ['nom' => '']);

    $resultado = ['errores' => [], 'datos' => $datos, 'editando' => $editando,
                  'outfits' => $vistaOutfits, 'nuevos' => $vistaNuevos];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $resultado;
    }

    $titol = trim($_POST['titol'] ?? '');
    $cos   = trim($_POST['cos'] ?? '');
    $va    = trim($_POST['va'] ?? '');
    $extra = [
        'frase'       => textoPost($_POST['frase'] ?? ''),
        'anniversary' => textoPost($_POST['anniversary'] ?? ''),
        'height'      => textoPost($_POST['height'] ?? ''),
        'weight'      => textoPost($_POST['weight'] ?? ''),
    ];

    $nomsOutfit    = is_array($_POST['outfit_nom'] ?? null) ? $_POST['outfit_nom'] : [];
    $borrarOutfits = is_array($_POST['outfit_del'] ?? null) ? $_POST['outfit_del'] : [];
    $nomsNuevos    = is_array($_POST['new_nom'] ?? null) ? $_POST['new_nom'] : [];
    $archivosOutfit = archivosPorClave('outfit_img');
    $archivosNuevos = archivosPorClave('new_img');

    // Mantenemos lo escrito por el usuario si hay errores (las imágenes elegidas no se pueden conservar)
    $resultado['datos'] = array_merge($datos, ['titol' => $titol, 'cos' => $cos, 'va' => $va], $extra);
    foreach ($outfitsActuales as $oid => $o) {
        $resultado['outfits'][$oid]['nom']    = array_key_exists($oid, $nomsOutfit) ? textoPost($nomsOutfit[$oid]) : $o['nom'];
        $resultado['outfits'][$oid]['borrar'] = isset($borrarOutfits[$oid]);
    }
    for ($i = 0; $i < OUTFITS_NUEVOS_POR_FORM; $i++) {
        $resultado['nuevos'][$i]['nom'] = textoPost($nomsNuevos[$i] ?? '');
    }

    if (!csrfValido()) {
        $resultado['errores']['general'] = 'Form expired (or file too large). Please try again.';
        return $resultado;
    }

    // ---- Validación ----
    $errores  = validarPersonaje($titol, $cos, $va) + validarDatosExtra($extra);
    $errorImg = validarImagenSubida($_FILES['imatge'] ?? []);
    if ($errorImg) {
        $errores['imatge'] = $errorImg;
    }

    $totalOutfits = 0;
    foreach ($resultado['outfits'] as $oid => $o) {
        if ($o['borrar']) {
            continue;
        }
        $totalOutfits++;
        $e = validarOutfit($o['nom'], $archivosOutfit[$oid] ?? [], false);
        foreach ($e as $campo => $mensaje) {
            $errores['outfit_' . ($campo === 'nom' ? 'nom' : 'img') . '_' . $oid] = $mensaje;
        }
    }
    $nuevosUsados = [];   // huecos nuevos en los que el usuario ha escrito un nombre o elegido una imagen
    for ($i = 0; $i < OUTFITS_NUEVOS_POR_FORM; $i++) {
        $archivo = $archivosNuevos[$i] ?? [];
        $nom     = $resultado['nuevos'][$i]['nom'];
        $usado   = $nom !== '' || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if (!$usado) {
            continue;
        }
        $nuevosUsados[] = $i;
        $totalOutfits++;
        $e = validarOutfit($nom, $archivo, true);
        foreach ($e as $campo => $mensaje) {
            $errores['new_' . ($campo === 'nom' ? 'nom' : 'img') . '_' . $i] = $mensaje;
        }
    }
    if ($totalOutfits > OUTFITS_MAX) {
        $errores['outfits'] = 'A character can have at most ' . OUTFITS_MAX . ' outfits.';
    }

    if ($errores) {
        $resultado['errores'] = $errores;
        return $resultado;
    }

    // ---- Guardado: todo en una transacción; las imágenes nuevas se limpian si algo falla ----
    $pdo        = getConexion();
    $escritas   = [];   // imágenes nuevas ya copiadas al disco (se borran si hay que deshacer)
    $obsoletas  = [];   // imágenes antiguas que se borran del disco cuando todo ha ido bien

    try {
        $rutaNueva = guardarImagenSubida($_FILES['imatge'] ?? []);
        if ($rutaNueva !== null) {
            $escritas[] = $rutaNueva;
        }

        $pdo->beginTransaction();

        if ($editando) {
            modificarPersonaje($id, usuarioId(), $titol, $cos, $va, $rutaNueva, $extra);
            $articleId = $id;
            if ($rutaNueva !== null) {
                $obsoletas[] = $datos['imatge'];
            }
        } else {
            $articleId = crearPersonaje(usuarioId(), $titol, $cos, $va, $rutaNueva, $extra);
        }

        // Outfits que ya existían: borrar o renombrar / cambiar imagen
        foreach ($outfitsActuales as $oid => $o) {
            if ($resultado['outfits'][$oid]['borrar']) {
                borrarOutfit($oid, $articleId);
                $obsoletas[] = $o['imatge'];
                continue;
            }
            $imgOutfit = guardarImagenSubida($archivosOutfit[$oid] ?? []);
            if ($imgOutfit !== null) {
                $escritas[]  = $imgOutfit;
                $obsoletas[] = $o['imatge'];
            }
            modificarOutfit($oid, $articleId, $resultado['outfits'][$oid]['nom'], $imgOutfit);
        }

        // Outfits nuevos: van al final del carrusel
        if ($nuevosUsados) {
            $ordre = siguienteOrdreOutfit($articleId);
            foreach ($nuevosUsados as $i) {
                $imgOutfit  = guardarImagenSubida($archivosNuevos[$i]);
                $escritas[] = $imgOutfit;
                crearOutfit($articleId, $resultado['nuevos'][$i]['nom'], $imgOutfit, $ordre++);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        foreach ($escritas as $ruta) {
            borrarImagenSubida($ruta);
        }
        error_log($e->getMessage());
        $resultado['errores']['general'] = 'The character could not be saved. Please try again later.';
        return $resultado;
    }

    // Todo guardado: ahora sí se borran del disco las imágenes que ya no se usan (solo las de uploads/)
    foreach ($obsoletas as $ruta) {
        borrarImagenSubida($ruta);
    }
    flash('ok', $editando ? 'Character updated.' : 'Character created.');
    redirigir('index.php');
}

/** Procesa el borrado (solo POST + CSRF + autor). Siempre redirige. */
function procesarBorradoPersonaje(): void
{
    exigirLogin();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido()) {
        flash('error', 'Invalid request.');
        redirigir('index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);

    try {
        $personaje = obtenerPersonaje($id);
        if (!$personaje) {
            flash('error', 'The character does not exist.');
        } elseif (!esAutor($personaje)) {
            flash('error', 'You can only delete your own characters.');
        } else {
            $outfits = obtenerOutfits($id);   // se leen antes de borrar, para poder limpiar sus imágenes
            if (borrarPersonaje($id, usuarioId())) {
                borrarImagenSubida($personaje['imatge']);
                foreach ($outfits as $o) {
                    borrarImagenSubida($o['imatge']);
                }
                flash('ok', 'Character deleted.');
            } else {
                flash('error', 'Could not delete.');
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        flash('error', 'Could not delete.');
    }
    redirigir('index.php');
}

/**
 * Guarda la imagen subida (ya validada) con un nombre aleatorio.
 * Devuelve la ruta relativa para la BDD o null si no se subió nada.
 */
function guardarImagenSubida(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $ext  = IMAGEN_TIPOS[$mime];
    // Nombre aleatorio: nunca usamos el nombre original del cliente
    $nombre = bin2hex(random_bytes(16)) . '.' . $ext;

    $carpeta = dirname(__DIR__) . '/' . CARPETA_SUBIDAS;
    if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
        throw new RuntimeException('The uploads folder cannot be created.');
    }
    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombre)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return CARPETA_SUBIDAS . $nombre;
}

/** Borra una imagen del disco, pero SOLO si está en la carpeta de subidas (no toca assets/). */
function borrarImagenSubida(?string $ruta): void
{
    if ($ruta !== null && str_starts_with($ruta, CARPETA_SUBIDAS) && basename($ruta) === substr($ruta, strlen(CARPETA_SUBIDAS))) {
        $fichero = dirname(__DIR__) . '/' . $ruta;
        if (is_file($fichero)) {
            unlink($fichero);
        }
    }
}
