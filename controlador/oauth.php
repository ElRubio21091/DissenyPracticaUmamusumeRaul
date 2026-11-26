<?php
/**
 * Autor: Raul Rubio - Pt04
 * oauth.php (controlador)
 * Lógica del login con Google. Las páginas oauth_google.php y oauth_callback.php
 * solo llaman a estas funciones.
 *
 * Al volver de Google hay tres casos:
 *  1. Ya existe un usuario con ese google_id     -> se inicia sesión.
 *  2. Existe un usuario local con el mismo email  -> se vincula a Google y se inicia sesión
 *     (solo si Google confirma que el email está verificado).
 *  3. No existe                                   -> se crea la cuenta (sin contraseña) y se inicia sesión.
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/../modelo/usuarios.php';

/** Paso 1: guarda state + code_verifier en la SESIÓN y manda al usuario a Google. */
function iniciarLoginGoogle(): void
{
    if (!googleConfigurado()) {
        flash('error', 'Google login is not configured on this server.');
        redirigir('login.php');
    }

    // Van en sesión (servidor) y no en cookie: el usuario no debe poder verlos ni cambiarlos
    $_SESSION['oauth_state']    = bin2hex(random_bytes(16));
    $_SESSION['oauth_verifier'] = base64url(random_bytes(32));

    redirigir(googleUrlAutorizacion($_SESSION['oauth_state'], $_SESSION['oauth_verifier']));
}

/** Paso 2: Google vuelve aquí con ?code=...&state=... */
function procesarCallbackGoogle(): void
{
    // Se leen y se borran: cada state/verifier sirve para un único intento
    $stateEsperado = $_SESSION['oauth_state'] ?? '';
    $verifier      = $_SESSION['oauth_verifier'] ?? '';
    unset($_SESSION['oauth_state'], $_SESSION['oauth_verifier']);

    // El usuario canceló en la pantalla de Google
    if (isset($_GET['error'])) {
        flash('error', 'Google login was cancelled.');
        redirigir('login.php');
    }

    // El state tiene que ser el que generamos nosotros: si no, la petición no la empezó este usuario (CSRF)
    $state = $_GET['state'] ?? '';
    $code  = $_GET['code'] ?? '';
    if ($stateEsperado === '' || !is_string($state) || !hash_equals($stateEsperado, $state)
        || !is_string($code) || $code === '') {
        flash('error', 'Google login expired or is invalid. Please try again.');
        redirigir('login.php');
    }

    try {
        $perfil  = googleObtenerPerfil(googleObtenerToken($code, $verifier));
        $usuario = usuarioDesdePerfilGoogle($perfil);
    } catch (RuntimeException | PDOException $e) {
        // Detalle técnico al log, mensaje genérico a pantalla
        error_log('OAuth Google: ' . $e->getMessage());
        flash('error', 'Could not log in with Google. Please try again later.');
        redirigir('login.php');
    }

    if ($usuario === null) {
        flash('error', 'Your Google email is not verified, so it cannot be used to log in.');
        redirigir('login.php');
    }

    // Mismo inicio de sesión que el login normal (regenera el id de sesión, cookie de último usuario...)
    loguearUsuario($usuario);
    flash('ok', 'Logged in with Google. Hello, ' . $usuario['username'] . '!');
    redirigir('index.php');
}

/**
 * Devuelve el usuario local que corresponde al perfil de Google (creándolo o
 * vinculándolo si hace falta). Devuelve null si el email no está verificado.
 */
function usuarioDesdePerfilGoogle(array $perfil): ?array
{
    $googleId = (string) $perfil['sub'];
    $email    = mb_strtolower((string) $perfil['email']);

    // 1. Ya había entrado con Google antes
    $usuario = buscarUsuarioPorGoogleId($googleId);
    if ($usuario) {
        return $usuario;
    }

    // Sin email verificado no vinculamos ni creamos nada
    if (empty($perfil['email_verified'])) {
        return null;
    }

    // 2. Ya tenía cuenta local con ese email: se vincula
    $usuario = buscarUsuarioPorEmail($email);
    if ($usuario) {
        vincularGoogleId((int) $usuario['id'], $googleId);
        return $usuario;
    }

    // 3. Usuario nuevo: username válido y libre a partir del nombre de Google
    $username = generarUsernameLibre($perfil['given_name'] ?? $perfil['name'] ?? strstr($email, '@', true));
    $id = crearUsuarioGoogle($username, $email, $googleId);
    return ['id' => $id, 'username' => $username, 'email' => $email];
}

/**
 * Convierte un nombre cualquiera ("María José") en un username que cumpla las
 * mismas reglas que el registro (3-30 caracteres: letras, números y _) y que
 * no exista todavía (añade un número si hace falta: maria_jose2, maria_jose3...).
 */
function generarUsernameLibre(string $base): string
{
    // Quita acentos (á -> a, ñ -> n, ç -> c...): htmlentities da "&aacute;" y nos quedamos con la letra.
    // (iconv TRANSLIT no sirve en Windows: convierte "á" en "'a")
    $base = htmlentities($base, ENT_QUOTES, 'UTF-8');
    $base = preg_replace('/&([A-Za-z])(acute|grave|circ|tilde|uml|ring|cedil|slash);/', '$1', $base);
    $base = html_entity_decode($base, ENT_QUOTES, 'UTF-8');
    // Lo que no sea letra/número sin acento pasa a "_"
    $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '_', $base), '_'));
    if (strlen($base) < 3) {
        $base = 'trainer';
    }
    $base = substr($base, 0, 25);   // deja sitio para el número

    $candidato = $base;
    for ($n = 2; existeUsername($candidato); $n++) {
        $candidato = $base . $n;
    }
    return $candidato;
}
