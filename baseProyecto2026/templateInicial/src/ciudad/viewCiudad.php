<?php
/*
    viewCiudad.php - Gestión y listado de ciudades

    ¿Navegable? Sí. Permite:
    1. Ver todas las ciudades cargadas
    2. Buscar ciudades por nombre (filtro con LIKE)
    3. Crear y editar ciudades (editCiudad.php)
    4. Eliminar una ciudad (solo si no tiene personas)
    5. Ver cuántas personas nacieron en cada ciudad
*/

// Librería de la base de datos (conexión + funciones de ciudades)
require_once '../../lib/bd/gestionBaseDatos.php';
// Librería de HTML compartido (piePagina, mostrarAlerta)
require_once '../../lib/html/funcionesHTML.php';
// Librería de utilidades (fechaFormato para mostrar las fechas como 14/05/2026)
require_once '../../lib/utils/varios.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Término de búsqueda de la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Lista de ciudades (filtrada o completa)
$ciudades = obtenerTodasLasCiudades($conexion, $busqueda);

/*
    CANTIDAD DE PERSONAS POR CIUDAD
    contarPersonasPorCiudad() hace un SELECT con COUNT + GROUP BY y devuelve
    un array así:  [1 => 5, 2 => 0, 7 => 12]  (id de ciudad => cantidad)
*/
$personasPorCiudad = contarPersonasPorCiudad($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ciudades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow">
                    <!-- Cabecera celeste con el contador de ciudades -->
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Gestión de Ciudades</h2>
                        <span class="badge bg-light text-dark"><?php echo count($ciudades); ?> ciudades</span>
                    </div>
                    <div class="card-body">

                        <!-- Error al eliminar una ciudad que tiene personas -->
                        <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
                            <div class="alert alert-danger">
                                No se puede eliminar la ciudad porque hay personas registradas que nacieron en ella.
                                Primero debe reasignar o eliminar esas personas.
                            </div>
                        <?php endif; ?>

                        <!-- Otros errores de validación que vienen de gestionCiudad.php -->
                        <?php if (isset($_GET['error']) && $_GET['error'] !== '1'): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <!-- BUSCADOR (igual que en viewPersona.php, pero para ciudades) -->
                        <form method="GET" action="viewCiudad.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control"
                                       placeholder="Buscar por nombre de ciudad..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-info w-100">Buscar</button>
                            </div>
                        </form>

                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="viewCiudad.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($ciudades)): ?>
                            <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron ciudades con ese nombre.' : 'No hay ciudades cargadas. Use el botón "Nueva ciudad".'; ?>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Ciudad</th>
                                        <th>Provincia</th>
                                        <th>Cód. Postal</th>
                                        <th>Latitud</th>
                                        <th>Longitud</th>
                                        <th>Fundación</th>
                                        <!-- Columna nueva: cuántas personas nacieron ahí -->
                                        <th>Personas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                    <tr>
                                        <td><?php echo $ciudad['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['provincia']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['codigo_postal']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['latitud']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['longitud']); ?></td>
                                        <td><?php echo fechaFormato($ciudad['fecha_fundacion']); ?></td>

                                        <!--
                                            CANTIDAD DE PERSONAS:
                                            buscamos el id de la ciudad en el array
                                            $personasPorCiudad. Con ?? 0 mostramos 0
                                            cuando la ciudad todavía no tiene personas.
                                        -->
                                        <td>
                                            <?php $cantidad = isset($personasPorCiudad[$ciudad['id']]) ? $personasPorCiudad[$ciudad['id']] : 0; ?>
                                            <?php if ($cantidad > 0): ?>
                                                <span class="badge bg-primary"><?php echo $cantidad; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <!-- EDITAR: link con ?id= para precargar el formulario -->
                                                <a href="editCiudad.php?id=<?php echo $ciudad['id']; ?>"
                                                   class="btn btn-warning btn-sm">Editar</a>

                                                <!-- ELIMINAR: POST con accion=eliminar.
                                                     gestionCiudad.php verifica que no haya personas
                                                     (porque la clave foránea lo impide) -->
                                                <form action="gestionCiudad.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta ciudad?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="editCiudad.php" class="btn btn-primary">Nueva ciudad</a>
                        <a href="../persona/viewPersona.php" class="btn btn-outline-success">Ver personas</a>
                        <a href="../index.php" class="btn btn-outline-primary">Inicio</a>
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
