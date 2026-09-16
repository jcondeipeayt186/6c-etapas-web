<?php
/*
    roles.php - CRUD para tabla roles
    Tabla roles: id, nombre, descripcion, tipo (TINYINT)
    tipo: 1=Administrador, 2=Directivo, 3=Operador/Usuario, etc.
*/

function insertarRol($conexion, $nombre, $descripcion, $tipo) {
    $sql = "INSERT INTO roles (nombre, descripcion, tipo) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre, $descripcion, (int)$tipo]);
    return $conexion->lastInsertId();
}

function obtenerTodosLosRoles($conexion, $busqueda = '') {
    $sql = "SELECT * FROM roles";
    $params = [];
    if ($busqueda !== '') {
        $sql .= " WHERE nombre LIKE ?";
        $params[] = '%' . $busqueda . '%';
    }
    $sql .= " ORDER BY tipo ASC, nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function obtenerRolPorId($conexion, $id) {
    $sql = "SELECT * FROM roles WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function actualizarRol($conexion, $id, $nombre, $descripcion, $tipo) {
    $sql = "UPDATE roles SET nombre = ?, descripcion = ?, tipo = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$nombre, $descripcion, (int)$tipo, $id]);
}

function eliminarRol($conexion, $id) {
    // Verificar si tiene usuarios asignados (opcional: bloquear)
    $sql = "DELETE FROM roles WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

function contarRoles($conexion) {
    $stmt = $conexion->query("SELECT COUNT(*) FROM roles");
    return (int) $stmt->fetchColumn();
}

function hayUsuariosEnRol($conexion, $rolId) {
    $sql = "SELECT COUNT(*) FROM usuario_rol WHERE rol_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$rolId]);
    return $stmt->fetchColumn() > 0;
}

function existeRolNombre($conexion, $nombre, $excluirId = null) {
    if ($excluirId) {
        $sql = "SELECT COUNT(*) FROM roles WHERE nombre = ? AND id != ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $excluirId]);
    } else {
        $sql = "SELECT COUNT(*) FROM roles WHERE nombre = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre]);
    }
    return $stmt->fetchColumn() > 0;
}

function obtenerRolesPorTipo($conexion, $tipo) {
    $sql = "SELECT * FROM roles WHERE tipo = ? ORDER BY nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([(int)$tipo]);
    return $stmt->fetchAll();
}
