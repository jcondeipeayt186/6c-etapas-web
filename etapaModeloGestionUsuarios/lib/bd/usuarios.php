<?php
/*
    usuarios.php - CRUD para tabla usuarios + relación N:M usuario_rol

    Tabla usuarios: id, username, password (hash), fechaUltimoAcceso, fechaCreacion
    Tabla usuario_rol: id, usuario_id, rol_id, fechaCreacion

    password se guarda con password_hash() y se verifica con password_verify()
    PASSWORD_DEFAULT es el algoritmo de hashing por defecto (actualmente bcrypt) y se recomienda usarlo para almacenar contraseñas
     de forma segura.
*/

require_once __DIR__ . '/conexion.php';

// --- USUARIOS ---

function insertarUsuario($conexion, $username, $passwordPlano) {
    $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (username, password, fechaCreacion) VALUES (?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$username, $hash]);
    return $conexion->lastInsertId();
}

function obtenerTodosLosUsuarios($conexion, $busqueda = '') {
    $sql = "SELECT * FROM usuarios";
    $params = [];
    if ($busqueda !== '') {
        $sql .= " WHERE username LIKE ?";
        $params[] = '%' . $busqueda . '%';
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function obtenerUsuarioPorId($conexion, $id) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function obtenerUsuarioPorUsername($conexion, $username) {
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function actualizarUsuario($conexion, $id, $username, $passwordPlano = null) {
    if ($passwordPlano !== null && $passwordPlano !== '') {
        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET username = ?, password = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$username, $hash, $id]);
    } else {
        $sql = "UPDATE usuarios SET username = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$username, $id]);
    }
}

function eliminarUsuario($conexion, $id) {
    // ON DELETE CASCADE en usuario_rol limpia relaciones automáticamente
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

function actualizarUltimoAcceso($conexion, $id) {
    $sql = "UPDATE usuarios SET fechaUltimoAcceso = NOW() WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}

function contarUsuarios($conexion) {
    $stmt = $conexion->query("SELECT COUNT(*) FROM usuarios");
    return (int) $stmt->fetchColumn();
}

function existeUsername($conexion, $username, $excluirId = null) {
    if ($excluirId) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE username = ? AND id != ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$username, $excluirId]);
    } else {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE username = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$username]);
    }
    return $stmt->fetchColumn() > 0;
}

// --- FUNCIONES DE LOGIN ---

function verificarCredenciales($conexion, $username, $passwordPlano) {
    $usuario = obtenerUsuarioPorUsername($conexion, $username);
    if (!$usuario) return null;
    if (!password_verify($passwordPlano, $usuario['password'])) return null;
    return $usuario;
}
