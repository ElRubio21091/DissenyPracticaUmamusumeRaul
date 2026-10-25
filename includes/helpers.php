<?php
/** ¿Existe la imagen? Si falta, las pantallas muestran un placeholder. */
function asset_exists(string $relative): bool
{
    return is_file(dirname(__DIR__) . '/' . ltrim($relative, '/'));
}
