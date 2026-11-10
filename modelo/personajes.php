<?php
/**
 * Autor: Raul Rubio - Pt04
 * personajes.php (modelo)
 * Consultas sobre la tabla `articles` (cada artículo es un character).
 * Todas con Prepared Statements. Las operaciones de modificar y borrar
 * incluyen `usuari_id` en el WHERE: así la propiedad se comprueba en la propia consulta.
 */

require_once __DIR__ . '/../config/conexion.php';

/** Total de personajes; si hay búsqueda, solo los que coinciden con el nombre. */
function contarPersonajes(string $busqueda = ''): int
{
    $stmt = getConexion()->prepare('SELECT COUNT(*) FROM articles WHERE titol LIKE :q');
    $stmt->execute([':q' => '%' . escaparLike($busqueda) . '%']);
    return (int) $stmt->fetchColumn();
}

/**
 * Personajes de una página. LIMIT/OFFSET se enlazan como INT
 * (con prepares reales MySQL no acepta LIMIT '10').
 */
function listarPersonajesPaginado(string $busqueda, int $limite, int $desplazamiento): array
{
    $sql = 'SELECT id, usuari_id, titol, va, imatge
            FROM articles
            WHERE titol LIKE :q
            ORDER BY id ASC
            LIMIT :limite OFFSET :desplazamiento';
    $stmt = getConexion()->prepare($sql);
    $stmt->bindValue(':q', '%' . escaparLike($busqueda) . '%');
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':desplazamiento', $desplazamiento, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Un personaje con el nombre de su autor (o false si no existe). */
function obtenerPersonaje(int $id): array|false
{
    $stmt = getConexion()->prepare(
        'SELECT a.*, u.username AS autor
         FROM articles a JOIN usuaris u ON u.id = a.usuari_id
         WHERE a.id = :id'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/** Inserta un personaje. Devuelve el id generado. */
function crearPersonaje(int $usuarioId, string $titol, string $cos, string $va, ?string $imatge): int
{
    $pdo  = getConexion();
    $stmt = $pdo->prepare(
        'INSERT INTO articles (usuari_id, titol, cos, va, imatge)
         VALUES (:u, :titol, :cos, :va, :imatge)'
    );
    $stmt->execute([':u' => $usuarioId, ':titol' => $titol, ':cos' => $cos, ':va' => $va, ':imatge' => $imatge]);
    return (int) $pdo->lastInsertId();
}

/**
 * Modifica un personaje SOLO si pertenece al usuario.
 * Si $imatge es null no se toca la imagen actual.
 */
function modificarPersonaje(int $id, int $usuarioId, string $titol, string $cos, string $va, ?string $imatge): bool
{
    $sql = 'UPDATE articles SET titol = :titol, cos = :cos, va = :va'
         . ($imatge !== null ? ', imatge = :imatge' : '')
         . ' WHERE id = :id AND usuari_id = :u';
    $params = [':titol' => $titol, ':cos' => $cos, ':va' => $va, ':id' => $id, ':u' => $usuarioId];
    if ($imatge !== null) {
        $params[':imatge'] = $imatge;
    }
    $stmt = getConexion()->prepare($sql);
    $stmt->execute($params);
    // rowCount() es 0 si no hubo cambios; la propiedad ya se comprobó antes en el controlador
    return true;
}

/** Borra un personaje SOLO si pertenece al usuario. true si se borró. */
function borrarPersonaje(int $id, int $usuarioId): bool
{
    $stmt = getConexion()->prepare('DELETE FROM articles WHERE id = :id AND usuari_id = :u');
    $stmt->execute([':id' => $id, ':u' => $usuarioId]);
    return $stmt->rowCount() > 0;
}

/** Escapa % y _ para que el usuario no pueda usarlos como comodines en el LIKE. */
function escaparLike(string $texto): string
{
    return addcslashes($texto, '%_\\');
}
