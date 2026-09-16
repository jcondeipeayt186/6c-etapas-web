<?php
/*
    personas.php - CRUD para tabla personas (migrado de gestionBaseDatos original)
    personas: id, nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id (FK), observaciones
*/

function insertarPersona($conexion, $datos) {
    $sql = "INSERT INTO personas (nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        $datos['observaciones']
    ]);
}

function obtenerTodasLasPersonas($conexion, $busqueda = '') {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia, c.codigo_postal AS ciudad_codigo_postal
            FROM personas p
            LEFT JOIN ciudades c ON p.ciudad_id = c.id";
    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }
    $sql .= " ORDER BY p.id DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

function obtenerPersonasConFiltro($conexion, $busqueda = '') {
    return obtenerTodasLasPersonas($conexion, $busqueda);
}

function obtenerPersonaPorId($conexion, $id) {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre FROM personas p LEFT JOIN ciudades c ON p.ciudad_id = c.id WHERE p.id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function actualizarPersona($conexion, $id, $datos) {
    $sql = "UPDATE personas
            SET nombre = ?, apellido = ?, dni = ?, cuit = ?, fecha_nacimiento = ?, email = ?, telefono = ?, direccion = ?, ciudad_id = ?, observaciones = ?
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
        $datos['observaciones'],
        $id
    ]);
}

function eliminarPersona($conexion, $id) {
    $sql = "DELETE FROM personas WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

function contarPersonas($conexion) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM personas");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}
