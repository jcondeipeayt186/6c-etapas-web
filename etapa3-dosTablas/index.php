<?php
/*
    index.php - Portada / Dashboard de la aplicación (ETAPA 3 - Dos Tablas)

    Esta es la página principal: la primera que ve el usuario.
    No es un formulario ni una tabla grande, sino un "tablero" que muestra:
    1. Estadísticas en vivo desde MySQL: cuántas personas y cuántas ciudades hay
    2. Accesos directos a las dos secciones principales del sistema

    Diferencia con la Etapa 2:
    - En Etapa 2 el index era directamente el formulario de carga
    - Ahora el index es una PORTADA y los formularios están en src/persona/editPersona.php y src/ciudad/editCiudad.php
*/

// Incluimos la librería de base de datos (obtenerConexion, contarPersonas, contarCiudades)
require_once 'lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina)
require_once 'lib/html/funcionesHTML.php';

// Creamos la conexión PDO a MySQL
$conexion = obtenerConexion();

// Consultamos las estadísticas con COUNT(*)
// contarPersonas() y contarCiudades() están definidas en gestionBaseDatos.php
$cantidadPersonas = contarPersonas($conexion); // Ej: 52
$cantidadCiudades = contarCiudades($conexion); // Ej: 20
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Gestión Personas y Ciudades</title>
    <!-- Bootstrap 5 CSS desde CDN (sin necesidad de instalar nada) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card shadow">
                    <!-- Cabecera azul con logo de MySQL -->
                    <div class="card-header bg-primary text-white text-center">
                        <img src="img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle">Bienvenido</h2>
                    </div>
                    <div class="card-body">

                        <p class="text-center text-muted">
                            Aplicación de gestión de <strong>personas</strong> y <strong>ciudades</strong> con base de datos MySQL
                        </p>
                        <p class="text-center small text-muted">
                            Etapa 3: dos tablas relacionadas por clave foránea (ciudad_id)
                        </p>

                        <!--
                            ESTADÍSTICAS:
                            Dos cards lado a lado (col-md-6) mostrando los COUNT(*) en grande.
                            display-3 es una clase de Bootstrap para números enormes.
                        -->
                        <div class="row g-3 text-center">
                            <!-- Card Personas -->
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <!-- Número grande azul: cantidad de personas -->
                                        <h1 class="display-3 text-primary mb-0"><?php echo $cantidadPersonas; ?></h1>
                                        <p class="text-muted mb-0">Personas registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <!-- Acceso a la gestión de personas (lista + filtro + update) -->
                                        <a href="src/persona/viewPersona.php" class="btn btn-primary w-100">Gestionar personas</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Ciudades -->
                            <div class="col-md-6">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <!-- Número grande celeste: cantidad de ciudades -->
                                        <h1 class="display-3 text-info mb-0"><?php echo $cantidadCiudades; ?></h1>
                                        <p class="text-muted mb-0">Ciudades registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <!-- Acceso a la gestión de ciudades (lista + filtro + CRUD) -->
                                        <a href="src/ciudad/viewCiudad.php" class="btn btn-info text-white w-100">Gestionar ciudades</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones secundarios: accesos directos para crear -->
                        <div class="text-center mt-4">
                            <!-- Cargar nueva persona (abre el formulario en modo ALTA) -->
                            <a href="src/persona/editPersona.php" class="btn btn-outline-primary me-2">Cargar nueva persona</a>
                            <!-- Cargar nueva ciudad -->
                            <a href="src/ciudad/editCiudad.php" class="btn btn-outline-info">Cargar nueva ciudad</a>
                        </div>

                        <!-- Aviso útil: si no hay ciudades, no se pueden cargar personas (por la FK) -->
                        <?php if ($cantidadCiudades === 0): ?>
                            <div class="alert alert-warning mt-3 text-center">
                                No hay ciudades cargadas. Primero debe
                                <a href="src/ciudad/editCiudad.php" class="alert-link">crear una ciudad</a>
                                para poder asignarla a las personas.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página común (función definida en lib/html/funcionesHTML.php) -->
    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
