<?php
/*
    gestionCiudades.php - Procesa las operaciones sobre ciudades (ETAPA 3)

    Archivo intermedio SIN HTML (igual que procesando.php pero para ciudades).

    Recibe del formulario (formCiudad.php) o de la tabla (ciudades.php) una "accion":
    - "crear"      -> INSERT de una ciudad nueva
    - "actualizar" -> UPDATE de una ciudad existente
    - "eliminar"   -> DELETE de una ciudad (verificando que no tenga personas)

    Despues de cada operacion redirige a ciudades.php.
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // La accion viene en un campo oculto del formulario
    $accion = $_POST['accion'];

    // =============================================
    // CASO 1: ELIMINAR una ciudad
    // =============================================
    if ($accion === 'eliminar') {
        $id = (int) $_POST['id'];

        // IMPORTANTE: si hay personas que nacieron en esta ciudad, la clave foranea
        // no permite eliminarla. Por eso lo verificamos antes.
        if (hayPersonasEnCiudad($conexion, $id)) {
            // Redirigimos con ?error=1 para que ciudades.php muestre el mensaje
            header("Location: ciudades.php?error=1");
            exit;
        }

        eliminarCiudad($conexion, $id);
        header("Location: ciudades.php");
        exit;
    }

    // =============================================
    // CASO 2: CREAR o ACTUALIZAR una ciudad
    // (ambos usan los mismos campos del formulario)
    // =============================================

    // Los campos opcionales (latitud, longitud, descripcion, fecha) si estan vacios
    // se guardan como NULL en la base de datos.
    $latitud  = ($_POST['latitud']  !== '') ? $_POST['latitud']  : null;
    $longitud = ($_POST['longitud'] !== '') ? $_POST['longitud'] : null;

    // =============================================
    // VALIDACION DE LATITUD Y LONGITUD
    // =============================================
    // La validacion es OBLIGATORIA porque si mandamos a MySQL un valor que no cabe
    // en la columna, se produce el error 500 "Out of range value".
    // Rangos reales: latitud -90 a +90, longitud -180 a +180.
    $error = null;
    if ($latitud !== null && (!is_numeric($latitud) || $latitud < -90 || $latitud > 90)) {
        $error = 'La latitud debe ser un numero entre -90 y 90 (ej: -31.416667)';
    } elseif ($longitud !== null && (!is_numeric($longitud) || $longitud < -180 || $longitud > 180)) {
        $error = 'La longitud debe ser un numero entre -180 y 180 (ej: -64.183333)';
    }

    // Si hay error de validacion, volvemos al formulario mostrando el mensaje
    if ($error) {
        header("Location: formCiudad.php?error=" . urlencode($error));
        exit;
    }

    $datos = [
        'nombre'           => trim($_POST['nombre']),
        'provincia'        => trim($_POST['provincia']),
        'latitud'          => $latitud,
        'longitud'         => $longitud,
        'codigo_postal'    => trim($_POST['codigo_postal']),
        'descripcion'      => trim($_POST['descripcion']),
        'fecha_fundacion'  => ($_POST['fecha_fundacion'] !== '') ? $_POST['fecha_fundacion'] : null,
    ];

    if ($accion === 'actualizar') {
        // UPDATE: modificamos la ciudad con el id que viene en el campo oculto
        $id = (int) $_POST['id'];
        actualizarCiudad($conexion, $id, $datos);
    } else {
        // INSERT: creamos una ciudad nueva
        insertarCiudad($conexion, $datos);
    }

    header("Location: ciudades.php");
    exit;
}
