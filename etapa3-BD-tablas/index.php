<?php
/*
    index.php - Pagina de bienvenida (ETAPA 3)

    Esta pagina es la PORTADA de la aplicacion.
    Muestra:
    1. Estadisticas de la base de datos (cantidad de personas y de ciudades)
    2. Botones para acceder a la gestion de personas y de ciudades
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

// Consultamos las estadisticas de la base de datos
$cantidadPersonas = contarPersonas($conexion);
$cantidadCiudades = contarCiudades($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Formulario de Personas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <img src="img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle">Bienvenido</h2>
                    </div>
                    <div class="card-body">

                        <p class="text-center text-muted">
                            Aplicacion de gestion de personas y ciudades con base de datos MySQL
                        </p>

                        <!--
                            ESTADISTICAS:
                            Bootstrap 5 permite agrupar numeros en "cards" dentro de una grilla.
                            Los valores se obtienen de la base de datos con COUNT(*).
                        -->
                        <div class="row g-3 text-center">
                            <div class="col-md-6">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <!-- display-3 es un estilo de letra grande de Bootstrap -->
                                        <h1 class="display-3 text-primary mb-0"><?php echo $cantidadPersonas; ?></h1>
                                        <p class="text-muted mb-0">Personas registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="resultado.php" class="btn btn-primary w-100">Gestionar personas</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <h1 class="display-3 text-info mb-0"><?php echo $cantidadCiudades; ?></h1>
                                        <p class="text-muted mb-0">Ciudades registradas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="ciudades.php" class="btn btn-info text-white w-100">Gestionar ciudades</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enlace directo al formulario de carga de persona -->
                        <div class="text-center mt-3">
                            <a href="formPersona.php" class="btn btn-outline-primary">Cargar nueva persona</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
