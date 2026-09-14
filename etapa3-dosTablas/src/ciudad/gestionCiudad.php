<?php
/*
    gestionCiudad.php - Procesa las operaciones sobre ciudades (ETAPA 3 dosTablas)

    Archivo intermedio SIN HTML (no navegable), igual que gestionPersona.php pero para ciudades.

    Recibe del formulario (editCiudad.php) o de la tabla (viewCiudad.php) una "accion":
    - "crear"      -> INSERT de una ciudad nueva
    - "actualizar" -> UPDATE de una ciudad existente
    - "eliminar"   -> DELETE de una ciudad (verificando que no tenga personas por la FK)

    Después de cada operación redirige a viewCiudad.php (o vuelve al formulario si hay error de validación).
*/

require_once '../../lib/bd/gestionBaseDatos.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Solo procesamos si llegaron datos por POST (formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // La acción viene en un campo oculto del formulario: crear / actualizar / eliminar
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    // =============================================
    // CASO 1: ELIMINAR una ciudad
    // =============================================
    if ($accion === 'eliminar') {
        $id = (int) $_POST['id']; // (int) por seguridad

        // IMPORTANTE: si hay personas que nacieron en esta ciudad, la clave foránea
        // no permite eliminarla (FOREIGN KEY). Por eso lo verificamos antes.
        // hayPersonasEnCiudad() hace SELECT COUNT(*) FROM personas WHERE ciudad_id = ?
        if (hayPersonasEnCiudad($conexion, $id)) {
            // Redirigimos con ?error=1 para que viewCiudad.php muestre el cartel rojo
            header("Location: viewCiudad.php?error=1");
            exit;
        }

        // Si no hay personas, se puede borrar sin problemas
        eliminarCiudad($conexion, $id); // DELETE FROM ciudades WHERE id = ?
        header("Location: viewCiudad.php");
        exit;
    }

    // =============================================
    // CASO 2: CREAR o ACTUALIZAR una ciudad
    // (ambos usan los mismos campos del formulario)
    // =============================================

    // Los campos opcionales (latitud, longitud, descripción, fecha) si están vacíos
    // se guardan como NULL en la base de datos (no como string vacío)
    // El operador ternario: condición ? valorSiTrue : valorSiFalse
    $latitud  = (isset($_POST['latitud']) && $_POST['latitud']  !== '') ? $_POST['latitud']  : null;
    $longitud = (isset($_POST['longitud']) && $_POST['longitud'] !== '') ? $_POST['longitud'] : null;

    // =============================================
    // VALIDACIÓN DE LATITUD Y LONGITUD
    // =============================================
    // Es OBLIGATORIA porque si mandamos a MySQL un valor que no cabe
    // en la columna DECIMAL(9,6) / DECIMAL(10,6), se produce el error 500 "Out of range value".
    // Rangos reales del planeta: latitud -90 a +90, longitud -180 a +180.
    $error = null;
    if ($latitud !== null && (!is_numeric($latitud) || $latitud < -90 || $latitud > 90)) {
        $error = 'La latitud debe ser un número entre -90 y 90 (ej: -31.416667)';
    } elseif ($longitud !== null && (!is_numeric($longitud) || $longitud < -180 || $longitud > 180)) {
        $error = 'La longitud debe ser un número entre -180 y 180 (ej: -64.183333)';
    }

    // Si hay error de validación, volvemos al formulario mostrando el mensaje en la URL
    // Para edición, incluimos el id para que vuelva al formulario correcto
    if ($error) {
        // Si es actualización, volvemos a editCiudad.php?id=X con el error
        if ($accion === 'actualizar' && isset($_POST['id'])) {
            header("Location: editCiudad.php?id=" . (int)$_POST['id'] . "&error=" . urlencode($error));
        } else {
            // Si es creación, volvemos a editCiudad.php sin id
            header("Location: editCiudad.php?error=" . urlencode($error));
        }
        exit;
    }

    // Armamos el array de datos para INSERT o UPDATE
    // trim() saca espacios al inicio/final del texto
    $datos = [
        'nombre'           => trim($_POST['nombre']),
        'provincia'        => trim($_POST['provincia']),
        'latitud'          => $latitud,  // null o decimal
        'longitud'         => $longitud, // null o decimal
        'codigo_postal'    => trim($_POST['codigo_postal']),
        'descripcion'      => trim($_POST['descripcion']),
        'fecha_fundacion'  => (isset($_POST['fecha_fundacion']) && $_POST['fecha_fundacion'] !== '') ? $_POST['fecha_fundacion'] : null,
    ];

    if ($accion === 'actualizar') {
        // UPDATE: modificamos la ciudad con el id que viene en el campo oculto
        $id = (int) $_POST['id'];
        actualizarCiudad($conexion, $id, $datos); // UPDATE ciudades SET ... WHERE id = ?
    } else {
        // INSERT: creamos una ciudad nueva (accion=crear)
        insertarCiudad($conexion, $datos); // INSERT INTO ciudades (...) VALUES (...)
    }

    // Después de crear o actualizar, volvemos al listado de ciudades
    header("Location: viewCiudad.php");
    exit;
}

// Si no fue por POST (acceso directo por URL), redirigimos a la portada
header("Location: ../../index.php");
exit;
