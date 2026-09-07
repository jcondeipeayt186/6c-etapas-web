<?php
/*
    gestionPersona.php - "El cerebro detrás de escena" para PERSONAS

    Este archivo NO tiene HTML, NO se ve en el navegador.
    Su trabajo es recibir los datos de los formularios, hacer la operación en MySQL
    y redirigir al usuario a viewPersona.php.

    Es una librería intermedia NO NAVEGABLE, igual que procesando.php de la Etapa 2.
    La diferencia es que ahora vive en src/persona/ y sabe hacer 3 operaciones:
    1. ELIMINAR una persona   (DELETE ... WHERE id = ?)
    2. ACTUALIZAR una persona  (UPDATE ... SET ... WHERE id = ?)  <- heredado de FiltroyUpdate
    3. CREAR una persona       (INSERT INTO ...)

    FLUJO:
    1. El usuario llena el formulario en editPersona.php (alta o edición)
    2. Los datos llegan ACÁ por POST (accion=crear / accion=actualizar / accion=eliminar)
    3. Este archivo los guarda/modifica/borra en MySQL usando las funciones de lib/bd/gestionBaseDatos.php
    4. Redirige a viewPersona.php para que el usuario vea la tabla actualizada
*/

// require_once incluye el archivo de conexión y funciones de la BD
// Si el archivo no existe o hay error, el programa se detiene (mejor que seguir con errores silenciosos)
require_once '../../lib/bd/gestionBaseDatos.php';

// Creamos la conexión a MySQL usando la función definida en gestionBaseDatos.php
$conexion = obtenerConexion();

// Verificamos que los datos llegaron por POST (método del formulario)
// Si alguien entra a este archivo escribiendo la URL directamente, no hay POST y no hace nada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // La acción viene en un campo oculto del formulario: crear / actualizar / eliminar
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    // =============================================
    // CASO 1: ELIMINAR una persona
    // =============================================
    // El botón de eliminar en viewPersona.php envía un campo "accion" con valor "eliminar" + el id
    if ($accion === 'eliminar') {
        // (int) convierte el id a número entero por seguridad (evita inyección si alguien manda texto)
        $id = (int) $_POST['id'];
        eliminarPersona($conexion, $id); // DELETE FROM personas WHERE id = ?

        // header("Location: ...") redirige al navegador a otra página
        // DESPUÉS de redirigir, SIEMPRE hay que usar exit para detener el script
        header("Location: viewPersona.php");
        exit;
    }

    // =============================================
    // CASO 2: ACTUALIZAR una persona existente
    // =============================================
    // El formulario de editPersona.php en modo edición envía accion=actualizar + id oculto + todos los campos
    if ($accion === 'actualizar') {
        $id = (int) $_POST['id'];

        // Verificamos que llegaron los campos obligatorios del formulario
        // Si faltan, no hacemos el UPDATE y volvemos al listado
        if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni'])) {
            // Armamos un array asociativo con todos los campos del formulario
            // trim() elimina espacios al inicio y al final ("  Juan  " → "Juan")
            // (int) para ciudad_id porque es clave foránea numérica
            $datos = [
                'nombre'           => trim($_POST['nombre']),
                'apellido'         => trim($_POST['apellido']),
                'dni'              => trim($_POST['dni']),
                'cuit'             => trim($_POST['cuit']),
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'email'            => trim($_POST['email']),
                'telefono'         => trim($_POST['telefono']),
                'direccion'        => trim($_POST['direccion']),
                // Normalización: en vez de texto libre, guardamos el ID de la ciudad elegida en el <select>
                'ciudad_id'        => ($_POST['ciudad_id'] !== '') ? (int) $_POST['ciudad_id'] : null,
                'observaciones'    => trim($_POST['observaciones']),
            ];

            // Llamamos a la función que ejecuta el UPDATE en la base de datos
            // UPDATE personas SET nombre=?, ... WHERE id=?
            actualizarPersona($conexion, $id, $datos);
        }

        header("Location: viewPersona.php");
        exit;
    }

    // =============================================
    // CASO 3: CREAR una persona nueva
    // =============================================
    // Si no era eliminar ni actualizar, asumimos que es un alta (accion=crear)
    // Verificamos que llegaron los campos obligatorios (evita que alguien entre directo a este archivo)
    if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni'])) {
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
            'observaciones'    => trim($_POST['observaciones']),
        ];

        // Llamamos a la función que ejecuta el INSERT en la base de datos
        insertarPersona($conexion, $datos);

        // Redirigimos a viewPersona.php para que el usuario vea la tabla con la nueva persona
        header("Location: viewPersona.php");
        exit;
    } else {
        // Si no llegaron los campos obligatorios, redirigimos al formulario
        header("Location: editPersona.php");
        exit;
    }
}

// Si no fue por POST (ej: acceso directo por GET), redirigimos a la portada
header("Location: ../../index.php");
exit;
