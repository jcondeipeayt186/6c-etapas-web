<?php
/*
    conexion.php - Conexión PDO centralizada (ETAPA 5 - Gestión Usuarios)

    Este archivo SOLO se encarga de la conexión.
    Las operaciones CRUD están separadas por módulo:
      - usuarios.php  -> tabla usuarios + usuario_rol
      - roles.php     -> tabla roles
      - personas.php  -> tabla personas
      - ciudades.php  -> tabla ciudades

    Lee credenciales desde .env (si existe) o usa valores por defecto.
*/

function cargarEnv($ruta) {
    if (!file_exists($ruta)) return;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || $linea[0] === '#') continue;
        if (strpos($linea, '=') === false) continue;
        list($clave, $valor) = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);
        // quitar comillas si existen
        $valor = trim($valor, "\"'");
        if (!array_key_exists($clave, $_ENV)) {
            $_ENV[$clave] = $valor;
            putenv("$clave=$valor");
        }
    }
}

/**
 * Obtiene la conexión PDO leyendo .env
 * @return PDO
 */
function obtenerConexion() {
    // Intentar cargar .env desde raíz del proyecto (2 niveles arriba de lib/bd)
    $posibles = [
        __DIR__ . '/../../.env',
        __DIR__ . '/../.env',
        __DIR__ . '/.env',
        dirname(__DIR__, 2) . '/.env',
    ];
    foreach ($posibles as $p) {
        if (file_exists($p)) { cargarEnv($p); break; }
    }
    // También probar con getcwd
    if (file_exists(getcwd() . '/.env')) cargarEnv(getcwd() . '/.env');

    $host    = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $dbname  = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'contactos5';
    $usuario = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass    = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    // compat: DB_PASS vs DB_PASSWORD
    if ($pass === '' && getenv('DB_PASSWORD')) $pass = getenv('DB_PASSWORD');
    $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';
    $port    = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

    try {
        $conexion = new PDO($dsn, $usuario, $pass);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexion: " . $e->getMessage() . " (verifica .env y que la BD exista - ejecuta sql/script.sql)");
    }
}

// Compatibilidad: mantener nombre gestionBaseDatos.php
// Si algún archivo viejo hace require 'gestionBaseDatos.php', que funcione
if (!function_exists('obtenerConexionCompat')) {
    // alias vacío
}
