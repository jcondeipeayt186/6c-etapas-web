<?php
// editUsuario.php - Alta/edición usuario con selección múltiple de roles
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/usuarios.php';
require_once '../../lib/bd/roles.php';
require_once '../../lib/bd/usuario_roles.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$todosRoles = obtenerTodosLosRoles($conexion);
$usuario = null;
$esEdicion = false;
$rolesAsignados = [];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $usuario = obtenerUsuarioPorId($conexion, $id);
    if ($usuario) {
        $esEdicion = true;
        $rolesAsignados = obtenerIdsRolesDeUsuario($conexion, $id);
    }
}
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username, $tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-center">
                        <h4 class="mb-0"><?php echo $esEdicion ? 'Modificar Usuario' : 'Nuevo Usuario'; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                        <?php if (isset($_GET['id']) && !$esEdicion): ?>
                            <div class="alert alert-danger">Usuario no encontrado.</div>
                            <a href="viewUsuario.php" class="btn btn-primary w-100">Volver</a>
                        <?php else: ?>
                        <form action="gestionUsuario.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" required
                                       value="<?php echo $esEdicion ? htmlspecialchars($usuario['username']) : ''; ?>"
                                       placeholder="ej: juanperez">
                                <div class="form-text">Debe ser único, sin espacios.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $esEdicion ? 'Nueva clave (dejar vacío para no cambiar)' : 'Clave *'; ?></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="passwordUsuario" <?php echo $esEdicion ? '' : 'required'; ?> placeholder="mínimo 4 caracteres">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordUsuario" aria-label="Mostrar u ocultar clave" title="Mostrar/ocultar">
                                        <i class="bi bi-eye" id="toggleIconUsuario"></i>
                                    </button>
                                </div>
                                <?php if ($esEdicion): ?><div class="form-text">Se guarda con password_hash().</div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Roles (puede seleccionar varios)</label>
                                <?php if (empty($todosRoles)): ?>
                                    <div class="alert alert-warning py-2">No hay roles creados. <a href="../roles/editRol.php">Crear un rol</a> primero.</div>
                                <?php else: ?>
                                    <div class="border rounded p-2 bg-white" style="max-height:180px; overflow-y:auto;">
                                    <?php foreach ($todosRoles as $rol): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="<?php echo $rol['id']; ?>" id="rol<?php echo $rol['id']; ?>"
                                                <?php echo in_array($rol['id'], $rolesAsignados) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="rol<?php echo $rol['id']; ?>">
                                                <?php echo htmlspecialchars($rol['nombre']); ?>
                                                <span class="badge bg-secondary">tipo <?php echo $rol['tipo']; ?></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($rol['descripcion']); ?></small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                    <div class="form-text">Administrador=tipo 1, Directivo=tipo 2 (acceden a Personas/Ciudades).</div>
                                <?php endif; ?>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear usuario'; ?>
                                </button>
                            </div>
                            <?php if ($esEdicion): ?>
                                <div class="d-grid mt-2"><a href="editUsuario.php" class="btn btn-outline-secondary">Cancelar edición</a></div>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewUsuario.php" class="btn btn-outline-warning">Ver usuarios</a>
                        <a href="../home/index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePasswordUsuario').addEventListener('click', function () {
            const input = document.getElementById('passwordUsuario');
            const icon = document.getElementById('toggleIconUsuario');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    </script>
</body>
</html>
