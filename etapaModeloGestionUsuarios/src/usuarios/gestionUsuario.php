<?php
// gestionUsuario.php - Procesa crear/actualizar/eliminar usuarios (solo POST)
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/usuarios.php';
require_once '../../lib/bd/usuario_roles.php';

$conexion = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)$_SESSION['usuario_id']) {
        header("Location: viewUsuario.php?error=" . urlencode("No puedes eliminarte a ti mismo"));
        exit;
    }
    eliminarUsuario($conexion, $id);
    header("Location: viewUsuario.php?msg=eliminado");
    exit;
}

if ($accion === 'actualizar') {
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roles = $_POST['roles'] ?? [];

    if ($username === '') {
        header("Location: editUsuario.php?id=$id&error=" . urlencode("Username obligatorio"));
        exit;
    }
    if (existeUsername($conexion, $username, $id)) {
        header("Location: editUsuario.php?id=$id&error=" . urlencode("Username ya existe"));
        exit;
    }
    // password opcional en edición; si viene vacío no cambia
    $pwd = ($password !== '') ? $password : null;
    if ($pwd !== null && strlen($pwd) < 4) {
        header("Location: editUsuario.php?id=$id&error=" . urlencode("Clave debe tener al menos 4 caracteres"));
        exit;
    }
    actualizarUsuario($conexion, $id, $username, $pwd);
    // sincronizar roles
    $rolesIds = array_map('intval', $roles);
    sincronizarRolesUsuario($conexion, $id, $rolesIds);
    // si edita su propio usuario, refrescar sesión
    if ($id === (int)$_SESSION['usuario_id']) {
        $rolesActuales = obtenerRolesDeUsuario($conexion, $id);
        $_SESSION['roles'] = $rolesActuales;
        $_SESSION['tipos'] = array_unique(array_column($rolesActuales, 'tipo'));
        $_SESSION['username'] = $username;
    }
    header("Location: viewUsuario.php?msg=actualizado");
    exit;
}

// crear
if (isset($_POST['username'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roles = $_POST['roles'] ?? [];

    if ($username === '' || $password === '') {
        header("Location: editUsuario.php?error=" . urlencode("Username y clave son obligatorios"));
        exit;
    }
    if (strlen($password) < 4) {
        header("Location: editUsuario.php?error=" . urlencode("Clave debe tener al menos 4 caracteres"));
        exit;
    }
    if (existeUsername($conexion, $username)) {
        header("Location: editUsuario.php?error=" . urlencode("Username ya existe"));
        exit;
    }
    $id = insertarUsuario($conexion, $username, $password);
    $rolesIds = array_map('intval', $roles);
    sincronizarRolesUsuario($conexion, $id, $rolesIds);
    header("Location: viewUsuario.php?msg=creado");
    exit;
}

header("Location: editUsuario.php");
exit;
