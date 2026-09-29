<?php
/*
    gestionModulo.php - PLANTILLA del archivo que guarda y borra

    Este archivo NO funciona tal cual: es una PLANTILLA.
    No tiene HTML (el usuario nunca lo ve): recibe los datos del formulario,
    los guarda en MySQL y redirige al listado.

    Recibe una acción del formulario:
    - "crear"     -> INSERT
    - "actualizar" -> UPDATE
    - "eliminar"  -> DELETE

    PATRÓN A SEGUIR EN CADA CASO:
        1. Tomar el dato del POST
        2. Llamar a la función de lib/bd/modulos-bd.php
        3. header("Location: viewModulo.php"); exit;
*/

// 1. Librerías
require_once '../../lib/bd/gestionBaseDatos.php';

// 2. Conexión
$conexion = obtenerConexion();

// 3. Solo seguimos si los datos llegaron por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: viewModulo.php");
    exit;
}

// 4. La acción viene en un campo oculto del formulario
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// =====================================================
// CASO 1: ELIMINAR
// =====================================================
if ($accion === 'eliminar') {
    $id = (int) $_POST['id']; // (int) por seguridad
    eliminarModulo($conexion, $id);

    header("Location: viewModulo.php");
    exit;
}

// =====================================================
// CASO 2: CREAR o ACTUALIZAR
// =====================================================

// Si no llegaron los campos obligatorios, volvemos al formulario
if (!isset($_POST['nombre'])) {
    header("Location: editModulo.php");
    exit;
}

// Armamos el array con los datos del formulario (trim() saca espacios)
$datos = [
    'nombre'      => trim($_POST['nombre']),
    'descripcion' => trim($_POST['descripcion']),
];

if ($accion === 'actualizar') {
    actualizarModulo($conexion, (int) $_POST['id'], $datos);
} else {
    insertarModulo($conexion, $datos);
}

// Volvemos al listado para ver el resultado
header("Location: viewModulo.php");
exit;
