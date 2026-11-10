<?php
/**
 * Autor: Raul Rubio - Pt04
 * usuarios.php (modelo)
 * Consultas sobre la tabla `usuaris`. Todas con Prepared Statements.
 */

require_once __DIR__ . '/../config/conexion.php';

/** Crea un usuario. Devuelve su id. Lanza PDOException si username/email ya existen. */
function crearUsuario(string $username, string $email, string $passwordHash): int
{
    $pdo  = getConexion();
    $stmt = $pdo->prepare(
        'INSERT INTO usuaris (username, email, password_hash) VALUES (:username, :email, :hash)'
    );
    $stmt->execute([':username' => $username, ':email' => $email, ':hash' => $passwordHash]);
    return (int) $pdo->lastInsertId();
}

/** ¿Existe ya ese nombre de usuario? (la colación utf8mb4_unicode_ci ya ignora mayúsculas) */
function existeUsername(string $username): bool
{
    $stmt = getConexion()->prepare('SELECT 1 FROM usuaris WHERE username = :u');
    $stmt->execute([':u' => $username]);
    return (bool) $stmt->fetchColumn();
}

/** ¿Existe ya ese email? */
function existeEmail(string $email): bool
{
    $stmt = getConexion()->prepare('SELECT 1 FROM usuaris WHERE email = :e');
    $stmt->execute([':e' => $email]);
    return (bool) $stmt->fetchColumn();
}

/** Busca un usuario por username O email (el login admite ambos). */
function buscarUsuarioPorIdentificador(string $identificador): array|false
{
    $stmt = getConexion()->prepare(
        'SELECT id, username, email, password_hash FROM usuaris WHERE username = :i1 OR email = :i2 LIMIT 1'
    );
    $stmt->execute([':i1' => $identificador, ':i2' => $identificador]);
    return $stmt->fetch();
}
