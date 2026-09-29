<?php
/*
    gestionPersona.php - Procesa las operaciones sobre personas

    ¿Navegable? NO. Este archivo no tiene HTML: el usuario nunca lo ve.
    Es el que se encarga de recibir los datos del formulario, guardarlos
    en MySQL y después redirigir al listado.

    Recibe una "accion" del formulario:
    - "crear"     -> INSERT (persona nueva)
    - "actualizar" -> UPDATE (persona existente)
    - "eliminar"  -> DELETE

    FLUJO:
    1. El usuario llena editPersona.php o aprieta un botón de viewPersona.php
    2. Los datos llegan ACÁ por POST
    3. Se guardan en la base usando las funciones de lib/bd/personas-bd.php
    4. Redirigimos a viewPersona.php para que se vea la tabla actualizada
*/

// Librerías: base de datos (conexión + funciones de personas) y archivos adjuntos
require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/utils/archivos.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Solo hacemos algo si los datos llegaron por POST.
// Si alguien escribe esta dirección en el navegador, no pasa nada.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// La acción viene en un campo oculto del formulario
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// =====================================================
// CASO 1: ELIMINAR una persona
// =====================================================
if ($accion === 'eliminar') {
    $id = (int) $_POST['id']; // (int) por seguridad

    // Antes de borrar, buscamos la persona para poder borrar también sus archivos
    $persona = obtenerPersonaPorId($conexion, $id);
    if ($persona) {
        borrarArchivo($persona['avatar_path']); // borra la foto del servidor
        borrarArchivo($persona['cv_path']);     // borra el currículum
    }

    eliminarPersona($conexion, $id); // DELETE FROM personas WHERE id = ?

    header("Location: viewPersona.php");
    exit;
}

// =====================================================
// CASO 2: CREAR o ACTUALIZAR una persona
// (los dos casos usan los mismos campos del formulario)
// =====================================================

// Si no llegaron los campos obligatorios, no hacemos nada y
// mandamos al usuario al formulario
if (!isset($_POST['nombre']) || !isset($_POST['apellido']) || !isset($_POST['dni'])) {
    header("Location: editPersona.php");
    exit;
}

// ---------------------------------------------------------------
// ARCHIVOS ADJUNTOS
// ---------------------------------------------------------------
// En el alta no hay archivo anterior, así que arranca en null (vacío).
// En la edición conservamos la ruta que ya tenía guardada
// (si el usuario no sube un archivo nuevo, se mantiene el viejo).
$avatarPath = !empty($_POST['avatar_actual']) ? $_POST['avatar_actual'] : null;
$cvPath     = !empty($_POST['cv_actual']) ? $_POST['cv_actual'] : null;

// Si se subió una foto nueva, se guarda en files/avatars
$avatarNuevo = guardarArchivoSubido(
    isset($_FILES['avatar']) ? $_FILES['avatar'] : null,
    'avatars',
    'avatar',
    array('jpg', 'jpeg', 'png', 'gif', 'webp')  // solo imágenes
);
if ($avatarNuevo !== null) {
    $avatarPath = $avatarNuevo;
}

// Guardamos el error por si el archivo de arriba tuvo problemas
$errores = obtenerUltimoErrorArchivo();

// Si se subió un currículum nuevo, se guarda en files/cv
$cvNuevo = guardarArchivoSubido(
    isset($_FILES['cv']) ? $_FILES['cv'] : null,
    'cv',
    'cv',
    array('pdf', 'doc', 'docx')  // solo documentos
);
if ($cvNuevo !== null) {
    $cvPath = $cvNuevo;
}

// Si el currículum no tuvo problemas, nos quedamos con el error anterior
if ($errores === '') {
    $errores = obtenerUltimoErrorArchivo();
}

// Si hubo algún problema con los archivos (tipo no permitido, muy grande...)
// volvemos al formulario mostrando el mensaje
if ($errores !== '') {
    if ($accion === 'actualizar') {
        header("Location: editPersona.php?id=" . (int) $_POST['id'] . "&error=" . urlencode($errores));
    } else {
        header("Location: editPersona.php?error=" . urlencode($errores));
    }
    exit;
}

// ---------------------------------------------------------------
// DATOS DE LA PERSONA
// ---------------------------------------------------------------
// Armamos un array con los campos del formulario.
// trim() saca espacios al principio y al final.
// (int) en ciudad_id porque es el número de la ciudad elegida.
$datos = [
    'nombre'           => trim($_POST['nombre']),
    'apellido'         => trim($_POST['apellido']),
    'dni'              => trim($_POST['dni']),
    'cuit'             => trim($_POST['cuit']),
    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
    'email'            => trim($_POST['email']),
    'telefono'         => trim($_POST['telefono']),
    'direccion'        => trim($_POST['direccion']),
    'ciudad_id'        => ($_POST['ciudad_id'] !== '') ? (int) $_POST['ciudad_id'] : null,
    'avatar_path'      => $avatarPath,
    'cv_path'          => $cvPath,
    'observaciones'    => trim($_POST['observaciones']),
];

if ($accion === 'actualizar') {
    actualizarPersona($conexion, (int) $_POST['id'], $datos); // UPDATE ... WHERE id = ?
} else {
    insertarPersona($conexion, $datos); // INSERT INTO personas ...
}

// Volvemos al listado para que el usuario vea el resultado
header("Location: viewPersona.php");
exit;
