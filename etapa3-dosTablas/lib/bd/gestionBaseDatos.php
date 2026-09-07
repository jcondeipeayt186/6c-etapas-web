<?php
/*
    gestionBaseDatos.php - Capa de acceso a datos (DAO) - ETAPA 3 dosTablas

    Este archivo es el "intermediario" entre PHP y MySQL.
    Contiene TODAS las funciones que hablan con la base de datos.

    ¿Por qué separar esto?
    - Reutilización: cualquier página puede hacer require_once y usar las funciones
    - Orden: la lógica SQL queda centralizada en un solo lugar
    - Mantenimiento: si cambia la conexión o una consulta, se cambia acá y no en 5 archivos

    Diferencias con la Etapa 2:
    - Ahora hay DOS tablas: ciudades y personas
    - personas ya NO guarda ciudad/provincia/codigo_postal como texto
    - personas guarda ciudad_id (clave foránea) que apunta a ciudades(id)
    - Las consultas de personas usan JOIN para traer el nombre de la ciudad
    - Heredamos el filtro por nombre (LIKE) y el UPDATE de la etapa Filtro+Update
*/

// ---------------------------------------------------------------------------
// CONEXIÓN PDO
// ---------------------------------------------------------------------------

/**
 * Establece la conexión con MySQL usando PDO.
 *
 * PDO (PHP Data Objects) es la forma segura de conectarse a bases de datos en PHP.
 * Permite usar "prepared statements" con ? que protegen contra inyección SQL.
 *
 * Inyección SQL: si concatenamos texto del usuario directo en el SQL,
 * un atacante puede mandar  ' OR '1'='1  y alterar la consulta.
 * Con ? + execute([]) los datos viajan separados y nunca se mezclan con el SQL.
 *
 * @return PDO Objeto de conexión listo para usar
 */
function obtenerConexion() {
    // -- Datos de conexión -- CAMBIAR según tu instalación (XAMPP suele ser root sin contraseña)
    $host = "localhost";        // Servidor MySQL (generalmente localhost)
    $usuario = "root";          // Usuario de MySQL
    $contrasena = "";           // Contraseña de MySQL (vacío por defecto en XAMPP)
    $baseDatos = "contactos3";  // Nombre de la base de datos (ver lib/bd/script.sql)

    try {
        // Creamos la conexión PDO con manejo de errores try/catch
        $conexion = new PDO(
            "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );
        // Configuramos PDO para que lance excepciones ante errores (más fácil de debuggear)
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;

    } catch (PDOException $e) {
        // Si hay error de conexión, mostramos el mensaje y detenemos el script
        // En producción no conviene mostrar detalles, pero para aprender es útil
        die("Error de conexion: " . $e->getMessage());
    }
}

/* ===================================================== */
/*  FUNCIONES DE PERSONAS                                */
/* ===================================================== */

/**
 * Inserta una nueva persona en la tabla personas.
 *
 * Nota clave de la normalización:
 * - Antes (Etapa 2) guardábamos ciudad y provincia como texto libre: "Córdoba", "cordoba", "Cba"
 * - Ahora guardamos ciudad_id (INT) que es la CLAVE FORÁNEA a ciudades(id)
 * - El código postal ya no está acá: pertenece a la ciudad (ver tabla ciudades)
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param array $datos Array con los datos de la persona (incluye ciudad_id)
 * @return bool true si insertó correctamente
 */
function insertarPersona($conexion, $datos) {
    // Los ? son placeholders que luego se reemplazan por valores reales de forma segura
    $sql = "INSERT INTO personas (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql); // PDO analiza la consulta pero no la ejecuta aún
    // execute() une cada valor con su ? EN ORDEN. El orden del SQL y del array debe coincidir exactamente.
    $resultado = $stmt->execute([
        $datos['nombre'],           // 1° ?
        $datos['apellido'],         // 2° ?
        $datos['dni'],              // 3° ?
        $datos['cuit'],             // 4° ?
        $datos['fecha_nacimiento'], // 5° ?
        $datos['email'],            // 6° ?
        $datos['telefono'],         // 7° ?
        $datos['direccion'],        // 8° ?
        $datos['ciudad_id'],        // 9° ? -> clave foránea a ciudades
        $datos['observaciones']     // 10° ?
    ]);

    return $resultado;
}

/**
 * Obtiene las personas con sus datos de ciudad.
 * Acepta un filtro opcional por NOMBRE.
 *
 * JOIN: combina dos tablas. "personas p JOIN ciudades c ON p.ciudad_id = c.id"
 * significa: por cada persona, buscar la ciudad cuyo id coincide con su ciudad_id
 * y traer también c.nombre y c.provincia.
 *
 * LIKE + %: busca textos que CONTENGAN el término.
 * '%' es comodín: '%jul%' matchea "Julián", "Julia", "Julieta".
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Término de búsqueda (filtra por nombre de persona, opcional)
 * @return array Array de personas (cada elemento es un array asociativo con ciudad_nombre y ciudad_provincia)
 */
function obtenerTodasLasPersonas($conexion, $busqueda = '') {
    // Consulta base con JOIN. p.* trae todas las columnas de personas, c.nombre como ciudad_nombre, etc.
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia, c.codigo_postal AS ciudad_codigo_postal
            FROM personas p
            LEFT JOIN ciudades c ON p.ciudad_id = c.id";
    // LEFT JOIN en vez de JOIN: si una persona no tiene ciudad asignada, igual aparece (ciudad_nombre = null)

    // Si hay término de búsqueda, agregamos WHERE con LIKE
    $parametros = []; // Array que contendrá los valores para los ?
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?"; // Se concatena el WHERE solo si hace falta
        $parametros[] = '%' . $busqueda . '%'; // Armamos '%Jul%' para buscar "que contenga"
    }

    $sql .= " ORDER BY p.id DESC"; // Los más nuevos primero

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros); // Si $busqueda es vacío, $parametros = [] y no hay ? que reemplazar

    // fetchAll trae todos los registros como arrays asociativos
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Alias para compatibilidad con la Etapa 2-FiltroyUpdate.
 * En esa etapa la función se llamaba obtenerPersonasConFiltro().
 * Ahora redirige a obtenerTodasLasPersonas() que hace lo mismo (con JOIN).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Término de búsqueda
 * @return array Array de personas
 */
function obtenerPersonasConFiltro($conexion, $busqueda = '') {
    return obtenerTodasLasPersonas($conexion, $busqueda);
}

/**
 * Obtiene UNA persona por su ID.
 * Se usa para precargar el formulario de edición (editPersona.php?id=X).
 *
 * fetch() trae UNA sola fila (a diferencia de fetchAll que trae todas).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona
 * @return array|null La persona encontrada o null si no existe
 */
function obtenerPersonaPorId($conexion, $id) {
    // JOIN para traer también los datos de la ciudad (por si el formulario los necesita)
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre FROM personas p LEFT JOIN ciudades c ON p.ciudad_id = c.id WHERE p.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]); // El ? se reemplaza por el id
    return $stmt->fetch(PDO::FETCH_ASSOC); // Un solo registro
}

/**
 * Actualiza los datos de una persona existente.
 *
 * UPDATE modifica una fila que ya existe, identificada por su id.
 * SET columna = ? : cada ? se reemplaza por el valor nuevo.
 * WHERE id = ? : ¡CRÍTICO! Sin el WHERE se modificarían TODAS las filas.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona a modificar
 * @param array $datos Array asociativo con los datos nuevos (incluye ciudad_id)
 * @return bool true si actualizó correctamente
 */
function actualizarPersona($conexion, $id, $datos) {
    $sql = "UPDATE personas
            SET nombre = ?, apellido = ?, dni = ?, cuit = ?, fecha_nacimiento = ?, email = ?, telefono = ?, direccion = ?, ciudad_id = ?, observaciones = ?
            WHERE id = ?";

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
        $datos['ciudad_id'], // clave foránea
        $datos['observaciones'],
        $id // El último ? es el del WHERE, por eso va al final
    ]);

    return $resultado;
}

/**
 * Elimina una persona por su ID.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la persona
 * @return bool true si eliminó correctamente
 */
function eliminarPersona($conexion, $id) {
    $sql = "DELETE FROM personas WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Cuenta cuántas personas hay en total.
 * Se usa en la portada (index.php) para mostrar la estadística.
 *
 * COUNT(*) cuenta filas. fetchColumn() trae el primer valor de la primera fila.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @return int Cantidad total de personas
 */
function contarPersonas($conexion) {
    $sql = "SELECT COUNT(*) FROM personas";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

/* ===================================================== */
/*  FUNCIONES DE CIUDADES (CRUD completo)                */
/* ===================================================== */

/**
 * Inserta una nueva ciudad en la tabla ciudades.
 * El código_postal pertenece a la CIUDAD, no a la persona (normalización).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param array $datos Array con los datos de la ciudad
 * @return bool true si insertó correctamente
 */
function insertarCiudad($conexion, $datos) {
    $sql = "INSERT INTO ciudades (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],      // puede ser null si el usuario no lo completó
        $datos['longitud'],     // puede ser null
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion'] // puede ser null
    ]);

    return $resultado;
}

/**
 * Obtiene las ciudades ordenadas por nombre.
 * Acepta un filtro opcional por NOMBRE de la ciudad (LIKE).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Término de búsqueda (filtra por nombre de ciudad)
 * @return array Array de ciudades
 */
function obtenerTodasLasCiudades($conexion, $busqueda = '') {
    $sql = "SELECT * FROM ciudades";

    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY nombre ASC"; // Orden alfabético para ciudades

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene UNA ciudad por su ID.
 * Se usa para cargar los datos en el formulario de edición (editCiudad.php).
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return array|null La ciudad encontrada o null si no existe
 */
function obtenerCiudadPorId($conexion, $id) {
    $sql = "SELECT * FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // Una sola fila
}

/**
 * Actualiza los datos de una ciudad existente.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad a modificar
 * @param array $datos Array con los datos nuevos
 * @return bool true si actualizó correctamente
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
        $id // WHERE id = ? va al final
    ]);

    return $resultado;
}

/**
 * Elimina una ciudad por su ID.
 * ATENCIÓN: la clave foránea de personas impide eliminar una ciudad
 * que tenga personas. Verificar con hayPersonasEnCiudad() antes.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si eliminó correctamente
 */
function eliminarCiudad($conexion, $id) {
    $sql = "DELETE FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

/**
 * Cuenta cuántas personas nacieron en una ciudad.
 * Si el resultado es > 0, NO se puede eliminar la ciudad porque la FK lo impide.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @param int $id ID de la ciudad
 * @return bool true si hay personas nacidas en esa ciudad
 */
function hayPersonasEnCiudad($conexion, $id) {
    $sql = "SELECT COUNT(*) FROM personas WHERE ciudad_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $cantidad = $stmt->fetchColumn(); // COUNT(*) devuelve un número
    return $cantidad > 0;
}

/**
 * Cuenta cuántas ciudades hay en total.
 * Se usa en la portada (index.php) para mostrar la estadística.
 *
 * @param PDO $conexion Conexión a la base de datos
 * @return int Cantidad total de ciudades
 */
function contarCiudades($conexion) {
    $sql = "SELECT COUNT(*) FROM ciudades";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}
