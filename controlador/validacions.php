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
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = 'La contraseña debe incluir una minúscula.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'La contraseña debe incluir una mayúscula.';
    }
    if (!preg_match('/\d/', $password)) {
        $errores[] = 'La contraseña debe incluir un número.';
    }
    if (strlen($password) > 72) {
        // bcrypt (el algoritmo por defecto) solo usa los primeros 72 bytes
        $errores[] = 'La contraseña no puede superar los 72 caracteres.';
    }
    return $errores;
}

/** Valida los datos del formulario de registro (sin consultar la BDD). */
function validarRegistro(string $username, string $email, string $password, string $password2): array
{
    $errores = [];

    if ($username === '') {
        $errores['username'] = 'El nombre de usuario es obligatorio.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errores['username'] = 'Usuario de 3 a 30 caracteres: letras, números y guion bajo.';
    }

    if ($email === '') {
        $errores['email'] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $errores['email'] = 'El email no es válido.';
    }

    if ($password === '') {
        $errores['password'] = 'La contraseña es obligatoria.';
    } else {
        $fallos = validarFortalezaPassword($password);
        if ($fallos) {
            $errores['password'] = implode(' ', $fallos);
        }
    }

    if ($password2 === '') {
        $errores['password_confirm'] = 'Repite la contraseña.';
    } elseif ($password !== $password2) {
        $errores['password_confirm'] = 'Las contraseñas no coinciden.';
    }

    return $errores;
}

/** Valida los campos del formulario de login. */
function validarLogin(string $identificador, string $password): array
{
    $errores = [];
    if ($identificador === '') {
        $errores['identifier'] = 'Introduce tu usuario o email.';
    }
    if ($password === '') {
        $errores['password'] = 'Introduce tu contraseña.';
    }
    return $errores;
}

/** Valida título (nombre), descripción y VA de un personaje. */
function validarPersonaje(string $titol, string $cos, string $va): array
{
    $errores = [];
    if ($titol === '' || mb_strlen($titol) > 100) {
        $errores['titol'] = 'El nombre es obligatorio (máximo 100 caracteres).';
    }
    if ($cos === '') {
        $errores['cos'] = 'La descripción es obligatoria.';
    } elseif (mb_strlen($cos) > 5000) {
        $errores['cos'] = 'La descripción es demasiado larga (máximo 5000 caracteres).';
    }
    if ($va === '' || mb_strlen($va) > 100) {
        $errores['va'] = 'El VA es obligatorio (máximo 100 caracteres).';
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
        return 'La imagen supera el tamaño máximo (2 MB).';
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return 'No se ha podido subir la imagen.';
    }
    if ($archivo['size'] > IMAGEN_MAX_BYTES) {
        return 'La imagen supera el tamaño máximo (2 MB).';
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset(IMAGEN_TIPOS[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
        return 'La imagen debe ser JPG, PNG o WEBP.';
    }
    return null;
}
