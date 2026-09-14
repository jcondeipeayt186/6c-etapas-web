<?php
/*
    gestionBaseDatos.php - Capa de acceso a datos (DAO)

    Este archivo contiene TODAS las funciones que interactuan con MySQL.
    Es como un "intermediario" entre nuestro PHP y la base de datos.

    POR QUE separar esto en otro archivo?
    - Reutilizacion: cualquier pagina puede incluir este archivo y usar sus funciones
    - Orden: la logica de BD queda centralizada en un solo lugar
    - Mantenimiento: si cambia la conexion o una consulta, se cambia en UN solo lugar
*/

/**
 * Establece la conexion con MySQL usando PDO.
 *
 * PDO (PHP Data Objects) es la forma segura de conectarse a bases de datos en PHP.
 * Permite usar "prepared statements" que protegen contra inyeccion SQL.
 * 
 * ¿Que es inyeccion SQL? Es cuando un usuario malintencionado envia datos que alteran la consulta SQL y pueden borrar o robar informacion.
 * 
 * ¿Como harian para enviar datos maliciosos en una consulta SQL? Si concatenamos directamente los datos del usuario en la consulta, por ejemplo:
 * $sql = "SELECT * FROM personas WHERE dni = '" . $dni . "'";
 * Si el usuario envia como dni: 123' OR '1'='1, la consulta se vuelve:
 * SELECT * FROM personas WHERE dni = '123' OR '1'='1'
 * Esto devuelve todos los registros de la tabla, porque '1'='1' siempre es verdadero. Un atacante podria usar esto para borrar registros, robar datos, etc.
 * Con prepared statements, los datos del usuario se envian por separado y nunca se mezclan con la consulta SQL
 * 
 * 
 * @return PDO Objeto de conexion listo para usar
 */
function obtenerConexion() {
    // Datos de conexion a MySQL - CAMBIAR segun tu instalacion
    $host = "localhost";       // Servidor (generalmente localhost)
    $usuario = "jconde";         // Usuario de MySQL
    $contrasena = "jc2021";          // Contrasena de MySQL (vacio por defecto en XAMPP)
    $baseDatos = "contactos2";  // Nombre de la base de datos

    try {
        // Creamos la conexion PDO con manejo de errores mediante try/catch
        $conexion = new PDO(
            "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );

        // Configuramos PDO para que lance excepciones ante errores
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conexion;

    } catch (PDOException $e) {
        // Si hay error de conexion, mostramos el mensaje y detenemos el script
        die("Error de conexion: " . $e->getMessage());
    }
}

/**
 * Inserta una nueva persona en la tabla personas.
 *
 * Usa prepared statements para proteger contra inyeccion SQL.
 * Los placeholders (?) se reemplazan por los valores reales de forma segura.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param array $datos Array asociativo con los datos de la persona
 * @return bool true si inserto correctamente, false si hubo error
 */
function insertarPersona($conexion, $datos) {
    // La consulta SQL usa ? como placeholders (marcadores de posicion)
    // Cada ? sera reemplazado por el valor correspondiente del array $datos
    $sql = "INSERT INTO personas (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad, provincia, codigo_postal, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparamos la consulta (PDO la analiza pero no la ejecuta aun)
    $stmt = $conexion->prepare($sql);

    // bind_param equivalent: pasamos los valores en orden
    // execute() con un array une los valores a los placeholders
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['apellido'],
        $datos['dni'],
        $datos['cuit'],
        $datos['fecha_nacimiento'],
        $datos['email'],
        $datos['telefono'],
        $datos['direccion'],
        $datos['ciudad'],
        $datos['provincia'],
        $datos['codigo_postal'],
        $datos['observaciones']
    ]);

    //Tener en cuenta el orden de los campos en la consulta SQL y en el array de datos, deben coincidir exactamente.

    return $resultado;
}

/**
 * Obtiene TODAS las personas de la base de datos.
 *
 * fetchAll() trae todos los registros de una sola vez.
 * PDO::FETCH_ASSOC retorna cada fila como un array asociativo,
 * donde las claves son los nombres de las columnas de la tabla.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @return array Array de personas (cada elemento es un array asociativo)
 */
function obtenerTodasLasPersonas($conexion) {
    // ORDER BY id DESC: ordena de mayor a menor (los mas nuevos primero)
    $sql = "SELECT * FROM personas ORDER BY id DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // fetchAll(PDO::FETCH_ASSOC) retorna todos los registros como array asociativo
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene las personas que cumplan con el filtro de busqueda.
 * Acepta una busqueda opcional por NOMBRE de la persona.
 *

 * LIKE: se usa para buscar textos que CONTENGAN el termino buscado.
 * '%' es un comodin: '%jul%' matchea "Julian", "Julia", etc.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param string $busqueda Termino de busqueda (filtra por nombre de persona)
 * @return array Array de personas con datos de la ciudad incluidos
 */
function obtenerPersonasConFiltro($conexion, $busqueda = '') {
    // ORDER BY id DESC: ordena de mayor a menor (los mas nuevos primero)
    $sql = "SELECT * FROM personas p";

    // Si hay termino de busqueda, agregamos un WHERE con LIKE
    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);

    // fetchAll(PDO::FETCH_ASSOC) retorna todos los registros como array asociativo
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Elimina una persona de la base de datos por su ID.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la persona a eliminar
 * @return bool true si elimino correctamente
 */
function eliminarPersona($conexion, $id) {
    $sql = "DELETE FROM personas WHERE id = ?";
    $stmt = $conexion->prepare($sql);

    // El array [1 => $id] reemplaza el primer ? por el valor de $id
    $resultado = $stmt->execute([$id]);

    return $resultado;
}
