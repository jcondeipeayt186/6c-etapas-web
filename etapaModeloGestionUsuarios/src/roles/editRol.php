<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (!in_array(1, $tipos)) { header("Location: ../home/index.php?error=" . urlencode("Solo Administrador")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/roles.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$rol = null;
$esEdicion = false;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $rol = obtenerRolPorId($conexion, $id);
    if ($rol) $esEdicion = true;
}
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username, $tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><?php echo $esEdicion ? 'Modificar Rol' : 'Nuevo Rol'; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                        <?php if (isset($_GET['id']) && !$esEdicion): ?>
                            <div class="alert alert-danger">Rol no encontrado.</div>
                            <a href="viewRol.php" class="btn btn-primary w-100">Volver</a>
                        <?php else: ?>
                        <form action="gestionRol.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $rol['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required
                                       value="<?php echo $esEdicion ? htmlspecialchars($rol['nombre']) : ''; ?>"
                                       placeholder="ej: Administrador, Auditor">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2" placeholder="Qué hace este rol..."><?php echo $esEdicion ? htmlspecialchars($rol['descripcion'] ?? '') : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo (TINYINT) *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="1" <?php echo ($esEdicion && $rol['tipo']==1)?'selected':''; ?>>1 - Administrador (acceso total)</option>
                                    <option value="2" <?php echo ($esEdicion && $rol['tipo']==2)?'selected':''; ?>>2 - Directivo (personas/ciudades)</option>
                                    <option value="3" <?php echo ($esEdicion && $rol['tipo']==3)?'selected':''; ?>>3 - Operador (sin módulos)</option>
                                    <option value="4" <?php echo ($esEdicion && $rol['tipo']==4)?'selected':''; ?>>4 - Invitado</option>
                                </select>
                                <div class="form-text">El tipo controla el acceso en home (1 y 2 ven personas).</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-dark'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear rol'; ?>
                                </button>
                            </div>
                            <?php if ($esEdicion): ?><div class="d-grid mt-2"><a href="editRol.php" class="btn btn-outline-secondary">Cancelar</a></div><?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewRol.php" class="btn btn-outline-dark">Ver roles</a>
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
