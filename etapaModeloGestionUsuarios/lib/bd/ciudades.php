<?php
/*
    ciudades.php - CRUD para tabla ciudades
    ciudades: id, nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion
*/

function insertarCiudad($conexion, $datos) {
    $sql = "INSERT INTO ciudades (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['provincia'],
        $datos['latitud'],
        $datos['longitud'],
        $datos['codigo_postal'],
        $datos['descripcion'],
        $datos['fecha_fundacion']
    ]);
}

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
    return $stmt->fetchAll();
}

function obtenerCiudadPorId($conexion, $id) {
    $sql = "SELECT * FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function actualizarCiudad($conexion, $id, $datos) {
    $sql = "UPDATE ciudades
            SET nombre = ?, provincia = ?, latitud = ?, longitud = ?, codigo_postal = ?, descripcion = ?, fecha_fundacion = ?
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
        $id
    ]);
}

function eliminarCiudad($conexion, $id) {
    $sql = "DELETE FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

function hayPersonasEnCiudad($conexion, $id) {
    $sql = "SELECT COUNT(*) FROM personas WHERE ciudad_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function contarCiudades($conexion) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM ciudades");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}
