<?php
/*
    viewCiudad.php - Gestión y listado de ciudades (ETAPA 3 dosTablas)

    Página NAVEGABLE que permite:
    1. Ver TODAS las ciudades cargadas
    2. Buscar ciudades por nombre (filtro con LIKE, igual que en personas)
    3. Crear una ciudad nueva (link a editCiudad.php sin id)
    4. Editar una ciudad existente (link a editCiudad.php?id=X)
    5. Eliminar una ciudad (POST a gestionCiudad.php, solo si no tiene personas)

    El buscador usa GET (?busqueda=...) porque es una consulta, no una modificación.
*/

// Incluimos la librería de base de datos (obtenerConexion, obtenerTodasLasCiudades)
require_once '../../lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Recibimos el término de búsqueda desde la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Si hay búsqueda, la función filtra por nombre con LIKE '%termino%'
// Si no hay búsqueda (vacío), trae todas las ciudades
$ciudades = obtenerTodasLasCiudades($conexion, $busqueda);
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
                    <!-- Cabecera celeste con contador -->
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Gestión de Ciudades</h2>
                        <!-- count() cuenta cuántos elementos hay en el array -->
                        <span class="badge bg-light text-dark"><?php echo count($ciudades); ?> ciudades</span>
                    </div>
                    <div class="card-body">

                        <!-- Mensaje cuando se intentó eliminar una ciudad con personas (viene con ?error=1) -->
                        <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
                            <div class="alert alert-danger">
                                No se puede eliminar la ciudad porque hay personas registradas que nacieron en ella.
                                Primero debe reasignar o eliminar esas personas.
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje de error de validación que puede venir desde gestionCiudad.php -->
                        <?php if (isset($_GET['error']) && $_GET['error'] !== '1'): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <!--
                            BUSCADOR (igual que en viewPersona.php pero para ciudades):
                            - method="GET": el término viaja en la URL (?busqueda=...)
                            - action="viewCiudad.php": recarga esta misma página con el filtro
                        -->
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

                        <!-- Si hay un filtro activo, mostramos un cartel con opción de quitarlo -->
                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="viewCiudad.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje cuando no hay ciudades -->
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
                                        <th>Latitud</th>
                                        <th>Longitud</th>
                                        <th>Cód. Postal</th>
                                        <th>Fundación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                    <tr>
                                        <td><?php echo $ciudad['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['provincia']); ?></td>
                                        <!-- htmlspecialchars para coordenadas también, por si vienen como texto -->
                                        <td><?php echo htmlspecialchars($ciudad['latitud']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['longitud']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['codigo_postal']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['fecha_fundacion']); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <!--
                                                    EDITAR: link con ?id=X
                                                    editCiudad.php detecta el id en la URL (GET) y carga los datos
                                                -->
                                                <a href="editCiudad.php?id=<?php echo $ciudad['id']; ?>"
                                                   class="btn btn-warning btn-sm">Editar</a>

                                                <!--
                                                    ELIMINAR: envía accion=eliminar a gestionCiudad.php por POST
                                                    gestionCiudad.php verifica si la ciudad tiene personas (clave foránea)
                                                    onsubmit: confirm() de JS pregunta antes de enviar
                                                -->
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
                        <!-- Sin ?id: editCiudad.php abre en modo ALTA (ciudad nueva) -->
                        <a href="editCiudad.php" class="btn btn-primary">Nueva ciudad</a>
                        <a href="../persona/editPersona.php" class="btn btn-outline-primary">Cargar persona</a>
                        <a href="../persona/viewPersona.php" class="btn btn-outline-success">Ver personas</a>
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
