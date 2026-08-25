<?php
/*
    procesando.php - Archivo intermedio SIN HTML (ETAPA 3)

    Igual que en la etapa 2, este archivo no se ve en el navegador.
    Recibe los datos del formulario y:
    1. Inserta una persona (con su ciudad_id) en la base de datos
    2. O elimina una persona existente
    3. Redirige a resultado.php
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CASO 1: ELIMINAR una persona
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $id = (int) $_POST['id'];
        eliminarPersona($conexion, $id);
        header("Location: resultado.php");
        exit;
    }

    // CASO 2: INSERTAR una nueva persona
    $datos = [
        'nombre'           => trim($_POST['nombre']),
        'apellido'         => trim($_POST['apellido']),
        'dni'              => trim($_POST['dni']),
        'cuit'             => trim($_POST['cuit']),
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'email'            => trim($_POST['email']),
        'telefono'         => trim($_POST['telefono']),
        'direccion'        => trim($_POST['direccion']),

        // NUEVO EN ETAPA 3:
        // En vez de guardar un texto, guardamos el ID de la ciudad donde nacio.
        // (int) convierte el valor a numero entero por seguridad.
        'ciudad_id'        => (int) $_POST['ciudad_id'],

        'observaciones'    => trim($_POST['observaciones']),
    ];

    insertarPersona($conexion, $datos);
    header("Location: resultado.php");
    exit;
}
