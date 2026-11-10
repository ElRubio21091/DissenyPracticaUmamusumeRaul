<?php
/**
 * Autor: Raul Rubio - Pt04
 * personaje_borrar.php
 * Borra un personaje (solo POST, solo su autor). Sin vista: siempre redirige.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/personajes.php';

procesarBorradoPersonaje();
