-- Raul Rubio - Pt04 (Umamusume: Pretty Derby)
-- Esquema de la BDD (sin datos). Importar a nivel de servidor en phpMyAdmin.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `pt04_raul_rubio`;
CREATE DATABASE IF NOT EXISTS `pt04_raul_rubio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pt04_raul_rubio`;

-- --------------------------------------------------------
-- Tabla `usuaris`: usuarios registrados
-- --------------------------------------------------------
CREATE TABLE `usuaris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,          -- resultado de password_hash(), nunca la contraseña en claro
  `data_registre` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla `articles`: los "posts" (characters)
--   titol = nombre del personaje, cos = descripcion
--   height (cm), frase (frase del personaje), anniversary (cumpleaños, texto libre)
--   y weight (texto libre) se muestran en la pantalla de detalle; todos son opcionales
-- --------------------------------------------------------
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuari_id` int(11) NOT NULL,                   -- autor del post
  `titol` varchar(100) NOT NULL,
  `cos` text NOT NULL,
  `va` varchar(100) NOT NULL,                     -- voice actor
  `imatge` varchar(255) DEFAULT NULL,             -- ruta relativa de la imagen (puede ser NULL)
  `data_creacio` timestamp NOT NULL DEFAULT current_timestamp(),
  `height` smallint(5) unsigned DEFAULT NULL,     -- altura en cm
  `frase` varchar(255) DEFAULT NULL,
  `anniversary` varchar(30) DEFAULT NULL,         -- cumpleaños, p. ej. "May 22"
  `weight` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuari` (`usuari_id`),
  KEY `idx_titol` (`titol`),
  CONSTRAINT `fk_articles_usuaris` FOREIGN KEY (`usuari_id`) REFERENCES `usuaris` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla `outfits`: los trajes de cada personaje (diapositivas del carrusel del detalle)
--   ordre = posición en el carrusel (de menor a mayor)
-- --------------------------------------------------------
CREATE TABLE `outfits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,                    -- nombre del traje, p. ej. "Default racewear"
  `imatge` varchar(255) NOT NULL,                 -- ruta relativa de la imagen
  `ordre` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_article` (`article_id`),
  CONSTRAINT `fk_outfits_articles` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
