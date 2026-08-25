<?php
/*
    resultado.php - Tabla con todas las personas (ETAPA 3)

    Muestra todas las personas junto con la ciudad donde nacieron.
    La ciudad se obtiene con un JOIN entre las tablas personas y ciudades.

    Incluye un BUSCADOR que filtra por el nombre de la persona.
    El termino de busqueda llega por GET (?busqueda=...), porque un buscador
    "no guarda datos" sino que pide una consulta, por eso usamos GET y no POST.
*/

require_once 'bd/gestionBaseDatos.php';

$conexion = obtenerConexion();

// Recibimos el termino de busqueda desde la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// La funcion recibe el termino: si esta vacio, trae todas las personas;
// si no, filtra por nombre usando LIKE
$personas = obtenerTodasLasPersonas($conexion, $busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personas Registradas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Personas Registradas</h2>
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">

                        <!--
                            BUSCADOR:
                            - method="GET": el termino viaja en la URL (?busqueda=...)
                            - action="resultado.php": recarga esta misma pagina con el filtro
                            - value=... : deja el termino escrito despues de buscar
                        -->
                        <form method="GET" action="resultado.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control"
                                       placeholder="Buscar por nombre de persona..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">Buscar</button>
                            </div>
                        </form>

                        <?php if ($busqueda !== ''): ?>
                            <!-- Indicamos que hay un filtro activo y damos opcion de quitarlo -->
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="resultado.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($personas)): ?>
                            <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron personas con ese nombre.' : 'No hay personas registradas aun.'; ?>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>DNI</th>
                                        <th>Email</th>
                                        <th>Lugar de Nacimiento</th>
                                        <th>Provincia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($personas as $persona): ?>
                                    <tr>
                                        <td><?php echo $persona['id']; ?></td>
                                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['email']); ?></td>

                                        <!--
                                            CIUDAD VIA JOIN:
                                            $persona['ciudad_nombre'] viene de la tabla ciudades
                                            gracias al JOIN hecho en gestionBaseDatos.php.
                                        -->
                                        <td><?php echo htmlspecialchars($persona['ciudad_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['ciudad_provincia']); ?></td>

                                        <td>
                                            <!-- Boton eliminar: envia accion=eliminar con el id de la persona -->
                                            <form action="procesando.php" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Seguro que deseas eliminar esta persona?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
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
                        <a href="formPersona.php" class="btn btn-primary">Nueva persona</a>
                        <a href="index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
