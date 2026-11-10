# Umamusume: Pretty Derby · Pt04

**Autor:** Raul Rubio

Web de personajes (characters) con registro, login y gestión de contenido propio.
Frontend con **Tailwind CSS (CDN)**; backend en **PHP + PDO + MySQL**.

## Puesta en marcha

1. Importar `sql/pt04_Raul_Rubio.sql` en phpMyAdmin (crea la BDD `pt04_raul_rubio` y las tablas).
2. *(Opcional)* Importar `sql/pt04_Raul_Rubio_dades_prova.sql`: crea el usuario `trainer` (contraseña `Trainer123`) y 10 personajes de ejemplo.
3. Servir la carpeta con Apache/XAMPP (usuario `root`, sin contraseña, como pide el enunciado). Abrir `index.php`.
4. La carpeta `uploads/characters/` necesita permiso de escritura (las imágenes que suben los usuarios).

## Qué hace

| Requisito del enunciado | Cómo está resuelto |
|---|---|
| Home pública, sin opciones de insertar/editar/borrar | `index.php` lista todos los personajes a cualquiera. Los botones *Edit/Delete* solo se pintan si `usuari_id` coincide con el usuario logueado. |
| Opción de logarse o registrarse en la Home | Botón *Login/Register* en la cabecera (cambia por *New character / usuario / Logout* al iniciar sesión). |
| Login y registro contra la BDD | `login.php`, `register.php` y `controlador/auth.php`. Se puede entrar con usuario **o** email. |
| Comprobar si el usuario ya existe | `existeUsername()` / `existeEmail()` antes de insertar, y además `UNIQUE` en la BDD por si dos registros llegan a la vez (se captura el error 23000). |
| Contraseña pedida dos veces | Campo *Repeat password* y comprobación de que coinciden. |
| Fortaleza de la contraseña | Mínimo 8 caracteres, con mayúscula, minúscula y número (`validarFortalezaPassword()`). Máximo 72 (límite de bcrypt). |
| Contraseña encriptada | `password_hash()` al registrar y `password_verify()` al entrar. Nunca se guarda en claro. |
| Mantener datos del formulario si hay error | Se devuelven usuario y email. **Las contraseñas no se devuelven al HTML a propósito**: reescribirlas en el `value` las expone en el código fuente de la página. |
| Sesión activa al menos 40 min | Sesión de **45 min de inactividad**, renovada en cada petición (`config/sesion.php`). |
| Editar solo los posts propios | Doble control: la vista solo muestra los botones al autor, el controlador comprueba la autoría y las consultas `UPDATE`/`DELETE` incluyen `AND usuari_id = :u`. Aunque alguien forzara la URL, el SQL no tocaría posts ajenos. |
| Paginación (de la Pt03) | 10 por página (2 filas × 5, como el diseño), página por defecto 1, `Anterior`/`Següent` deshabilitados en los extremos, y dos `div` (`#articles` y `#paginacio`). Funciona junto con el buscador. |
| Funciones | Toda la lógica está en funciones (`modelo/`, `controlador/`, `config/`). |
| Git | Repositorio Git. Cada fichero lleva mi nombre y el código está comentado. |

## Sesiones y cookies: cuándo uso cada una

- **Sesión (servidor)** para todo lo que no debe poder tocar el usuario: quién está logueado, el token CSRF y los mensajes de aviso. Solo viaja un identificador opaco en la cookie `PHPSESSID`
  (`HttpOnly`, `SameSite=Lax`, y `Secure` si hay HTTPS). Al iniciar sesión se regenera el id (evita *session fixation*).
- **Cookie (navegador)** solo para algo no sensible que debe sobrevivir al cierre de sesión: `ultimo_usuario` guarda el nombre de usuario 30 días para rellenar el login.
  **Nunca** se guarda la contraseña ni nada que dé acceso a la cuenta en una cookie.

## Base de datos (`pt04_raul_rubio`)

- `usuaris` (`id`, `username` UNIQUE, `email` UNIQUE, `password_hash`, `data_registre`)
- `articles` (`id`, `usuari_id` FK → `usuaris.id` con `ON DELETE CASCADE`, `titol`, `cos`, `va`, `imatge`, `data_creacio`)

Un *article* es un personaje: `titol` = nombre, `cos` = descripción. `va` (doblador) e `imatge` son campos extra que pedía el diseño.

## Seguridad (decisiones)

- **Inyección SQL:** todas las consultas son Prepared Statements con `ATTR_EMULATE_PREPARES = false`. Los comodines `%` y `_` del buscador se escapan.
- **XSS:** todo lo que viene de usuarios o BDD se imprime con `e()` (`htmlspecialchars`).
- **CSRF:** token en todos los formularios POST. Login, registro, crear, editar, borrar y logout lo comprueban. Borrar y logout solo aceptan POST (un enlace no puede borrar nada).
- **Subida de imágenes:** se valida el contenido real con `finfo` + `getimagesize` (no la extensión del cliente), máximo 2 MB, solo JPG/PNG/WEBP, nombre aleatorio, y `uploads/.htaccess` prohíbe ejecutar PHP en esa carpeta. Al borrar o reemplazar un personaje se borra su imagen anterior, pero nunca las de `assets/`.
- **Login:** mismo mensaje si falla el usuario o la contraseña, para no revelar qué usuarios existen.

## Estructura

```
index.php, login.php, register.php, logout.php   → páginas (solo vista + llamada al controlador)
personaje.php, personaje_form.php, personaje_borrar.php
config/       conexion.php (PDO) · sesion.php (sesión, cookies, flash, CSRF)
modelo/       usuarios.php · personajes.php (consultas SQL)
controlador/  auth.php · personajes.php · validacions.php (lógica)
includes/     head, header, footer, logo, social, flash, helpers, bootstrap (piezas de vista)
sql/          esquema y datos de prueba
uploads/      imágenes subidas por usuarios
```

## Pendiente / no incluido

- Recuperación de contraseña (opcional en el enunciado): no implementada.
- Los botones de redes sociales del diseño son decorativos.
- El enlace *Fan letters* de la cabecera es un marcador, aún sin página.
