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

/** Outfits de un personaje, en el orden indicado por `ordre` (para el carrusel del detalle). */
function obtenerOutfits(int $articleId): array
{
    $stmt = getConexion()->prepare(
        'SELECT id, nom, imatge FROM outfits WHERE article_id = :id ORDER BY ordre ASC, id ASC'
    );
    $stmt->execute([':id' => $articleId]);
    return $stmt->fetchAll();
}

/** Valor de texto vacío -> NULL (las columnas opcionales de la BDD admiten NULL). */
function textoONull(string $valor): ?string
{
    return $valor === '' ? null : $valor;
}

/**
 * Inserta un personaje. $extra trae frase, anniversary, height y weight (opcionales, '' = sin dato).
 * Devuelve el id generado.
 */
function crearPersonaje(int $usuarioId, string $titol, string $cos, string $va, ?string $imatge, array $extra): int
{
    $pdo  = getConexion();
    $stmt = $pdo->prepare(
        'INSERT INTO articles (usuari_id, titol, cos, va, imatge, frase, anniversary, height, weight)
         VALUES (:u, :titol, :cos, :va, :imatge, :frase, :anniversary, :height, :weight)'
    );
    $stmt->execute([
        ':u' => $usuarioId, ':titol' => $titol, ':cos' => $cos, ':va' => $va, ':imatge' => $imatge,
        ':frase' => textoONull($extra['frase']), ':anniversary' => textoONull($extra['anniversary']),
        ':height' => $extra['height'] === '' ? null : (int) $extra['height'], ':weight' => textoONull($extra['weight']),
    ]);
    return (int) $pdo->lastInsertId();
}

/**
 * Modifica un personaje SOLO si pertenece al usuario.
 * Si $imatge es null no se toca la imagen actual. Los campos de $extra se guardan siempre
 * (así se puede vaciar un dato dejando el campo en blanco).
 */
function modificarPersonaje(int $id, int $usuarioId, string $titol, string $cos, string $va, ?string $imatge, array $extra): bool
{
    $sql = 'UPDATE articles SET titol = :titol, cos = :cos, va = :va,
                   frase = :frase, anniversary = :anniversary, height = :height, weight = :weight'
         . ($imatge !== null ? ', imatge = :imatge' : '')
         . ' WHERE id = :id AND usuari_id = :u';
    $params = [
        ':titol' => $titol, ':cos' => $cos, ':va' => $va, ':id' => $id, ':u' => $usuarioId,
        ':frase' => textoONull($extra['frase']), ':anniversary' => textoONull($extra['anniversary']),
        ':height' => $extra['height'] === '' ? null : (int) $extra['height'], ':weight' => textoONull($extra['weight']),
    ];
    if ($imatge !== null) {
        $params[':imatge'] = $imatge;
    }
    $stmt = getConexion()->prepare($sql);
    $stmt->execute($params);
    // rowCount() es 0 si no hubo cambios; la propiedad ya se comprobó antes en el controlador
    return true;
}

/**
 * Borra un personaje SOLO si pertenece al usuario. true si se borró.
 * También borra sus outfits explícitamente (por si la BDD no tuviera el ON DELETE CASCADE).
 */
function borrarPersonaje(int $id, int $usuarioId): bool
{
    $pdo = getConexion();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('DELETE FROM articles WHERE id = :id AND usuari_id = :u');
        $stmt->execute([':id' => $id, ':u' => $usuarioId]);
        $borrado = $stmt->rowCount() > 0;
        if ($borrado) {
            $pdo->prepare('DELETE FROM outfits WHERE article_id = :id')->execute([':id' => $id]);
        }
        $pdo->commit();
        return $borrado;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Siguiente posición libre en el carrusel de un personaje. */
function siguienteOrdreOutfit(int $articleId): int
{
    $stmt = getConexion()->prepare('SELECT COALESCE(MAX(ordre), 0) + 1 FROM outfits WHERE article_id = :id');
    $stmt->execute([':id' => $articleId]);
    return (int) $stmt->fetchColumn();
}

/** Añade un outfit a un personaje. */
function crearOutfit(int $articleId, string $nom, string $imatge, int $ordre): void
{
    $stmt = getConexion()->prepare(
        'INSERT INTO outfits (article_id, nom, imatge, ordre) VALUES (:a, :nom, :imatge, :ordre)'
    );
    $stmt->execute([':a' => $articleId, ':nom' => $nom, ':imatge' => $imatge, ':ordre' => $ordre]);
}

/** Cambia el nombre (y la imagen, si $imatge no es null) de un outfit del personaje indicado. */
function modificarOutfit(int $id, int $articleId, string $nom, ?string $imatge): void
{
    $sql = 'UPDATE outfits SET nom = :nom' . ($imatge !== null ? ', imatge = :imatge' : '')
         . ' WHERE id = :id AND article_id = :a';
    $params = [':nom' => $nom, ':id' => $id, ':a' => $articleId];
    if ($imatge !== null) {
        $params[':imatge'] = $imatge;
    }
    getConexion()->prepare($sql)->execute($params);
}

/** Borra un outfit del personaje indicado. */
function borrarOutfit(int $id, int $articleId): void
{
    getConexion()->prepare('DELETE FROM outfits WHERE id = :id AND article_id = :a')
        ->execute([':id' => $id, ':a' => $articleId]);
}

/** Escapa % y _ para que el usuario no pueda usarlos como comodines en el LIKE. */
function escaparLike(string $texto): string
{
    return addcslashes($texto, '%_\\');
}
