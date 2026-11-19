<?php
/**
 * Autor: Raul Rubio - Pt04
 * validacions.php
 * Funciones de validación reutilizables. Cada una devuelve un array de errores
 * (vacío = todo correcto). No tocan la base de datos ni generan HTML.
 */

const IMAGEN_MAX_BYTES = 2 * 1024 * 1024;   // 2 MB
const IMAGEN_TIPOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/** Comprueba la fortaleza de la contraseña. Devuelve los fallos encontrados. */
function validarFortalezaPassword(string $password): array
{
    $errores = [];
    if (mb_strlen($password) < 8) {
        $errores[] = 'The password must be at least 8 characters long.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = 'The password must include a lowercase letter.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'The password must include an uppercase letter.';
    }
    if (!preg_match('/\d/', $password)) {
        $errores[] = 'The password must include a number.';
    }
    if (strlen($password) > 72) {
        // bcrypt (el algoritmo por defecto) solo usa los primeros 72 bytes
        $errores[] = 'The password cannot exceed 72 characters.';
    }
    return $errores;
}

/** Valida los datos del formulario de registro (sin consultar la BDD). */
function validarRegistro(string $username, string $email, string $password, string $password2): array
{
    $errores = [];

    if ($username === '') {
        $errores['username'] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errores['username'] = 'Username must be 3 to 30 characters: letters, numbers and underscore.';
    }

    if ($email === '') {
        $errores['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errores['email'] = 'The email is not valid.';
    }

    if ($password === '') {
        $errores['password'] = 'Password is required.';
    } else {
        $fallos = validarFortalezaPassword($password);
        if ($fallos) {
            $errores['password'] = implode(' ', $fallos);
        }
    }

    if ($password2 === '') {
        $errores['password_confirm'] = 'Repeat the password.';
    } elseif ($password !== $password2) {
        $errores['password_confirm'] = 'The passwords do not match.';
    }

    return $errores;
}

/** Valida los campos del formulario de login. */
function validarLogin(string $identificador, string $password): array
{
    $errores = [];
    if ($identificador === '') {
        $errores['identifier'] = 'Enter your username or email.';
    }
    if ($password === '') {
        $errores['password'] = 'Enter your password.';
    }
    return $errores;
}

/** Valida título (nombre), descripción y VA de un personaje. */
function validarPersonaje(string $titol, string $cos, string $va): array
{
    $errores = [];
    if ($titol === '' || mb_strlen($titol) > 100) {
        $errores['titol'] = 'Name is required (maximum 100 characters).';
    }
    if ($cos === '') {
        $errores['cos'] = 'Description is required.';
    } elseif (mb_strlen($cos) > 5000) {
        $errores['cos'] = 'Description is too long (maximum 5000 characters).';
    }
    if ($va === '' || mb_strlen($va) > 100) {
        $errores['va'] = 'VA is required (maximum 100 characters).';
    }
    return $errores;
}

/**
 * Valida una imagen subida ($_FILES['imatge']). La imagen es opcional.
 * Devuelve un mensaje de error o null si está bien (o no se ha subido nada).
 * El tipo se comprueba por el CONTENIDO real (finfo), no por la extensión que manda el cliente.
 */
function validarImagenSubida(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'The image exceeds the maximum size (2 MB).';
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return 'The image could not be uploaded.';
    }
    if ($archivo['size'] > IMAGEN_MAX_BYTES) {
        return 'The image exceeds the maximum size (2 MB).';
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset(IMAGEN_TIPOS[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
        return 'The image must be JPG, PNG or WEBP.';
    }
    return null;
}
