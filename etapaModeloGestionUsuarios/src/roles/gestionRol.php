<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/roles.php';

$conexion = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../../index.php"); exit; }

$accion = $_POST['accion'] ?? '';

if ($accion === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if (hayUsuariosEnRol($conexion, $id)) {
        header("Location: viewRol.php?error=" . urlencode("No se puede eliminar: hay usuarios con este rol. Quite el rol de los usuarios primero."));
        exit;
    }
    eliminarRol($conexion, $id);
    header("Location: viewRol.php?msg=eliminado");
    exit;
}

if ($accion === 'actualizar') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = (int)($_POST['tipo'] ?? 3);
    if ($nombre === '') { header("Location: editRol.php?id=$id&error=" . urlencode("Nombre obligatorio")); exit; }
    if (existeRolNombre($conexion, $nombre, $id)) { header("Location: editRol.php?id=$id&error=" . urlencode("Ya existe un rol con ese nombre")); exit; }
    if ($tipo <1 || $tipo>127) { header("Location: editRol.php?id=$id&error=" . urlencode("Tipo fuera de rango")); exit; }
    actualizarRol($conexion, $id, $nombre, $descripcion, $tipo);
    header("Location: viewRol.php?msg=actualizado");
    exit;
}

// crear
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$tipo = (int)($_POST['tipo'] ?? 3);
if ($nombre === '') { header("Location: editRol.php?error=" . urlencode("Nombre obligatorio")); exit; }
if (existeRolNombre($conexion, $nombre)) { header("Location: editRol.php?error=" . urlencode("Ya existe un rol con ese nombre")); exit; }
insertarRol($conexion, $nombre, $descripcion, $tipo);
header("Location: viewRol.php?msg=creado");
exit;
