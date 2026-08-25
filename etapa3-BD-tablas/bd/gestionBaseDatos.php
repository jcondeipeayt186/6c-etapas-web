<?php
/*
    gestionBaseDatos.php - Capa de acceso a datos (DAO) - ETAPA 3

    Este archivo contiene TODAS las funciones que interactuan con MySQL,
    para las DOS tablas: ciudades y personas.

    Diferencias con la etapa 2:
    - Ahora hay funciones para CIUDADES (crear, leer, actualizar, eliminar = CRUD)
    - La tabla personas ahora tiene una CLAVE FORANEA (ciudad_id) que apunta a ciudades
    - La consulta de personas usa un JOIN para obtener el nombre de la ciudad
    - Funciones de BUSQUEDA por nombre (personas y ciudades)
    - Funciones de CONTEO (para las estadisticas de la portada index.php)
*/

/**
 * Establece la conexion con MySQL usando PDO.
 * PDO es la forma segura de conectarse a bases de datos en PHP.
 *
 * @return PDO Objeto de conexion listo para usar
 */
function obtenerConexion() {
    // Datos de conexion a MySQL - CAMBIAR segun tu instalacion
    $host = "localhost";
    $usuario = "root";
    $contrasena = "";
    $baseDatos = "contactos3";  // Base de datos nueva (la de la etapa 3)

    try {
        $conexion = new PDO(
            "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexion: " . $e->getMessage());
    }
}

/* ===================================================== */
/*  FUNCIONES DE PERSONAS                                */
/* ===================================================== */

/**
 * Inserta una nueva persona en la tabla personas.
 * Nota: en vez de guardar la ciudad como texto, guardamos ciudad_id
 * que es la CLAVE FORANEA que apunta a la tabla ciudades.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param array $datos Array con los datos de la persona (incluye ciudad_id)
 * @return bool true si inserto correctamente
 */
function insertarPersona($conexion, $datos) {
    // Los ? son placeholders que luego se reemplazan por valores reales de forma segura
    // OJO: ya NO se guarda codigo_postal en personas (se movio a la tabla ciudades)
    $sql = "INSERT INTO personas (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['apellido'],
        $datos['dni'],
        $datos['cuit'],
        $datos['fecha_nacimiento'],
        $datos['email'],
        $datos['telefono'],
        $datos['direccion'],
        $datos['ciudad_id'],    // ID de la ciudad donde nacio (clave foranea)
        $datos['observaciones']
    ]);

    return $resultado;
}

/**
 * Obtiene las personas, junto con el nombre y provincia de su ciudad.
 * Acepta una busqueda opcional por NOMBRE de la persona.
 *
 * JOIN: permite combinar datos de dos tablas.
 * "personas p JOIN ciudades c ON p.ciudad_id = c.id"
 * significa: unir cada persona con la ciudad cuyo id coincide con su ciudad_id.
 *
 * LIKE: se usa para buscar textos que CONTENGAN el termino buscado.
 * '%' es un comodin: '%jul%' matchea "Julian", "Julia", etc.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param string $busqueda Termino de busqueda (filtra por nombre de persona)
 * @return array Array de personas con datos de la ciudad incluidos
 */
function obtenerTodasLasPersonas($conexion, $busqueda = '') {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia
            FROM personas p
            JOIN ciudades c ON p.ciudad_id = c.id";

    // Si hay termino de busqueda, agregamos un WHERE con LIKE
    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);

    // fetchAll trae todos los registros como arrays asociativos
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Elimina una persona por su ID.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la persona
 * @return bool true si elimino correctamente
 */
function eliminarPersona($conexion, $id) {
    $sql = "DELETE FROM personas WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Cuenta cuantas personas hay en total.
 * Se usa en la portada (index.php) para mostrar la estadistica.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @return int Cantidad total de personas
 */
function contarPersonas($conexion) {
    $sql = "SELECT COUNT(*) FROM personas";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // fetchColumn devuelve el valor del COUNT
    return $stmt->fetchColumn();
}

/* ===================================================== */
/*  FUNCIONES DE CIUDADES (CRUD completo)                */
/* ===================================================== */

/**
 * Inserta una nueva ciudad en la tabla ciudades.
 * Incluye codigo_postal: el codigo postal pertenece a la CIUDAD,
 * no a la persona que nacio en ella.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param array $datos Array con los datos de la ciudad
 * @return bool true si inserto correctamente
 */
function insertarCiudad($conexion, $datos) {
    $sql = "INSERT INTO ciudades (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],
        $datos['longitud'],
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion']
    ]);

    return $resultado;
}

/**
 * Obtiene las ciudades ordenadas por nombre.
 * Acepta una busqueda opcional por NOMBRE de la ciudad.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param string $busqueda Termino de busqueda (filtra por nombre de ciudad)
 * @return array Array de ciudades
 */
function obtenerTodasLasCiudades($conexion, $busqueda = '') {
    $sql = "SELECT * FROM ciudades";

    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY nombre ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene UNA ciudad por su ID.
 * Se usa para cargar los datos en el formulario de edicion (formCiudad.php).
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la ciudad
 * @return array|null La ciudad encontrada o null si no existe
 */
function obtenerCiudadPorId($conexion, $id) {
    $sql = "SELECT * FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);

    // fetch trae UN solo registro (a diferencia de fetchAll que trae todos)
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Actualiza los datos de una ciudad existente.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la ciudad a modificar
 * @param array $datos Array con los datos nuevos
 * @return bool true si actualizo correctamente
 */
function actualizarCiudad($conexion, $id, $datos) {
    $sql = "UPDATE ciudades
            SET nombre = ?, provincia = ?, latitud = ?, longitud = ?, codigo_postal = ?, descripcion = ?, fecha_fundacion = ?
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],
        $datos['longitud'],
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion'],
        $id
    ]);

    return $resultado;
}

/**
 * Elimina una ciudad por su ID.
 * ATENCION: la clave foranea de personas impide eliminar una ciudad
 * que tenga personas. Por eso, antes de eliminar, se debe verificar
 * con la funcion hayPersonasEnCiudad().
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si elimino correctamente
 */
function eliminarCiudad($conexion, $id) {
    $sql = "DELETE FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Cuenta cuantas personas nacieron en una ciudad.
 * Si el resultado es mayor a 0, NO se puede eliminar la ciudad
 * porque la clave foranea lo impide.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si hay personas nacidas en esa ciudad
 */
function hayPersonasEnCiudad($conexion, $id) {
    $sql = "SELECT COUNT(*) FROM personas WHERE ciudad_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);

    // fetchColumn devuelve el primer valor de la primera fila (el COUNT)
    $cantidad = $stmt->fetchColumn();
    return $cantidad > 0;
}

/**
 * Cuenta cuantas ciudades hay en total.
 * Se usa en la portada (index.php) para mostrar la estadistica.
 *
 * @param PDO $conexion Conexion a la base de datos
 * @return int Cantidad total de ciudades
 */
function contarCiudades($conexion) {
    $sql = "SELECT COUNT(*) FROM ciudades";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}
