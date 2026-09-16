<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador puede gestionar roles")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/roles.php';
require_once '../../lib/bd/usuario_roles.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$roles = obtenerTodosLosRoles($conexion, $busqueda);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username, $tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Gestión de Roles</h4>
                        <span class="badge bg-light text-dark"><?php echo count($roles); ?> roles</span>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='creado'): ?><div class="alert alert-success">Rol creado.</div><?php endif; ?>
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='actualizado'): ?><div class="alert alert-success">Rol actualizado.</div><?php endif; ?>
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='eliminado'): ?><div class="alert alert-success">Rol eliminado.</div><?php endif; ?>
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>

                        <form method="GET" action="viewRol.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre de rol..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark w-100">Buscar</button>
                            </div>
                        </form>
                        <?php if ($busqueda !== ''): ?><div class="alert alert-secondary py-2">Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong> <a href="viewRol.php" class="float-end">Quitar filtro</a></div><?php endif; ?>

                        <?php if (empty($roles)): ?><div class="alert alert-info">No hay roles. Use "Nuevo rol".</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Tipo</th>
                                        <th>Usuarios</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $r): 
                                        $usuarios = obtenerUsuariosDeRol($conexion, $r['id']);
                                    ?>
                                    <tr>
                                        <td><?php echo $r['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($r['nombre']); ?></strong></td>
                                        <td class="small"><?php echo htmlspecialchars($r['descripcion'] ?? ''); ?></td>
                                        <td><span class="badge <?php echo $r['tipo']==1?'bg-danger':($r['tipo']==2?'bg-primary':'bg-secondary'); ?>"><?php echo $r['tipo']==1?'Admin':($r['tipo']==2?'Directivo':'Tipo '.$r['tipo']); ?> (<?php echo $r['tipo']; ?>)</span></td>
                                        <td><span class="badge bg-info"><?php echo count($usuarios); ?></span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="editRol.php?id=<?php echo $r['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="gestionRol.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar rol <?php echo htmlspecialchars($r['nombre']); ?>?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
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
                        <a href="editRol.php" class="btn btn-dark">Nuevo rol</a>
                        <a href="../usuarios/viewUsuario.php" class="btn btn-outline-warning">Ver usuarios</a>
                        <a href="../home/index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
                <div class="alert alert-info mt-3 small">
                    <strong>Tipos:</strong> 1 = Administrador (gestiona todo), 2 = Directivo (gestiona personas/ciudades), 3+ = otros sin acceso a módulos.
                    El control en <code>src/home/index.php</code> muestra personas solo si <code>tipo IN (1,2)</code>.
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
