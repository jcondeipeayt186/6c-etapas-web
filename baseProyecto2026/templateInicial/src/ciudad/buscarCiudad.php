<?php
/*
    buscarCiudad.php - Busca ciudades por nombre (para el combo del formulario)

    ¿Navegable? NO. Esta página no muestra nada: devuelve los datos en
    formato JSON y nada más.

    ¿Para qué sirve?
    Cuando hay muchas ciudades, en editPersona.php el usuario escribe el
    nombre de la ciudad en un <input type="text"> y este archivo le
    devuelve las ciudades que coinciden con lo que escribió.

    Se llama desde JavaScript (fetch) así:
        buscarCiudad.php?termino=Rio

    Respuesta (JSON): una lista de ciudades con id, nombre y provincia.
*/

// Librería de base de datos (conexión + funciones de ciudades)
require_once '../../lib/bd/gestionBaseDatos.php';

// Leemos el término que se está buscando desde la URL
$termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';

// Con menos de 3 letras no buscamos nada (si no, el usuario vería
// siempre las primeras 20 ciudades de la lista)
if (strlen($termino) < 3) {
    header('Content-Type: application/json; charset=utf-8');
    echo '[]';
    exit;
}

// Buscamos en la base (SELECT ... WHERE nombre LIKE 'termino%' LIMIT 20)
$conexion = obtenerConexion();
$ciudades = obtenerCiudadesPorNombre($conexion, $termino, 20);

// Le decimos al navegador que la respuesta es JSON
header('Content-Type: application/json; charset=utf-8');

// Convertimos el array de PHP a JSON
// JSON_UNESCAPED_UNICODE: deja las tildes como tildes (no como \u00ED)
echo json_encode($ciudades, JSON_UNESCAPED_UNICODE);
