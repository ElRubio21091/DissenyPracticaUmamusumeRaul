<?php
/**
 * Autor: Raul Rubio - Pt04
 * helpers.php
 * Funciones pequeñas de apoyo para las vistas.
 */

/** ¿Existe la imagen? Si falta, las pantallas muestran un placeholder. */
function asset_exists(?string $relative): bool
{
    if ($relative === null || $relative === '') {
        return false;
    }
    return is_file(dirname(__DIR__) . '/' . ltrim($relative, '/'));
}

/** Escapa texto para mostrarlo en HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Campo oculto con el token CSRF para los formularios POST. */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(tokenCsrf()) . '">';
}

/** Pinta el mensaje de error de un campo (si lo hay) bajo el input. */
function errorCampo(array $errores, string $campo): string
{
    if (empty($errores[$campo])) {
        return '';
    }
    return '<p class="mt-1 px-3 text-xs font-bold text-red-600" role="alert">' . e($errores[$campo]) . '</p>';
}
