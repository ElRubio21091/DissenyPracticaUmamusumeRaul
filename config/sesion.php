<?php
/**
 * Autor: Raul Rubio - Pt04
 * sesion.php
 * Todo lo relacionado con sesiones y cookies:
 *  - SESIÓN (servidor): guarda quién está logueado, el token CSRF y los mensajes flash.
 *    Va en sesión porque son datos sensibles que el usuario no debe poder manipular.
 *  - COOKIE (navegador): "ultimo_usuario" guarda solo el nombre de usuario para
 *    rellenar el login la próxima vez. No es sensible y debe sobrevivir al cierre de sesión.
 */

// Mínimo exigido: 40 min. Se usan 45 min de INACTIVIDAD (se renueva con cada petición).
const SESION_DURACION = 45 * 60;
const COOKIE_ULTIMO_USUARIO = 'ultimo_usuario';

/** Arranca la sesión con parámetros seguros y comprueba si ha caducado por inactividad. */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Que el recolector de PHP no borre la sesión antes de tiempo
    ini_set('session.gc_maxlifetime', (string) SESION_DURACION);

    session_set_cookie_params([
        'lifetime' => SESION_DURACION,
        'path'     => '/',
        'httponly' => true,       // JavaScript no puede leer la cookie de sesión (mitiga XSS)
        'samesite' => 'Lax',      // mitiga CSRF
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();

    // Caducidad por inactividad
    if (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > SESION_DURACION) {
        session_unset();
        session_destroy();
        session_start();
        flash('error', 'Tu sesión ha caducado. Vuelve a iniciar sesión.');
    }
    $_SESSION['ultima_actividad'] = time();

    // Renueva la cookie para que la duración sea deslizante
    if (isset($_SESSION['usuario_id'])) {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + SESION_DURACION,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
    }
}

/** Marca al usuario como logueado. Regenera el id para evitar session fixation. */
function loguearUsuario(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id']       = (int) $usuario['id'];
    $_SESSION['usuario_username'] = $usuario['username'];

    // Cookie no sensible: solo el username, 30 días
    setcookie(COOKIE_ULTIMO_USUARIO, $usuario['username'], [
        'expires'  => time() + 30 * 24 * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Cierra la sesión por completo (datos, cookie de sesión y fichero del servidor). */
function cerrarSesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** ¿Hay un usuario logueado? */
function estaLogueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/** Id del usuario logueado (o null si es anónimo). */
function usuarioId(): ?int
{
    return $_SESSION['usuario_id'] ?? null;
}

/** Nombre del usuario logueado (o null). */
function usuarioNombre(): ?string
{
    return $_SESSION['usuario_username'] ?? null;
}

/** Obliga a estar logueado; si no, manda al login. */
function exigirLogin(): void
{
    if (!estaLogueado()) {
        flash('error', 'Debes iniciar sesión para hacer eso.');
        redirigir('login.php');
    }
}

/** Impide entrar a login/register si ya hay sesión. */
function exigirAnonimo(): void
{
    if (estaLogueado()) {
        redirigir('index.php');
    }
}

/* ---------- Mensajes flash (se muestran una sola vez) ---------- */

function flash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Devuelve el mensaje flash y lo borra. */
function tomarFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Protección CSRF ---------- */

/** Token CSRF de la sesión (se crea si no existe). */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Valida el token enviado en un POST. */
function csrfValido(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Redirige y termina el script. */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}
