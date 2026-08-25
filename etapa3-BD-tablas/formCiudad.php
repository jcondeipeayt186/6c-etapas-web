<?php
/*
    formCiudad.php - Formulario de alta y edicion de ciudades (ETAPA 3)

    Este formulario sirve para DOS cosas:
    1. CREAR una ciudad nueva    -> cuando se accede sin ?id= (ej: formCiudad.php)
    2. EDITAR una ciudad         -> cuando se accede con ?id= (ej: formCiudad.php?id=3)

    En el caso de edicion, se busca la ciudad en la base de datos y
    se precargan sus datos en los campos del formulario.
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

// Variable que contendra la ciudad a editar (o null si es alta nueva)
$ciudad = null;
$esEdicion = false;

// Si la URL trae ?id=, estamos editando una ciudad existente
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $ciudad = obtenerCiudadPorId($conexion, $id);
    $esEdicion = true;
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
                    <div class="card-header bg-primary text-white text-center">
                        <h2><?php echo $esEdicion ? 'Editar Ciudad' : 'Nueva Ciudad'; ?></h2>
                    </div>
                    <div class="card-body">

                        <?php if (isset($_GET['error'])): ?>
                            <!-- Mensaje de error de validacion (latitud/longitud fuera de rango) -->
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($esEdicion && !$ciudad): ?>
                            <!-- Si se pidio editar una ciudad que no existe -->
                            <div class="alert alert-danger">La ciudad no existe.</div>
                            <a href="ciudades.php" class="btn btn-primary">Volver a ciudades</a>
                        <?php else: ?>

                        <!--
                            El formulario siempre envia a gestionCiudades.php
                            - Si es alta: no lleva id, gestionCiudades hará INSERT
                            - Si es edicion: lleva id oculto, gestionCiudades hará UPDATE
                        -->
                        <form action="gestionCiudades.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <!-- Campo oculto con el id de la ciudad y la accion "actualizar" -->
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
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
                                    <!-- step="any" permite decimales como -31.416667 -->
                                    <input type="number" step="any" class="form-control" id="latitud" name="latitud"
                                           value="<?php echo $esEdicion ? $ciudad['latitud'] : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitud" class="form-label">Longitud</label>
                                    <input type="number" step="any" class="form-control" id="longitud" name="longitud"
                                           value="<?php echo $esEdicion ? $ciudad['longitud'] : ''; ?>">
                                </div>
                            </div>

                            <!-- codigo_postal: ahora se carga en la CIUDAD, no en la persona -->
                            <div class="mb-3">
                                <label for="codigo_postal" class="form-label">Codigo Postal</label>
                                <input type="text" class="form-control" id="codigo_postal" name="codigo_postal"
                                       value="<?php echo $esEdicion ? htmlspecialchars($ciudad['codigo_postal']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="fecha_fundacion" class="form-label">Fecha de Fundacion</label>
                                <input type="date" class="form-control" id="fecha_fundacion" name="fecha_fundacion"
                                       value="<?php echo $esEdicion ? $ciudad['fecha_fundacion'] : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($ciudad['descripcion']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear ciudad'; ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="ciudades.php" class="btn btn-outline-secondary">Volver a ciudades</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
