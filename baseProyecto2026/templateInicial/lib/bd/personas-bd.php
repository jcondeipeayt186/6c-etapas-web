<?php
/*
    lib/bd/personas-bd.php - Consultas SQL de la tabla PERSONAS

    Este archivo tiene TODAS las funciones que leen o escriben en la
    tabla personas. Nada más. Así cada módulo tiene su propio archivo:

        lib/bd/gestionBaseDatos.php  -> la conexión
        lib/bd/personas-bd.php       -> tabla personas
        lib/bd/ciudades-bd.php       -> tabla ciudades

    Tabla personas:
        id, nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono,
        direccion, ciudad_id (clave foránea a ciudades), avatar_path, cv_path, observaciones

    AVISO IMPORTANTE (normalización):
    En personas NO están ni la ciudad, ni la provincia, ni el código postal
    como texto. Solo está ciudad_id, que es el NÚMERO de la ciudad
    (clave foránea a ciudades.id). Para mostrar el nombre de la ciudad
    hacemos un JOIN (ver obtenerTodasLasPersonas).
*/

// ------------------------------------------------------------------
// CREAR
// ------------------------------------------------------------------

/**
 * Inserta una persona nueva en la tabla personas.
 *
 * Los "?" son marcadores de posición: van los valores separados en el
 * execute(). Nunca hay que concatenar datos del usuario dentro del SQL.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param array $datos Array con los datos de la persona
 * @return bool true si se insertó correctamente
 */
function insertarPersona($conexion, $datos) {
    $sql = "INSERT INTO personas
                (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono,
                 direccion, ciudad_id, avatar_path, cv_path, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['apellido'],
        $datos['dni'],
        $datos['cuit'],
        $datos['fecha_nacimiento'],
        $datos['email'],
        $datos['telefono'],
        $datos['direccion'],
        $datos['ciudad_id'],   // clave foránea a ciudades (puede ser null)
        $datos['avatar_path'], // ruta de la foto (files/avatars/...) o null
        $datos['cv_path'],     // ruta del currículum (files/cv/...) o null
        $datos['observaciones'],
    ]);
}

// ------------------------------------------------------------------
// LEER
// ------------------------------------------------------------------

/**
 * Obtiene TODAS las personas con los datos de su ciudad.
 * Acepta un filtro opcional por nombre.
 *
 * JOIN: combina dos tablas. "personas p LEFT JOIN ciudades c ON p.ciudad_id = c.id"
 * significa: por cada persona, buscar la ciudad cuyo id coincide con su
 * ciudad_id y traer también el nombre y la provincia de esa ciudad.
 *
 * LEFT JOIN (y no JOIN) para que también aparezcan las personas que
 * todavía no tienen ciudad asignada.
 *
 * LIKE con %: busca textos que CONTENGAN el término.
 * '%Rio%' encuentra "Río Cuarto", "Río Tercero", etc.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Texto a buscar en el nombre (opcional)
 * @return array Array de personas
 */
function obtenerTodasLasPersonas($conexion, $busqueda = '') {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia
            FROM personas p
            LEFT JOIN ciudades c ON p.ciudad_id = c.id";

    $parametros = []; // valores que se van a poner en los "?"
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY p.id DESC"; // las más nuevas primero

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

/**
 * Obtiene UNA persona por su ID (se usa para precargar el formulario
 * de edición: editPersona.php?id=5).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona
 * @return array|null La persona encontrada o null si no existe
 */
function obtenerPersonaPorId($conexion, $id) {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre
            FROM personas p
            LEFT JOIN ciudades c ON p.ciudad_id = c.id
            WHERE p.id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(); // fetch() trae UNA sola fila
}

/**
 * Cuenta cuántas personas hay en total (se usa en la portada).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @return int Cantidad total de personas
 */
function contarPersonas($conexion) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM personas");
    $stmt->execute();
    return (int) $stmt->fetchColumn(); // fetchColumn() trae el primer valor
}

// ------------------------------------------------------------------
// MODIFICAR
// ------------------------------------------------------------------

/**
 * Actualiza los datos de una persona existente.
 *
 * UPDATE modifica la fila que tiene ese id. El WHERE id = ? es OBLIGATORIO:
 * sin él se modificarían TODAS las personas de la tabla.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona a modificar
 * @param array $datos Array con los datos nuevos
 * @return bool true si se actualizó correctamente
 */
function actualizarPersona($conexion, $id, $datos) {
    $sql = "UPDATE personas
            SET nombre = ?, apellido = ?, dni = ?, cuit = ?, fecha_nacimiento = ?,
                email = ?, telefono = ?, direccion = ?, ciudad_id = ?,
                avatar_path = ?, cv_path = ?, observaciones = ?
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['apellido'],
        $datos['dni'],
        $datos['cuit'],
        $datos['fecha_nacimiento'],
        $datos['email'],
        $datos['telefono'],
        $datos['direccion'],
        $datos['ciudad_id'],
        $datos['avatar_path'],
        $datos['cv_path'],
        $datos['observaciones'],
        $id, // este "?" es el del WHERE, por eso va último
    ]);
}

// ------------------------------------------------------------------
// BORRAR
// ------------------------------------------------------------------

/**
 * Elimina una persona por su ID.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona
 * @return bool true si se eliminó correctamente
 */
function eliminarPersona($conexion, $id) {
    $stmt = $conexion->prepare("DELETE FROM personas WHERE id = ?");
    return $stmt->execute([$id]);
}
