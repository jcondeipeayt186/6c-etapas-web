<?php
// viewUsuario.php - Listado de usuarios con buscador y roles
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador puede gestionar usuarios")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/usuarios.php';
require_once '../../lib/bd/usuario_roles.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$usuarios = obtenerTodosLosUsuarios($conexion, $busqueda);
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username, $tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow">
                    <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Gestión de Usuarios</h4>
                        <span class="badge bg-dark"><?php echo count($usuarios); ?> usuarios</span>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='creado'): ?><div class="alert alert-success">Usuario creado correctamente.</div><?php endif; ?>
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='actualizado'): ?><div class="alert alert-success">Usuario actualizado.</div><?php endif; ?>
                        <?php if (isset($_GET['msg']) && $_GET['msg']==='eliminado'): ?><div class="alert alert-success">Usuario eliminado.</div><?php endif; ?>
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>

                        <form method="GET" action="viewUsuario.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control" placeholder="Buscar por username..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning w-100">Buscar</button>
                            </div>
                        </form>
                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary py-2">Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong> <a href="viewUsuario.php" class="float-end">Quitar filtro</a></div>
                        <?php endif; ?>

                        <?php if (empty($usuarios)): ?>
                            <div class="alert alert-info">No hay usuarios. Cree uno con "Nuevo usuario".</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Roles</th>
                                        <th>Creación</th>
                                        <th>Último acceso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $u): 
                                        $roles = obtenerRolesDeUsuario($conexion, $u['id']);
                                    ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                        <td>
                                            <?php if (empty($roles)): ?>
                                                <span class="badge bg-secondary">sin rol</span>
                                            <?php else: foreach ($roles as $r): ?>
                                                <span class="badge <?php echo $r['tipo']==1?'bg-danger':($r['tipo']==2?'bg-primary':'bg-secondary'); ?>">
                                                    <?php echo htmlspecialchars($r['nombre']); ?> (tipo <?php echo $r['tipo']; ?>)
                                                </span>
                                            <?php endforeach; endif; ?>
                                        </td>
                                        <td class="small"><?php echo htmlspecialchars($u['fechaCreacion']); ?></td>
                                        <td class="small"><?php echo $u['fechaUltimoAcceso'] ? htmlspecialchars($u['fechaUltimoAcceso']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="editUsuario.php?id=<?php echo $u['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="gestionUsuario.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario <?php echo htmlspecialchars($u['username']); ?>?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" <?php echo ($u['id']==$_SESSION['usuario_id'])?'disabled title="No puedes eliminarte a ti mismo"':''; ?>>Eliminar</button>
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
                        <a href="editUsuario.php" class="btn btn-warning">Nuevo usuario</a>
                        <a href="../roles/viewRol.php" class="btn btn-outline-dark">Ver roles</a>
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
