<?php
/*
    index.php - PORTADA del proyecto

    Esta es la primera página que ve el usuario al abrir la aplicación.
    No tiene nada de base de datos: solo muestra el cartel, una imagen y
    un botón para entrar.

    Cuando se aprieta "INGRESAR" se pasa a src/index.php, que sí es el
    tablero de la aplicación (con las estadísticas y los módulos).
*/

// Mensaje opcional que puede llegar desde otra página (?mensaje=...)
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Gestión de Personas y Ciudades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Dos estilos mínimos para que las imágenes no sean enormes */
        .logo-carta { max-height: 110px; }
        .imagen-portada { max-height: 380px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <!-- Cartel con el logo de la escuela -->
                <div class="text-center mb-4">
                    <img src="img/LogoCasta.png" alt="Logo IPEAyT 186" class="logo-carta img-fluid">
                </div>

                <div class="card shadow">
                    <div class="row g-0">

                        <!-- Columna izquierda: imagen de portada -->
                        <div class="col-md-7 bg-white d-flex align-items-center justify-content-center p-4">
                            <img src="img/image.png" alt="Imagen de portada" class="imagen-portada img-fluid">
                        </div>

                        <!-- Columna derecha: cartel de bienvenida y botón de entrada -->
                        <div class="col-md-5 bg-light p-4 text-center">
                            <h3 class="text-primary mb-1">Gestión de Personas y Ciudades</h3>
                            <p class="text-muted small mb-4">Proyecto base 2026 &middot; PHP + MySQL + Bootstrap 5</p>

                            <!-- Si viene un mensaje de otra página, lo mostramos -->
                            <?php if ($mensaje !== ''): ?>
                                <div class="alert alert-info py-2"><?php echo htmlspecialchars($mensaje); ?></div>
                            <?php endif; ?>

                            <a href="src/index.php" class="btn btn-primary btn-lg w-100">INGRESAR</a>

                            <hr>

                            <p class="small text-muted mb-0">
                                Proyecto educativo IPEAyT 186<br>
                                Contacto de personas con su ciudad de nacimiento
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
