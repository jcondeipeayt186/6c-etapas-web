<?php
/*
    usuario_roles.php - Gestión N:M usuario <-> rol
    Tabla usuario_rol: id, usuario_id, rol_id, fechaCreacion
*/

function asignarRolAUsuario($conexion, $usuarioId, $rolId) {
    // Evitar duplicados
    $sql = "SELECT COUNT(*) FROM usuario_rol WHERE usuario_id = ? AND rol_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuarioId, $rolId]);
    if ($stmt->fetchColumn() > 0) return false;
    $sql = "INSERT INTO usuario_rol (usuario_id, rol_id, fechaCreacion) VALUES (?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$usuarioId, $rolId]);
}

function quitarRolDeUsuario($conexion, $usuarioId, $rolId) {
    $sql = "DELETE FROM usuario_rol WHERE usuario_id = ? AND rol_id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$usuarioId, $rolId]);
}

function obtenerRolesDeUsuario($conexion, $usuarioId) {
    $sql = "SELECT r.* FROM roles r
            INNER JOIN usuario_rol ur ON r.id = ur.rol_id
            WHERE ur.usuario_id = ?
            ORDER BY r.tipo ASC, r.nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuarioId]);
    return $stmt->fetchAll();
}

function obtenerIdsRolesDeUsuario($conexion, $usuarioId) {
    $sql = "SELECT rol_id FROM usuario_rol WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuarioId]);
    return array_column($stmt->fetchAll(), 'rol_id');
}

function obtenerUsuariosDeRol($conexion, $rolId) {
    $sql = "SELECT u.* FROM usuarios u
            INNER JOIN usuario_rol ur ON u.id = ur.usuario_id
            WHERE ur.rol_id = ?
            ORDER BY u.username ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$rolId]);
    return $stmt->fetchAll();
}

function sincronizarRolesUsuario($conexion, $usuarioId, $arrayRolIds) {
    // Elimina todos y vuelve a insertar (simple y efectivo)
    $conexion->prepare("DELETE FROM usuario_rol WHERE usuario_id = ?")->execute([$usuarioId]);
    if (empty($arrayRolIds)) return true;
    $sql = "INSERT INTO usuario_rol (usuario_id, rol_id, fechaCreacion) VALUES (?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    foreach ($arrayRolIds as $rolId) {
        $stmt->execute([$usuarioId, (int)$rolId]);
    }
    return true;
}

function usuarioTieneTipo($conexion, $usuarioId, $tiposPermitidos) {
    // $tiposPermitidos array [1,2] por ejemplo
    if (empty($tiposPermitidos)) return false;
    $placeholders = implode(',', array_fill(0, count($tiposPermitidos), '?'));
    $sql = "SELECT COUNT(*) FROM roles r
            INNER JOIN usuario_rol ur ON r.id = ur.rol_id
            WHERE ur.usuario_id = ? AND r.tipo IN ($placeholders)";
    $params = array_merge([$usuarioId], $tiposPermitidos);
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}
