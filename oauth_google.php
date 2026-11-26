<?php
/**
 * Autor: Raul Rubio - Pt04
 * oauth_google.php
 * Botón "Continue with Google": redirige a la pantalla de login de Google.
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/oauth.php';

exigirAnonimo();
iniciarLoginGoogle();
