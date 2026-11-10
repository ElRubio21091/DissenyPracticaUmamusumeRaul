<?php
/**
 * Autor: Raul Rubio - Pt04
 * logout.php
 * Cierra la sesión (solo acepta POST con token CSRF).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/auth.php';

procesarLogout();
