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

/* ---------- Login con Google (OAuth) ---------- */

/** Busca el usuario vinculado a una cuenta de Google (por su "sub"). */
function buscarUsuarioPorGoogleId(string $googleId): array|false
{
    $stmt = getConexion()->prepare('SELECT id, username, email FROM usuaris WHERE google_id = :g LIMIT 1');
    $stmt->execute([':g' => $googleId]);
    return $stmt->fetch();
}

/** Busca un usuario solo por email (para vincular una cuenta local existente con Google). */
function buscarUsuarioPorEmail(string $email): array|false
{
    $stmt = getConexion()->prepare('SELECT id, username, email, google_id FROM usuaris WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    return $stmt->fetch();
}

/** Vincula una cuenta local existente con su cuenta de Google. */
function vincularGoogleId(int $usuarioId, string $googleId): void
{
    $stmt = getConexion()->prepare('UPDATE usuaris SET google_id = :g WHERE id = :id');
    $stmt->execute([':g' => $googleId, ':id' => $usuarioId]);
}

/**
 * Crea un usuario que entra con Google. No tiene contraseña local
 * (password_hash = NULL), así que solo podrá entrar con Google.
 */
function crearUsuarioGoogle(string $username, string $email, string $googleId): int
{
    $pdo  = getConexion();
    $stmt = $pdo->prepare(
        'INSERT INTO usuaris (username, email, password_hash, google_id) VALUES (:u, :e, NULL, :g)'
    );
    $stmt->execute([':u' => $username, ':e' => $email, ':g' => $googleId]);
    return (int) $pdo->lastInsertId();
}
