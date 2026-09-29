<?php
/*
    lib/bd/ciudades-bd.php - Consultas SQL de la tabla CIUDADES

    Mismo criterio que personas-bd.php: un archivo por módulo (una tabla).
    Acá viven todas las consultas de la tabla ciudades:

        lib/bd/gestionBaseDatos.php  -> la conexión
        lib/bd/personas-bd.php       -> tabla personas
        lib/bd/ciudades-bd.php       -> tabla ciudades  (este archivo)

    Tabla ciudades:
        id, nombre, provincia, latitud, longitud, codigo_postal,
        descripcion, fecha_fundacion
*/

// ------------------------------------------------------------------
// CREAR
// ------------------------------------------------------------------

/**
 * Inserta una ciudad nueva.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param array $datos Array con los datos de la ciudad
 * @return bool true si se insertó correctamente
 */
function insertarCiudad($conexion, $datos) {
    $sql = "INSERT INTO ciudades
                (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],       // puede ser null si no se completó
        $datos['longitud'],      // puede ser null
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion'], // puede ser null
    ]);
}

// ------------------------------------------------------------------
// LEER
// ------------------------------------------------------------------

/**
 * Obtiene las ciudades ordenadas por nombre, con un filtro opcional.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Texto a buscar en el nombre (opcional)
 * @return array Array de ciudades
 */
function obtenerTodasLasCiudades($conexion, $busqueda = '') {
    $sql = "SELECT * FROM ciudades";

    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY nombre ASC"; // orden alfabético

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

/**
 * Busca ciudades cuyo nombre empieza con el término escrito.
 *
 * Se usa en editPersona.php cuando hay MUCHAS ciudades: el usuario escribe
 * 3 caracteres o más y se le muestran solo las que coinciden.
 *
 * MySQL con utf8mb4 no distingue mayúsculas de minúsculas al comparar
 * (según la collation, tampoco suele distinguir tildes: "rio" encuentra
 * "Río Cuarto").
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $termino Texto buscado (mínimo 3 letras)
 * @param int $limite Cantidad máxima de resultados (para no traer 5000 ciudades)
 * @return array Array de ciudades
 */
function obtenerCiudadesPorNombre($conexion, $termino, $limite = 20) {
    $sql = "SELECT id, nombre, provincia
            FROM ciudades
            WHERE nombre LIKE ?
            ORDER BY nombre ASC
            LIMIT $limite"; // el ? del LIKE ya protege el texto

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$termino . '%']); // % al final = "empieza con"
    return $stmt->fetchAll();
}

/**
 * Obtiene UNA ciudad por su ID (se usa en el formulario de edición).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return array|null La ciudad encontrada o null si no existe
 */
function obtenerCiudadPorId($conexion, $id) {
    $stmt = $conexion->prepare("SELECT * FROM ciudades WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Cuenta cuántas ciudades hay en total (se usa en la portada).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @return int Cantidad total de ciudades
 */
function contarCiudades($conexion) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM ciudades");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/**
 * Cuenta cuántas personas nacieron en cada ciudad.
 *
 * Devuelve un array "id de ciudad" => "cantidad de personas".
 * Se usa en viewCiudad.php para mostrar una columna con ese número.
 *
 * LEFT JOIN: además de las ciudades, suma 0 a las que no tienen ninguna
 * persona (si solo contáramos las personas, esas ciudades no aparecerían).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @return array Array con el id de la ciudad y la cantidad de personas
 */
function contarPersonasPorCiudad($conexion) {
    $sql = "SELECT c.id, COUNT(p.id) AS cantidad
            FROM ciudades c
            LEFT JOIN personas p ON p.ciudad_id = c.id
            GROUP BY c.id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // Convertimos el resultado en un array asociativo id => cantidad
    // para poder consultarlo así: $conteos[$ciudad['id']]
    $conteos = [];
    foreach ($stmt->fetchAll() as $fila) {
        $conteos[$fila['id']] = (int) $fila['cantidad'];
    }
    return $conteos;
}

/**
 * Indica si una ciudad tiene personas asociadas.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si hay al menos una persona que nació en esa ciudad
 */
function hayPersonasEnCiudad($conexion, $id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM personas WHERE ciudad_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

// ------------------------------------------------------------------
// MODIFICAR
// ------------------------------------------------------------------

/**
 * Actualiza los datos de una ciudad existente.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad a modificar
 * @param array $datos Array con los datos nuevos
 * @return bool true si se actualizó correctamente
 */
function actualizarCiudad($conexion, $id, $datos) {
    $sql = "UPDATE ciudades
            SET nombre = ?, provincia = ?, latitud = ?, longitud = ?,
                codigo_postal = ?, descripcion = ?, fecha_fundacion = ?
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],
        $datos['longitud'],
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion'],
        $id,
    ]);
}

// ------------------------------------------------------------------
// BORRAR
// ------------------------------------------------------------------

/**
 * Elimina una ciudad por su ID.
 *
 * ATENCIÓN: la clave foránea de personas impide borrar una ciudad que
 * tenga personas. Hay que verificar antes con hayPersonasEnCiudad().
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si se eliminó correctamente
 */
function eliminarCiudad($conexion, $id) {
    $stmt = $conexion->prepare("DELETE FROM ciudades WHERE id = ?");
    return $stmt->execute([$id]);
}
