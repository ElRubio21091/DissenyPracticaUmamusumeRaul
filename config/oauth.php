<?php
/**
 * Autor: Raul Rubio - Pt04
 * oauth.php
 * Configuración y llamadas HTTP del login con Google (OAuth 2.0 / OpenID Connect).
 * Se implementa a mano con cURL (sin librerías externas) siguiendo el flujo
 * "Authorization Code" con PKCE:
 *   1. Redirigimos al usuario a Google con un `state` aleatorio y un `code_challenge`.
 *   2. Google vuelve a oauth_callback.php con un `code`.
 *   3. Cambiamos ese `code` (+ client_secret + code_verifier) por un access_token.
 *   4. Con el access_token pedimos el perfil (sub, email, nombre).
 *
 * Las credenciales NO están en el código: se leen del fichero `.env`
 * (ignorado por Git). Hay un `.env.example` como plantilla.
 */

const GOOGLE_AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_URL    = 'https://oauth2.googleapis.com/token';
const GOOGLE_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

/**
 * Lee el fichero .env de la raíz del proyecto (formato CLAVE=valor).
 * Se lee una sola vez por petición. Devuelve null si la clave no existe.
 */
function env(string $clave): ?string
{
    static $valores = null;

    if ($valores === null) {
        $valores = [];
        $ruta = __DIR__ . '/../.env';
        if (is_readable($ruta)) {
            foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
                $linea = trim($linea);
                // Comentarios y líneas sin "=" se ignoran
                if ($linea === '' || $linea[0] === '#' || !str_contains($linea, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $linea, 2);
                // Quita comillas opcionales: CLAVE="valor"
                $valores[trim($k)] = trim(trim($v), "\"'");
            }
        }
    }

    $valor = $valores[$clave] ?? '';
    return $valor === '' ? null : $valor;
}

/** ¿Están configuradas las credenciales de Google? Si no, el botón avisa en lugar de fallar. */
function googleConfigurado(): bool
{
    return env('GOOGLE_CLIENT_ID') !== null && env('GOOGLE_CLIENT_SECRET') !== null;
}

/**
 * URL a la que Google devuelve al usuario. Debe coincidir EXACTAMENTE con la
 * registrada en Google Cloud Console. Si no se indica en el .env, se calcula
 * a partir de la URL actual (p. ej. http://localhost/.../oauth_callback.php).
 */
function googleRedirectUri(): string
{
    $uri = env('GOOGLE_REDIRECT_URI');
    if ($uri !== null) {
        return $uri;
    }
    $esquema = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $carpeta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $esquema . '://' . $_SERVER['HTTP_HOST'] . $carpeta . '/oauth_callback.php';
}

/** Codificación base64 "URL safe" sin relleno (la que pide PKCE). */
function base64url(string $bytes): string
{
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

/**
 * Construye la URL de autorización de Google.
 * $state evita CSRF en el callback; $codeVerifier se queda en la sesión
 * y a Google solo le mandamos su hash (code_challenge).
 */
function googleUrlAutorizacion(string $state, string $codeVerifier): string
{
    return GOOGLE_AUTH_URL . '?' . http_build_query([
        'client_id'             => env('GOOGLE_CLIENT_ID'),
        'redirect_uri'          => googleRedirectUri(),
        'response_type'         => 'code',
        'scope'                 => 'openid email profile',
        'state'                 => $state,
        'code_challenge'        => base64url(hash('sha256', $codeVerifier, true)),
        'code_challenge_method' => 'S256',
        'prompt'                => 'select_account',  // deja elegir cuenta aunque haya una abierta
    ]);
}

/**
 * Petición HTTP con cURL que devuelve el JSON decodificado.
 * Lanza RuntimeException si la red falla o Google responde con error.
 */
function httpJson(string $url, ?array $post = null, ?string $bearer = null): array
{
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,   // nunca desactivar: comprobamos que hablamos con Google
    ]);
    if ($post !== null) {
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($bearer !== null) {
        curl_setopt($c, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $bearer]);
    }

    $respuesta = curl_exec($c);
    $codigo    = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $errorRed  = curl_error($c);
    curl_close($c);

    if ($respuesta === false) {
        throw new RuntimeException('Error de red con Google: ' . $errorRed);
    }
    $json = json_decode($respuesta, true);
    if ($codigo !== 200 || !is_array($json)) {
        throw new RuntimeException('Google respondió HTTP ' . $codigo . ': ' . $respuesta);
    }
    return $json;
}

/** Cambia el `code` recibido en el callback por un access_token. */
function googleObtenerToken(string $code, string $codeVerifier): string
{
    $json = httpJson(GOOGLE_TOKEN_URL, [
        'code'          => $code,
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri'  => googleRedirectUri(),
        'grant_type'    => 'authorization_code',
        'code_verifier' => $codeVerifier,
    ]);
    if (empty($json['access_token'])) {
        throw new RuntimeException('Google no devolvió access_token.');
    }
    return $json['access_token'];
}

/**
 * Pide el perfil del usuario a Google. Devuelve como mínimo:
 *  sub (id único y estable), email, email_verified, name, given_name.
 */
function googleObtenerPerfil(string $accessToken): array
{
    $perfil = httpJson(GOOGLE_USERINFO_URL, null, $accessToken);
    if (empty($perfil['sub']) || empty($perfil['email'])) {
        throw new RuntimeException('Perfil de Google incompleto.');
    }
    return $perfil;
}
