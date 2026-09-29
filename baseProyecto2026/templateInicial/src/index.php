<?php
/*
    src/index.php - Inicio / Dashboard de la aplicación

    Esta es la pantalla principal una vez que se entra a la aplicación.
    Muestra:
    1. Dos tarjetas con las estadísticas que salen de MySQL (COUNT(*))
    2. Botones para ir a cada módulo: personas y ciudades

    ¿Navegable? Sí. Es una página con HTML que el usuario ve en el navegador.
    No tiene formularios ni botones que guarden datos: solo muestra y
    sirve de menú.
*/

// Incluimos la librería de la base de datos.
// Trae obtenerConexion(), contarPersonas() y contarCiudades()
// (porque gestionBaseDatos.php ya incluye personas-bd.php y ciudades-bd.php)
require_once '../lib/bd/gestionBaseDatos.php';

// Incluimos las librerías de HTML compartido (piePagina, mostrarAlerta)
require_once '../lib/html/funcionesHTML.php';

// Creamos la conexión PDO a MySQL
$conexion = obtenerConexion();

// Consultamos las estadísticas.
// Si todavía no se ejecutó el script.sql, obtenerConexion() corta el script
// con un mensaje de error, así que acá siempre hay conexión.
$cantidadPersonas = contarPersonas($conexion);
$cantidadCiudades = contarCiudades($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Gestión de Personas y Ciudades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Barra superior con el nombre de la aplicación -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                <img src="../img/mysql.png" alt="Logo MySQL" width="30" height="30" class="me-2">
                Gestión de Personas y Ciudades
            </span>
            <a href="../index.php" class="btn btn-outline-light btn-sm">Portada</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card shadow">
                    <!-- Cabecera azul con el título -->
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0">Inicio</h2>
                    </div>
                    <div class="card-body">

                        <p class="text-center text-muted">
                            Aplicación de gestión de <strong>personas</strong> y <strong>ciudades</strong> con base de datos MySQL
                        </p>
                        <p class="text-center small text-muted">
                            Proyecto base 2026 &middot; PHP + MySQL + Bootstrap 5
                        </p>

                        <!--
                            ESTADÍSTICAS:
                            Dos tarjetas con los COUNT(*) que vienen de la base.
                            "display-4" es la clase de Bootstrap para números grandes.
                        -->
                        <div class="row g-3 text-center">
                            <!-- Tarjeta de personas -->
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h1 class="display-4 text-primary mb-0"><?php echo $cantidadPersonas; ?></h1>
                                        <p class="text-muted mb-0">Personas registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="persona/viewPersona.php" class="btn btn-primary w-100">Gestionar personas</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta de ciudades -->
                            <div class="col-md-6">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <h1 class="display-4 text-info mb-0"><?php echo $cantidadCiudades; ?></h1>
                                        <p class="text-muted mb-0">Ciudades registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="ciudad/viewCiudad.php" class="btn btn-info text-white w-100">Gestionar ciudades</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones para crear registros nuevos -->
                        <div class="text-center mt-4">
                            <a href="persona/editPersona.php" class="btn btn-outline-primary me-2">Cargar nueva persona</a>
                            <a href="ciudad/editCiudad.php" class="btn btn-outline-info">Cargar nueva ciudad</a>
                        </div>

                        <!-- Aviso: sin ciudades no se pueden crear personas (por la clave foránea) -->
                        <?php if ($cantidadCiudades === 0): ?>
                            <div class="alert alert-warning mt-3 text-center mb-0">
                                No hay ciudades cargadas. Primero debe
                                <a href="ciudad/editCiudad.php" class="alert-link">crear una ciudad</a>
                                para poder asignarla a las personas.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página común (definido en lib/html/funcionesHTML.php) -->
    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
