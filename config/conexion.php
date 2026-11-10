<?php
/**
 * Autor: Raul Rubio - Pt04
 * conexion.php
 * Crea y devuelve la conexión PDO. Se reutiliza en todas las consultas.
 */

function getConexion(): PDO
{
    // Estática: la conexión se abre una sola vez por petición
    static $conexion = null;

    if ($conexion === null) {
        try {
            // Nombre de la BDD en minúsculas (case-sensitive: pt04 <> Pt04)
            $conexion = new PDO(
                'mysql:host=localhost;dbname=pt04_raul_rubio;charset=utf8mb4',
                'root',
                ''
            );
            // Excepciones ante errores SQL, para que funcione el try...catch
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Resultados como array asociativo
            $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Prepared statements reales (no emulados)
            $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            // Detalle técnico al log, mensaje genérico a pantalla
            error_log('Error de conexión: ' . $e->getMessage());
            die('No se ha podido conectar con la base de datos.');
        }
    }

    return $conexion;
}
