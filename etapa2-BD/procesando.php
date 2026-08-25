<?php
/*
    ARCHIVO PROCESANDO.PHP - "El cerebro detras de escena"

    Este archivo NO tiene HTML, NO se ve en el navegador.
    Su trabajo es recibir los datos del formulario, guardarlos en la base de datos,
    y redirigir al usuario a resultado.php.

    FLUJO:
    1. El usuario llena el formulario en index.php
    2. Los datos llegan AQUI por POST
    3. Este archivo los guarda en MySQL
    4. Redirige a resultado.php para mostrar todos los registros
*/

// require_once incluye el archivo de conexion y funciones de la BD
// Si el archivo no existe o hay error, el programa se detiene
require_once 'bd/gestionBaseDatos.php';

// Creamos la conexion a MySQL usando la funcion que definimos en gestionBaseDatos.php
$conexion = obtenerConexion();

// Verificamos que los datos llegaron por POST (metodo del formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CASO 1: ELIMINAR una persona
    // El boton de eliminar en resultado.php envia un campo "accion" con valor "eliminar"
    // El operador === compara valor y tipo de dato, es mas seguro que == que solo compara valor
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        // (int) convierte el id a numero entero por seguridad
        $id = (int) $_POST['id'];
        eliminarPersona($conexion, $id);

        // header("Location: ...") redirige al navegador a otra pagina
        // DESPUES de hacer la redireccion, SIEMPRE hay que usar exit para detener el script
        header("Location: resultado.php");
        exit;
    }else if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni'])) {
        // CASO 2: INSERTAR una nueva persona   
        // Si llegaron los campos obligatorios del formulario, continuamos con el INSERT
        // (esto evita que alguien acceda a procesando.php directamente sin pasar por el formulario)
       
        // Armamos un array asociativo con todos los campos del formulario
        // trim() elimina espacios al inicio y al final de cada texto
        $datos = [
            'nombre'           => trim($_POST['nombre']),
            'apellido'         => trim($_POST['apellido']),
            'dni'              => trim($_POST['dni']),
            'cuit'             => trim($_POST['cuit']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'email'            => trim($_POST['email']),
            'telefono'         => trim($_POST['telefono']),
            'direccion'        => trim($_POST['direccion']),
            'ciudad'           => trim($_POST['ciudad']),
            'provincia'        => trim($_POST['provincia']),
            'codigo_postal'    => trim($_POST['codigo_postal']),
            'observaciones'    => trim($_POST['observaciones']),
        ];

        // Llamamos a la funcion que ejecuta el INSERT en la base de datos
        insertarPersona($conexion, $datos);

        // Redirigimos a resultado.php para que el usuario vea todos los registros
        header("Location: resultado.php");
        exit;
    } else {
        // Si no llegaron los campos obligatorios, redirigimos al usuario a index.php
        header("Location: index.php");
        // Aca podemos ver de agregar notificaciones al usuario de que faltan campos obligatorios, pero eso lo dejamos para mas adelante
        exit;
    }

    
}


/*
if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni']))

Cuando el usuario hace click en "Guardar" en index.php, los datos del formulario llegan a procesando.php por POST.
Si no viene el nombre, apellido o dni, significa que el usuario accedio a procesando.php directamente sin pasar por el formulario.
En ese caso, isset($_POST['nombre']), isset($_POST['apellido']) y/o isset($_POST['dni'])  devuelven false, y redirigimos al usuario a index.php para que llene el formulario.

El array asociativo $datos contiene todos los campos del formulario, y se pasa a la funcion insertarPersona() que hace el INSERT en la base de datos.
¿Como es un array asociativo? 
Es un array donde cada elemento tiene una clave asociada, como $datos['nombre'].
¿Como se agregan o consultan los datos de un array asociativo? 
Se agregan usando $datos['clave'] = valor; o con el operador => por ejemplo $datos = ['clave' => valor];
y se consultan usando $datos['clave'].

*/