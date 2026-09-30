<?php
/*
    editModulo.php - PLANTILLA del formulario de alta/edición

    Este archivo NO funciona tal cual: es una PLANTILLA para crear
    el formulario de un módulo nuevo.

    CÓMO USARLA:
    1. Copiala y renombrala (por ejemplo editPais.php)
    2. Cambiá MODULO / Modulo por el nombre de tu módulo
    3. Agregá o quitá campos según la tabla de tu módulo

    El mismo archivo sirve para los dos casos:
    - Sin ?id= en la URL  -> muestra el formulario VACÍO (alta)
    - Con ?id= en la URL   -> busca el registro y lo muestra CARGADO (edición)

    El formulario siempre manda los datos a gestionModulo.php,
    que es el que se encarga de guardarlos.
*/

// 1. Librerías
require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/html/funcionesHTML.php';
require_once '../../lib/utils/varios.php';

// 1.b Aviso: al editar necesitamos obtenerModuloPorId(). Si el alumno todavía
// no la escribió, mostramos el mensaje de "plantilla sin terminar".
if (isset($_GET['id']) && !function_exists('obtenerModuloPorId')) {
    require_once 'avisoPlantilla.php';
    mostrarAvisoPlantilla('obtenerModuloPorId');
}

// 2. Conexión
$conexion = obtenerConexion();

// 3. Modo edición
$modulo = null;    // el registro a editar
$esEdicion = false;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $modulo = obtenerModuloPorId($conexion, $id);
    if ($modulo) {
        $esEdicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> MODULO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><?php echo $esEdicion ? 'Modificar MODULO' : 'Nuevo MODULO'; ?></h2>
                    </div>
                    <div class="card-body">

                        <!-- Errores que pueden venir de gestionModulo.php -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <!-- Se pidió editar algo que no existe -->
                        <?php if (isset($_GET['id']) && !$modulo): ?>
                            <div class="alert alert-danger">El registro no existe o el ID es inválido.</div>
                            <a href="viewModulo.php" class="btn btn-primary w-100">Volver al listado</a>
                        <?php else: ?>

                        <!-- El formulario manda todo a gestionModulo.php -->
                        <form action="gestionModulo.php" method="POST">
                            <!-- Campos ocultos: indican qué hacer y sobre qué registro -->
                            <?php if ($esEdicion): ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $modulo['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       value="<?php echo $esEdicion ? htmlspecialchars($modulo['nombre']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($modulo['descripcion']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear'; ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewModulo.php" class="btn btn-outline-primary">Volver al listado</a>
                        <a href="../index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
