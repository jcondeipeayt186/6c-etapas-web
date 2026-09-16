<?php
/*
    auth.php - Helpers de autenticación y autorización

    Funciones:
    - iniciarSesion(): asegura session_start()
    - estaLogueado()
    - requerirLogin(): redirige a /index.php si no hay sesión
    - usuarioTieneRolTipo(): verifica si el usuario actual tiene algún tipo permitido
    - obtenerUsuarioActual()
*/

if (session_status() === PHP_SESSION_NONE) {
    // se inicia al incluir, pero también las páginas lo hacen explícito
}

function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function estaLogueado() {
    iniciarSesion();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function requerirLogin() {
    iniciarSesion();
    if (!estaLogueado()) {
        header("Location: ../../index.php?error=login_requerido");
        // Si estamos en root, probar absoluto
        if (!headers_sent()) {
            // intentar calcular ruta relativa
        }
        exit;
    }
}

/**
 * Requiere login con redirección configurable
 */
function requerirLoginRedir($redir = '../../index.php') {
    iniciarSesion();
    if (!estaLogueado()) {
        header("Location: $redir?error=login_requerido");
        exit;
    }
}

function obtenerUsuarioActual() {
    iniciarSesion();
    if (!estaLogueado()) return null;
    return [
        'id' => $_SESSION['usuario_id'],
        'username' => $_SESSION['username'] ?? '',
        'roles' => $_SESSION['roles'] ?? [],
        'tipos' => $_SESSION['tipos'] ?? []
    ];
}

function tieneTipo($tiposPermitidos) {
    iniciarSesion();
    if (!estaLogueado()) return false;
    $tipos = $_SESSION['tipos'] ?? [];
    foreach ($tiposPermitidos as $t) {
        if (in_array((int)$t, array_map('intval', $tipos))) return true;
    }
    return false;
}

function esAdmin() { return tieneTipo([1]); }
function esDirectivoOAdmin() { return tieneTipo([1,2]); }

/**
 * Carga roles/tipos desde BD y los guarda en sesión
 * Llamar después de login o al iniciar cada request si se quiere refrescar
 */
function refrescarRolesEnSesion($conexion, $usuarioId) {
    iniciarSesion();
    require_once __DIR__ . '/../bd/usuario_roles.php';
    $roles = obtenerRolesDeUsuario($conexion, $usuarioId);
    $_SESSION['roles'] = $roles;
    $_SESSION['tipos'] = array_unique(array_column($roles, 'tipo'));
    $_SESSION['roles_nombres'] = array_column($roles, 'nombre');
}

function cerrarSesion() {
    iniciarSesion();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
