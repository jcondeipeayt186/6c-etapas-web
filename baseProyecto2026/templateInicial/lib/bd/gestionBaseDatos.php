<?php
/*
    lib/bd/gestionBaseDatos.php - La CONEXIÓN con MySQL (archivo "puente")

    Este archivo es el único que sabe CÓMO conectarse a la base de datos.
    Todas las demás librerías (personas-bd.php, ciudades-bd.php) solo
    saben hacer consultas SQL, no se preocupan por el usuario ni la contraseña.

    Además lee el archivo .env de la raíz del proyecto para tomar de ahí
    los datos de configuración:

        DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_CHARSET

    ¿Por qué un .env?
    - Para no escribir la contraseña de MySQL dentro del código PHP
    - Para poder cambiar de base o de servidor sin tocar el código
    - Si alguien más usa el proyecto, solo cambia SU .env (ver .env.example)

    Al final del archivo hacemos require de las librerías de cada módulo,
    así las páginas solo necesitan incluir ESTE archivo y ya tienen
    disponibles las funciones de personas y de ciudades.
*/

// La función cargarEnv() está en lib/utils/varios.php porque la usan
// tanto la base de datos (acá) como el envío de mails (lib/utils/mail.php)
require_once __DIR__ . '/../utils/varios.php';

// Librerías de cada módulo (tabla personas y tabla ciudades)
require_once __DIR__ . '/personas-bd.php';
require_once __DIR__ . '/ciudades-bd.php';

/**
 * Establece la conexión con MySQL usando PDO.
 *
 * PDO (PHP Data Objects) es la forma recomendada de conectarse a una base
 * de datos en PHP. Permite usar "prepared statements" con ? que protegen
 * contra la inyección SQL.
 *
 * Inyección SQL: si concatenamos texto del usuario dentro del SQL, un
 * atacante puede mandar  ' OR '1'='1  y alterar la consulta.
 * Con ? + execute([...]) los datos viajan separados y nunca se mezclan.
 *
 * @return PDO Objeto de conexión listo para usar
 */
function obtenerConexion() {
    // 1. Cargamos el archivo .env de la raíz del proyecto (2 carpetas arriba: lib/bd -> lib -> raíz)
    cargarEnv(__DIR__ . '/../../.env');

    // 2. Leemos los datos del .env con la función valorEntorno($nombre, $valorPorDefecto)
    //    $_ENV es el array donde cargarEnv() guarda las variables del .env
    $host     = valorEntorno('DB_HOST', 'localhost');
    $base     = valorEntorno('DB_NAME', 'contactos3');
    $usuario  = valorEntorno('DB_USER', 'root');
    $clave    = valorEntorno('DB_PASS', '');
    $puerto   = valorEntorno('DB_PORT', '3306');
    $charset  = valorEntorno('DB_CHARSET', 'utf8mb4');

    // 3. Armamos el DSN: es la "dirección" de la base de datos
    //    (DSN = Data Source Name, el nombre que le damos a los datos de conexión)
    $dsn = "mysql:host=$host;port=$puerto;dbname=$base;charset=$charset";

    try {
        // 4. Creamos la conexión PDO
        $conexion = new PDO($dsn, $usuario, $clave);

        // 5. Configuramos PDO para que avise los errores como excepciones
        //    ( así es más fácil ver qué pasó si algo sale mal )
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 6. Con esta línea los resultados de las consultas vienen como
        //    arrays asociativos: $fila['nombre'] en lugar de $fila[1]
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $conexion;

    } catch (PDOException $e) {
        // Si no se puede conectar, mostramos el error y frenamos el script
        die("Error de conexion: " . $e->getMessage()
            . " (revisá el archivo .env y que la base de datos exista: ejecutá lib/bd/script.sql)");
    }
}
