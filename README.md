# Umamusume: Pretty Derby · Pt04

**Autor:** Raul Rubio

Web de personajes (characters) con registro, login y gestión de contenido propio.
Frontend con **Tailwind CSS (CDN)**; backend en **PHP + PDO + MySQL**.

## Puesta en marcha

1. Importar `sql/pt04_Raul_Rubio.sql` en phpMyAdmin (crea la BDD `pt04_raul_rubio` y las tablas).
2. *(Opcional)* Importar `sql/pt04_Raul_Rubio_dades_prova.sql`: crea el usuario `trainer` (contraseña `Trainer123`) y 10 personajes de ejemplo.
3. Servir la carpeta con Apache/XAMPP (usuario `root`, sin contraseña, como pide el enunciado). Abrir `index.php`.
4. La carpeta `uploads/characters/` necesita permiso de escritura (las imágenes que suben los usuarios).
5. *(Login con Google)* Copiar `.env.example` como `.env` y poner el `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET` (ver [Login con Google](#login-con-google-oauth-20)). Sin `.env` la web funciona igual; solo el botón de Google avisa de que no está configurado.

**Requisitos:** PHP 8 con las extensiones `pdo_mysql`, `mbstring` y `fileinfo` (XAMPP las trae activadas) y MySQL/MariaDB.
**Se necesita conexión a internet**: Tailwind CSS y la tipografía (Google Fonts) se cargan por CDN.
Las imágenes de los personajes de prueba están en `assets/img/characters/` (incluidas en el repositorio). Solo se ignora el contenido de `uploads/characters/`, que son las imágenes que suben los usuarios mientras usan la web.

## Qué hace

| Requisito del enunciado | Cómo está resuelto |
|---|---|
| Home pública, sin opciones de insertar/editar/borrar | `index.php` lista todos los personajes a cualquiera. Los botones *Edit/Delete* solo se pintan si `usuari_id` coincide con el usuario logueado. |
| Opción de logarse o registrarse en la Home | Botón *Login/Register* en la cabecera (cambia por *New character / usuario / Logout* al iniciar sesión). |
| Login y registro contra la BDD | `login.php`, `register.php` y `controlador/auth.php`. Se puede entrar con usuario **o** email. |
| Login con OAuth | Botón de Google en login y registro (`oauth_google.php` → Google → `oauth_callback.php`). Flujo *Authorization Code* con `state` y PKCE, hecho a mano con cURL. |
| Comprobar si el usuario ya existe | `existeUsername()` / `existeEmail()` antes de insertar, y además `UNIQUE` en la BDD por si dos registros llegan a la vez (se captura el error 23000). |
| Contraseña pedida dos veces | Campo *Repeat password* y comprobación de que coinciden. |
| Fortaleza de la contraseña | Mínimo 8 caracteres, con mayúscula, minúscula y número (`validarFortalezaPassword()`). Máximo 72 (límite de bcrypt). |
| Contraseña encriptada | `password_hash()` al registrar y `password_verify()` al entrar. Nunca se guarda en claro. |
| Mantener datos del formulario si hay error | Se devuelven usuario y email. **Las contraseñas no se devuelven al HTML a propósito**: reescribirlas en el `value` las expone en el código fuente de la página. |
| Sesión activa al menos 40 min | Sesión de **45 min de inactividad**, renovada en cada petición (`config/sesion.php`). |
| Editar solo los posts propios | Doble control: la vista solo muestra los botones al autor, el controlador comprueba la autoría y las consultas `UPDATE`/`DELETE` incluyen `AND usuari_id = :u`. Aunque alguien forzara la URL, el SQL no tocaría posts ajenos. |
| Paginación (de la Pt03) | 10 por página (2 filas × 5, como el diseño), página por defecto 1, `Anterior`/`Següent` deshabilitados en los extremos, y dos `div` (`#articles` y `#paginacio`). Funciona junto con el buscador. |
| Formulario de crear / editar | `personaje_form.php`: nombre, VA, descripción e imagen principal, más los datos opcionales de *General info* (frase, cumpleaños, altura, peso) y la gestión de **outfits** (renombrar, cambiar imagen, borrar y añadir hasta 3 nuevos por envío, máximo 10 por personaje). Todo se guarda en una transacción; si algo falla, se deshacen los cambios y las imágenes recién subidas. |
| Detalle del personaje | `personaje.php`: carrusel de outfits accesible (botones reales, `aria-live`), VA, frase, General Info y descripción. |
| Funciones | Toda la lógica está en funciones (`modelo/`, `controlador/`, `config/`). |
| Git | Repositorio Git. Cada fichero lleva mi nombre y el código está comentado. |

## Login con Google (OAuth 2.0)

**Configuración (una vez):**
1. En [Google Cloud Console](https://console.cloud.google.com/) → *APIs & Services* → *OAuth consent screen*: tipo *External*, y añadir tu cuenta como *test user*.
2. *Credentials* → *Create credentials* → *OAuth client ID* → *Web application*.
   *Authorized redirect URI*: `http://localhost/ProjecteBackendDisseny/umamusume/oauth_callback.php` (tiene que ser idéntica a la de `.env`).
3. Copiar `.env.example` a `.env` y pegar el *Client ID* y el *Client secret*.

**Cómo funciona:**
1. `oauth_google.php` genera un `state` aleatorio y un `code_verifier` (PKCE), los guarda en la **sesión** y redirige a Google.
2. Google devuelve al usuario a `oauth_callback.php?code=...&state=...`. Si el `state` no coincide con el de la sesión, se rechaza (protección CSRF). El `state` solo vale para un intento.
3. El servidor cambia el `code` por un *access token* (con el `client_secret` y el `code_verifier`) y pide el perfil: `sub`, `email`, `email_verified`, nombre.
4. Con el perfil (`controlador/oauth.php`):
   - si ya hay un usuario con ese `google_id` → entra;
   - si hay una cuenta local con el mismo email **y Google dice que está verificado** → se vincula (`google_id`) y entra;
   - si no → se crea la cuenta con un *username* generado del nombre (`maria_jose`, `maria_jose2`...) y `password_hash = NULL`.
5. Se llama al mismo `loguearUsuario()` que el login normal, así que la sesión de 45 min, la regeneración del id y las cookies funcionan igual.

**Decisiones:**
- Sin librerías (Composer): así se ve el flujo completo y la práctica sigue funcionando con solo XAMPP.
- Las credenciales van en `.env` (en `.gitignore`), nunca en el código ni en Git. Solo se sube `.env.example` vacío.
- Las cuentas creadas con Google no tienen contraseña local: el login por formulario las rechaza con el mismo mensaje genérico.
- `CURLOPT_SSL_VERIFYPEER` activado: el servidor comprueba que habla con Google de verdad.

## Sesiones y cookies: cuándo uso cada una

- **Sesión (servidor)** para todo lo que no debe poder tocar el usuario: quién está logueado, el token CSRF y los mensajes de aviso. Solo viaja un identificador opaco en la cookie `PHPSESSID`
  (`HttpOnly`, `SameSite=Lax`, y `Secure` si hay HTTPS). Al iniciar sesión se regenera el id (evita *session fixation*).
- **Cookie (navegador)** solo para algo no sensible que debe sobrevivir al cierre de sesión: `ultimo_usuario` guarda el nombre de usuario 30 días para rellenar el login.
  **Nunca** se guarda la contraseña ni nada que dé acceso a la cuenta en una cookie.
- El `state` y el `code_verifier` de OAuth van en **sesión**: si fueran en cookie, el propio navegador podría leerlos o cambiarlos.

## Base de datos (`pt04_raul_rubio`)

- `usuaris` (`id`, `username` UNIQUE, `email` UNIQUE, `password_hash` (NULL en cuentas creadas con Google), `google_id` UNIQUE (NULL si nunca ha entrado con Google), `data_registre`)
- `articles` (`id`, `usuari_id` FK → `usuaris.id` con `ON DELETE CASCADE`, `titol`, `cos`, `va`, `imatge`, `data_creacio`, y opcionales: `height`, `frase`, `anniversary`, `weight`)
- `outfits` (`id`, `article_id` FK → `articles.id` con `ON DELETE CASCADE`, `nom`, `imatge`, `ordre`): los trajes que se ven en el carrusel de `personaje.php`

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
oauth_google.php, oauth_callback.php             → inicio y retorno del login con Google
personaje.php, personaje_form.php, personaje_borrar.php
config/       conexion.php (PDO) · sesion.php (sesión, cookies, flash, CSRF) · oauth.php (.env y llamadas a Google)
modelo/       usuarios.php · personajes.php (consultas SQL)
controlador/  auth.php · oauth.php · personajes.php · validacions.php (lógica)
.env.example  plantilla de credenciales de Google (el .env real no se sube)
includes/     head, header, footer, logo, social, flash, helpers, bootstrap (piezas de vista)
sql/          esquema y datos de prueba
uploads/      imágenes subidas por usuarios
```

## Pendiente / no incluido

- Recuperación de contraseña (opcional en el enunciado): no implementada.
- De los botones de redes sociales solo funciona Google; Reddit, Facebook y X son decorativos.
- El enlace *Fan letters* de la cabecera es un marcador, aún sin página.
