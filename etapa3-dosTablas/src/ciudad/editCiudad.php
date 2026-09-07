<?php
/*
    editCiudad.php - Formulario de alta y edición de ciudades (ETAPA 3 dosTablas)

    Este formulario sirve para DOS cosas (mismo archivo, dos modos):
    1. CREAR una ciudad nueva  -> cuando se accede sin ?id=  (ej: editCiudad.php)
    2. EDITAR una ciudad        -> cuando se accede con ?id=   (ej: editCiudad.php?id=3)

    En el caso de edición, se busca la ciudad en la base de datos y
    se precargan sus datos en los campos del formulario (value="...").
    El botón y el título cambian según el modo.
*/

// Incluimos la librería de base de datos (obtenerConexion, obtenerCiudadPorId)
require_once '../../lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Variables para el modo edición
$ciudad = null;     // Contendrá la ciudad a editar o null si es alta
$esEdicion = false; // true si ?id= viene en la URL y la ciudad existe

// Si la URL trae ?id=, estamos editando una ciudad existente
if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // (int) por seguridad
    $ciudad = obtenerCiudadPorId($conexion, $id); // SELECT * WHERE id = ?
    if ($ciudad) {
        $esEdicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Ciudad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <!-- Cabecera celeste: título dinámico -->
                    <div class="card-header bg-info text-white text-center">
                        <h2><?php echo $esEdicion ? 'Editar Ciudad' : 'Nueva Ciudad'; ?></h2>
                    </div>
                    <div class="card-body">

                        <!-- Mensaje de error de validación que puede venir desde gestionCiudad.php -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Si se pidió editar una ciudad que no existe -->
                        <?php if (isset($_GET['id']) && !$ciudad): ?>
                            <div class="alert alert-danger">La ciudad no existe o el ID es inválido.</div>
                            <a href="viewCiudad.php" class="btn btn-primary">Volver a ciudades</a>
                        <?php else: ?>

                        <!--
                            El formulario siempre envía a gestionCiudad.php
                            - Si es alta: lleva accion=crear, gestionCiudad hará INSERT
                            - Si es edición: lleva id oculto + accion=actualizar, gestionCiudad hará UPDATE
                        -->
                        <form action="gestionCiudad.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <!-- Campos ocultos para el UPDATE -->
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
                            <?php else: ?>
                                <!-- En modo alta, accion=crear -->
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <!-- value precargado si es edición, vacío si es alta -->
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($ciudad['nombre']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($ciudad['provincia']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitud" class="form-label">Latitud</label>
                                    <!-- step="any" permite decimales como -31.416667. No es required: puede ser null -->
                                    <input type="number" step="any" class="form-control" id="latitud" name="latitud"
                                           value="<?php echo $esEdicion ? htmlspecialchars($ciudad['latitud']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitud" class="form-label">Longitud</label>
                                    <input type="number" step="any" class="form-control" id="longitud" name="longitud"
                                           value="<?php echo $esEdicion ? htmlspecialchars($ciudad['longitud']) : ''; ?>">
                                </div>
                            </div>

                            <!-- codigo_postal: pertenece a la CIUDAD, no a la persona (normalización) -->
                            <div class="mb-3">
                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal"
                                       value="<?php echo $esEdicion ? htmlspecialchars($ciudad['codigo_postal']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="fecha_fundacion" class="form-label">Fecha de Fundación</label>
                                <!-- type=date espera YYYY-MM-DD. Puede estar vacío (null en la BD) -->
                                <input type="date" class="form-control" id="fecha_fundacion" name="fecha_fundacion"
                                       value="<?php echo $esEdicion ? htmlspecialchars($ciudad['fecha_fundacion']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <!-- textarea no usa value, el contenido va entre las etiquetas -->
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($ciudad['descripcion']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <!-- Botón dinámico según el modo -->
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear ciudad'; ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewCiudad.php" class="btn btn-outline-info">Volver a ciudades</a>
                        <a href="../../index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
