<?php
/**
 * Autor: Raul Rubio - Pt04
 * auth.php (controlador)
 * Lógica de registro, login y logout. Las vistas (login.php / register.php)
 * solo llaman a estas funciones y pintan el resultado.
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../modelo/usuarios.php';
require_once __DIR__ . '/validacions.php';

/**
 * Procesa el registro. Devuelve el estado que necesita la vista:
 *  ['errores' => [...], 'datos' => ['username' => ..., 'email' => ...]]
 * Si todo va bien, redirige y no vuelve.
 * Los datos NO sensibles se devuelven para repoblar el formulario si hay errores.
 */
function procesarRegistro(): array
{
    $estado = ['errores' => [], 'datos' => ['username' => '', 'email' => '']];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $estado;
    }

    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password_confirm'] ?? '';

    $estado['datos'] = ['username' => $username, 'email' => $email];

    if (!csrfValido()) {
        $estado['errores']['general'] = 'Formulario caducado. Inténtalo de nuevo.';
        return $estado;
    }

    $errores = validarRegistro($username, $email, $password, $password2);

    // Comprobamos que el usuario no exista ya (solo si el formato es correcto)
    try {
        if (!isset($errores['username']) && existeUsername($username)) {
            $errores['username'] = 'Ese nombre de usuario ya está en uso.';
        }
        if (!isset($errores['email']) && existeEmail($email)) {
            $errores['email'] = 'Ese email ya está registrado.';
        }

        if (!$errores) {
            // La contraseña se guarda cifrada (hash con sal incluida), nunca en claro
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $id = crearUsuario($username, $email, $hash);
            } catch (PDOException $e) {
                // 23000 = violación de UNIQUE: dos registros simultáneos con el mismo dato
                if ($e->getCode() === '23000') {
                    $estado['errores']['general'] = 'Ese usuario o email ya existe.';
                    return $estado;
                }
                throw $e;
            }
            loguearUsuario(['id' => $id, 'username' => $username]);
            flash('ok', '¡Bienvenido, ' . $username . '! Tu cuenta se ha creado.');
            redirigir('index.php');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errores['general'] = 'No se ha podido completar el registro. Inténtalo más tarde.';
    }

    $estado['errores'] = $errores;
    return $estado;
}

/**
 * Procesa el login. Devuelve ['errores' => [...], 'identificador' => '...'].
 * Si las credenciales son correctas, redirige y no vuelve.
 */
function procesarLogin(): array
{
    // Si hay cookie con el último usuario, se usa para rellenar el campo
    $estado = [
        'errores'       => [],
        'identificador' => $_COOKIE[COOKIE_ULTIMO_USUARIO] ?? '',
    ];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $estado;
    }

    $identificador = trim($_POST['identifier'] ?? '');
    $password      = $_POST['password'] ?? '';
    $estado['identificador'] = $identificador;

    if (!csrfValido()) {
        $estado['errores']['general'] = 'Formulario caducado. Inténtalo de nuevo.';
        return $estado;
    }

    $errores = validarLogin($identificador, $password);

    if (!$errores) {
        try {
            $usuario = buscarUsuarioPorIdentificador($identificador);
            // Mismo mensaje si falla el usuario o la contraseña: no revelamos cuál de los dos es
            if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
                $errores['general'] = 'Usuario o contraseña incorrectos.';
            } else {
                loguearUsuario($usuario);
                flash('ok', 'Sesión iniciada. ¡Hola, ' . $usuario['username'] . '!');
                redirigir('index.php');
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errores['general'] = 'No se ha podido iniciar sesión. Inténtalo más tarde.';
        }
    }

    $estado['errores'] = $errores;
    return $estado;
}

/** Procesa el logout (solo por POST con CSRF válido). */
function procesarLogout(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValido()) {
        cerrarSesion();
        // Tras destruir la sesión hay que abrir otra para poder dejar un mensaje
        iniciarSesion();
        flash('ok', 'Has cerrado sesión.');
    }
    redirigir('index.php');
}
