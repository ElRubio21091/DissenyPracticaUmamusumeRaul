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

/**
 * Gestiona el formulario de crear / editar un personaje.
 * $id = 0 => alta. $id > 0 => edición (solo si eres el autor).
 * Devuelve ['errores' => [...], 'datos' => [...], 'editando' => bool].
 * Si se guarda bien, redirige y no vuelve.
 */
function procesarFormularioPersonaje(int $id): array
{
    exigirLogin();

    $editando = $id > 0;
    $datos    = ['titol' => '', 'cos' => '', 'va' => '', 'imatge' => null];

    if ($editando) {
        $actual = personajeOHome($id);
        if (!esAutor($actual)) {
            flash('error', 'You can only edit your own characters.');
            redirigir('index.php');
        }
        $datos = $actual;
    }

    $resultado = ['errores' => [], 'datos' => $datos, 'editando' => $editando];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $resultado;
    }

    $titol = trim($_POST['titol'] ?? '');
    $cos   = trim($_POST['cos'] ?? '');
    $va    = trim($_POST['va'] ?? '');

    // Mantenemos lo escrito por el usuario si hay errores (y la imagen actual)
    $resultado['datos'] = array_merge($datos, ['titol' => $titol, 'cos' => $cos, 'va' => $va]);

    if (!csrfValido()) {
        $resultado['errores']['general'] = 'Form expired (or file too large). Please try again.';
        return $resultado;
    }

    $errores = validarPersonaje($titol, $cos, $va);
    $errorImg = validarImagenSubida($_FILES['imatge'] ?? []);
    if ($errorImg) {
        $errores['imatge'] = $errorImg;
    }

    if ($errores) {
        $resultado['errores'] = $errores;
        return $resultado;
    }

    try {
        // Solo se guarda la imagen en disco cuando todo lo demás es válido
        $rutaNueva = guardarImagenSubida($_FILES['imatge'] ?? []);

        if ($editando) {
            modificarPersonaje($id, usuarioId(), $titol, $cos, $va, $rutaNueva);
            // Si se ha reemplazado la imagen, borramos la anterior (solo si es una subida nuestra)
            if ($rutaNueva !== null) {
                borrarImagenSubida($datos['imatge']);
            }
            flash('ok', 'Character updated.');
        } else {
            crearPersonaje(usuarioId(), $titol, $cos, $va, $rutaNueva);
            flash('ok', 'Character created.');
        }
        redirigir('index.php');
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $resultado['errores']['general'] = 'The character could not be saved. Please try again later.';
    }

    return $resultado;
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
        } elseif (borrarPersonaje($id, usuarioId())) {
            borrarImagenSubida($personaje['imatge']);
            flash('ok', 'Character deleted.');
        } else {
            flash('error', 'Could not delete.');
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
