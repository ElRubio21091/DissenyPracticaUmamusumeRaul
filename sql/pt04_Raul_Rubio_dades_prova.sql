-- Raul Rubio - Pt04: datos de prueba (OPCIONAL). Importar DESPUES de pt04_Raul_Rubio.sql.
-- Usuario de prueba: trainer / trainer@example.com / contraseña: Trainer123
SET NAMES utf8mb4;
USE `pt04_raul_rubio`;

INSERT INTO `usuaris` (`id`,`username`,`email`,`password_hash`) VALUES
(1,'trainer','trainer@example.com','$2y$10$ZEj7FpFeJYBd6hgEOvxfZ.y.ECn23x9iBNE3mBBoSTpyeVYnP43MG');

INSERT INTO `articles` (`usuari_id`,`titol`,`cos`,`va`,`imatge`) VALUES
(1,'Copano Rickey','Descripcion de prueba de Copano Rickey. Editala desde la web si eres su autor.','Komoni Inagaki','assets/img/characters/copano-rickey.png'),
(1,'Matikanefukukitaru','Descripcion de prueba de Matikanefukukitaru. Editala desde la web si eres su autor.','Hiyori Nitta','assets/img/characters/matikanefukukitaru.png'),
(1,'Marvelous Sunday','Descripcion de prueba de Marvelous Sunday. Editala desde la web si eres su autor.','Marie Miyake','assets/img/characters/marvelous-sunday.png'),
(1,'Grass Wonder','Descripcion de prueba de Grass Wonder. Editala desde la web si eres su autor.','Rena Maeda','assets/img/characters/grass-wonder.png'),
(1,'El Condor Pasa','Descripcion de prueba de El Condor Pasa. Editala desde la web si eres su autor.','Minami Takahashi','assets/img/characters/el-condor-pasa.png'),
(1,'Tamamo Cross','Descripcion de prueba de Tamamo Cross. Editala desde la web si eres su autor.','Naomi Ozora','assets/img/characters/tamamo-cross.png'),
(1,'Mayano Top Gun','Descripcion de prueba de Mayano Top Gun. Editala desde la web si eres su autor.','Mio Hoshitani','assets/img/characters/mayano-top-gun.png'),
(1,'Twin Turbo','Descripcion de prueba de Twin Turbo. Editala desde la web si eres su autor.','Miharu Hanai','assets/img/characters/twin-turbo.png'),
(1,'Yamanin Zephyr','Descripcion de prueba de Yamanin Zephyr. Editala desde la web si eres su autor.','Riona Imaizumi','assets/img/characters/yamanin-zephyr.png'),
(1,'Shinko Windy','Descripcion de prueba de Shinko Windy. Editala desde la web si eres su autor.','Yuuki Takada','assets/img/characters/shinko-windy.png');
