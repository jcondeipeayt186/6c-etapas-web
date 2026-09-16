<?php
// viewPersona.php - ETAPA 5 con control de acceso (tipo 1,2)
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (count(array_intersect([1,2], $tipos))===0) { header("Location: ../home/index.php?error=" . urlencode("No tiene permisos para Personas")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/personas.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$personas = obtenerTodasLasPersonas($conexion, $busqueda);
$username = $_SESSION['username'] ?? '';
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
    <?php navbar($username, $tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Personas Registradas</h4>
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="viewPersona.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">Buscar</button>
                            </div>
                        </form>
                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary py-2">Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong> <a href="viewPersona.php" class="float-end">Quitar filtro</a></div>
                        <?php endif; ?>
                        <?php if (empty($personas)): ?>
                            <div class="alert alert-info"><?php echo ($busqueda !== '') ? 'No se encontraron personas.' : 'No hay personas. Use "Nueva persona".'; ?></div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Email</th><th>Teléfono</th><th>Lugar Nac.</th><th>Provincia</th><th>Acciones</th>
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
                                        <td><?php echo htmlspecialchars($persona['telefono']); ?></td>
                                        <td><?php echo $persona['ciudad_nombre'] ? htmlspecialchars($persona['ciudad_nombre']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $persona['ciudad_provincia'] ? htmlspecialchars($persona['ciudad_provincia']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="editPersona.php?id=<?php echo $persona['id']; ?>" class="btn btn-warning btn-sm">Modificar</a>
                                                <form action="gestionPersona.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar persona?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
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
                        <a href="editPersona.php" class="btn btn-primary">Nueva persona</a>
                        <a href="../ciudad/viewCiudad.php" class="btn btn-outline-info">Ver ciudades</a>
                        <a href="../home/index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
