<?php
/*
    ciudades.php - Gestion de ciudades (ETAPA 3)

    Esta pagina permite:
    1. Ver TODAS las ciudades cargadas en la base de datos
    2. Buscar ciudades por nombre
    3. Acceder al formulario para CREAR una ciudad nueva
    4. EDITAR una ciudad existente
    5. ELIMINAR una ciudad (solo si no tiene personas nacidas en ella)

    NOTA: ahora tambien muestra el codigo_postal, que pertenece a la ciudad.
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

// Recibimos el termino de busqueda desde la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Si hay busqueda, la funcion filtra por nombre con LIKE
$ciudades = obtenerTodasLasCiudades($conexion, $busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Ciudades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Gestion de Ciudades</h2>
                        <span class="badge bg-light text-dark"><?php echo count($ciudades); ?> ciudades</span>
                    </div>
                    <div class="card-body">

                        <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
                            <!-- Mensaje que se muestra cuando se intento eliminar una ciudad con personas -->
                            <div class="alert alert-danger">
                                No se puede eliminar la ciudad porque hay personas registradas que nacieron en ella.
                            </div>
                        <?php endif; ?>

                        <!--
                            BUSCADOR:
                            - method="GET": el termino viaja en la URL (?busqueda=...)
                            - action="ciudades.php": recarga esta misma pagina con el filtro
                        -->
                        <form method="GET" action="ciudades.php" class="row g-2 mb-3">
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
                                <a href="ciudades.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($ciudades)): ?>
                            <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron ciudades con ese nombre.' : 'No hay ciudades cargadas. Use el boton "Nueva ciudad".'; ?>
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
                                        <th>Cod. Postal</th>
                                        <th>Fundacion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                    <tr>
                                        <td><?php echo $ciudad['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['provincia']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['latitud']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['longitud']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['codigo_postal']); ?></td>
                                        <td><?php echo htmlspecialchars($ciudad['fecha_fundacion']); ?></td>
                                        <td>
                                            <!--
                                                EDITAR: enlace con ?id=X
                                                formCiudad.php detecta el id en la URL y carga los datos
                                            -->
                                            <a href="formCiudad.php?id=<?php echo $ciudad['id']; ?>"
                                               class="btn btn-warning btn-sm">Editar</a>

                                            <!--
                                                ELIMINAR: envia accion=eliminar a gestionCiudades.php
                                                gestionCiudades.php verifica si la ciudad tiene personas
                                            -->
                                            <form action="gestionCiudades.php" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Seguro que deseas eliminar esta ciudad?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <!-- Sin ?id: formCiudad.php abre en modo ALTA (ciudad nueva) -->
                        <a href="formCiudad.php" class="btn btn-primary">Nueva ciudad</a>
                        <a href="formPersona.php" class="btn btn-outline-primary">Cargar persona</a>
                        <a href="index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
