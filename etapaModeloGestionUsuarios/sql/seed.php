<?php
// seed.php - Ejecuta inserts iniciales con hashes reales. Ejecutar una vez: php sql/seed.php
require_once __DIR__ . '/../lib/bd/conexion.php';
require_once __DIR__ . '/../lib/bd/roles.php';
require_once __DIR__ . '/../lib/bd/usuarios.php';
require_once __DIR__ . '/../lib/bd/usuario_roles.php';
require_once __DIR__ . '/../lib/bd/ciudades.php';
require_once __DIR__ . '/../lib/bd/personas.php';

$conexion = obtenerConexion();

// Roles base
$rolesBase = [
    ['Administrador','Acceso total: gestiona usuarios, roles, personas y ciudades',1],
    ['Directivo','Gestiona personas y ciudades, no gestiona usuarios',2],
    ['Operador','Usuario operativo con acceso limitado',3],
];
foreach ($rolesBase as $r) {
    if (!existeRolNombre($conexion, $r[0])) insertarRol($conexion, $r[0], $r[1], $r[2]);
}
echo "Roles OK\n";

// Usuarios demo - crea si no existe, o actualiza clave si ya existe (corrige hash viejo de 'password')
$usuariosDemo = [
    ['admin','admin123',[1]],
    ['directivo','direc123',[2]],
    ['operador','oper123',[3]],
];
foreach ($usuariosDemo as $u) {
    $exist = obtenerUsuarioPorUsername($conexion, $u[0]);
    if (!$exist) {
        $id = insertarUsuario($conexion, $u[0], $u[1]);
        echo "Usuario {$u[0]} creado id $id\n";
        foreach ($u[2] as $tipo) {
            $roles = obtenerRolesPorTipo($conexion, $tipo);
            foreach ($roles as $rol) asignarRolAUsuario($conexion, $id, $rol['id']);
        }
    } else {
        // Si la clave guardada no verifica con la esperada, la corregimos
        if (!password_verify($u[1], $exist['password'])) {
            $hash = password_hash($u[1], PASSWORD_DEFAULT);
            $conexion->prepare("UPDATE usuarios SET password=? WHERE id=?")->execute([$hash, $exist['id']]);
            echo "Usuario {$u[0]} existente -> password corregido\n";
        } else {
            echo "Usuario {$u[0]} ya existe (password OK)\n";
        }
        // Asegurar asignación de rol si faltaba
        foreach ($u[2] as $tipo) {
            $roles = obtenerRolesPorTipo($conexion, $tipo);
            foreach ($roles as $rol) asignarRolAUsuario($conexion, $exist['id'], $rol['id']);
        }
    }
}

echo "Seed completo. Contar: usuarios=".contarUsuarios($conexion)." roles=".contarRoles($conexion)."\n";
