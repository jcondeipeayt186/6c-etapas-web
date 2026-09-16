<?php
// login.php - Procesa el formulario de index.php (POST username, password)
session_start();// Inicia la sesión para poder almacenar información del usuario autenticado

//require_once lo que hace es incluir y evaluar el archivo especificado durante la ejecución del script. 
//Si el archivo no se encuentra, se producirá un error fatal y se detendrá la ejecución del script. 
//Esto es útil para asegurarse de que los archivos necesarios estén presentes antes de continuar con la ejecución del código.
//Por otro lado, include lo que hace es incluir y evaluar el archivo especificado durante la ejecución del script y no produce 
//un error fatal si el archivo no se encuentra, sino que genera una advertencia y continúa con la ejecución del script.
require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/usuarios.php';
require_once '../../lib/bd/usuario_roles.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    //urlencode() codifica una cadena para que pueda ser utilizada en una URL, reemplazando caracteres especiales con su representación de escape.
    header("Location: ../../index.php?error=" . urlencode("Usuario y clave son obligatorios"));
    exit;
}

$conexion = obtenerConexion();
$usuario = verificarCredenciales($conexion, $username, $password);

if (!$usuario) {
    header("Location: ../../index.php?error=credenciales");
    exit;
}

// Actualizar fechaUltimoAcceso
actualizarUltimoAcceso($conexion, $usuario['id']);

// Cargar roles y tipos en sesión
$roles = obtenerRolesDeUsuario($conexion, $usuario['id']);
$tipos = array_unique(array_column($roles, 'tipo'));

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['username'] = $usuario['username'];
$_SESSION['roles'] = $roles;
$_SESSION['tipos'] = $tipos;
$_SESSION['fecha_login'] = date('Y-m-d H:i:s');

header("Location: ../home/index.php");
exit;
