<?php
/**
 * Autor: Raul Rubio - Pt04
 * oauth_callback.php
 * URL de retorno de Google (la "Authorized redirect URI" registrada en Google Cloud Console).
 */
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/controlador/oauth.php';

exigirAnonimo();
procesarCallbackGoogle();
