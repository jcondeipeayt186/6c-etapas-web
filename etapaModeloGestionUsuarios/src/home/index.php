<?php
/*
    src/home/index.php - Dashboard (ex index.php de etapa3)

    Si ingresa bien (login), acá llega. Muestra módulos de gestión personas
    solo si el rol del usuario es tipo 1 (Administrador) o 2 (Directivo),
    sino mensaje "no tiene módulos disponibles".
*/
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php?error=login_requerido");
    exit;
}
require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/personas.php';
require_once '../../lib/bd/ciudades.php';
require_once '../../lib/bd/usuarios.php';
require_once '../../lib/bd/roles.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();

// Refrescar tipos por si cambiaron roles desde otra sesión (opcional)
$tipos = $_SESSION['tipos'] ?? [];
$username = $_SESSION['username'] ?? 'Usuario';
$rolesNombres = isset($_SESSION['roles']) ? implode(', ', array_column($_SESSION['roles'], 'nombre')) : '-';

$cantidadPersonas = contarPersonas($conexion);
$cantidadCiudades = contarCiudades($conexion);
$cantidadUsuarios = contarUsuarios($conexion);
$cantidadRoles = contarRoles($conexion);

$tieneModulosPersonas = count(array_intersect([1,2], array_map('intval', $tipos))) > 0;
$esAdmin = in_array(1, array_map('intval', $tipos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username, array_map('intval', $tipos)); ?>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><img src="../../img/mysql.png" alt="MySQL" width="30" class="me-2">Bienvenido, <?php echo htmlspecialchars($username); ?></span>
                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($rolesNombres); ?></span>
                    </div>
                    <div class="card-body">
                        <p class="text-center text-muted">
                            Aplicación de gestión de <strong>personas</strong> y <strong>ciudades</strong> con base de datos MySQL
                        </p>
                        <p class="text-center small text-muted">Etapa 5: usuarios + roles N:M + control de acceso por tipo</p>

                        <?php if ($tieneModulosPersonas): ?>
                        <!-- ESTADÍSTICAS -->
                        <div class="row g-3 text-center">
                            <div class="col-md-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <h1 class="display-5 text-primary mb-0"><?php echo $cantidadPersonas; ?></h1>
                                        <p class="text-muted mb-0">Personas</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="../persona/viewPersona.php" class="btn btn-primary w-100 btn-sm">Gestionar personas</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <h1 class="display-5 text-info mb-0"><?php echo $cantidadCiudades; ?></h1>
                                        <p class="text-muted mb-0">Ciudades</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="../ciudad/viewCiudad.php" class="btn btn-info text-white w-100 btn-sm">Gestionar ciudades</a>
                                    </div>
                                </div>
                            </div>
                            <?php if ($esAdmin): ?>
                            <div class="col-md-3">
                                <div class="card border-warning h-100">
                                    <div class="card-body">
                                        <h1 class="display-5 text-warning mb-0"><?php echo $cantidadUsuarios; ?></h1>
                                        <p class="text-muted mb-0">Usuarios</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="../usuarios/viewUsuario.php" class="btn btn-warning w-100 btn-sm">Gestionar usuarios</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-dark h-100">
                                    <div class="card-body">
                                        <h1 class="display-5 text-dark mb-0"><?php echo $cantidadRoles; ?></h1>
                                        <p class="text-muted mb-0">Roles</p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="../roles/viewRol.php" class="btn btn-dark w-100 btn-sm">Gestionar roles</a>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-md-3">
                                <div class="card border-secondary h-100 opacity-50">
                                    <div class="card-body">
                                        <h1 class="display-5 text-secondary mb-0"><?php echo $cantidadUsuarios; ?></h1>
                                        <p class="text-muted mb-0">Usuarios</p>
                                        <small class="text-muted">(solo Administrador)</small>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-secondary w-100 btn-sm" disabled>Sin acceso</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-secondary h-100 opacity-50">
                                    <div class="card-body">
                                        <h1 class="display-5 text-secondary mb-0"><?php echo $cantidadRoles; ?></h1>
                                        <p class="text-muted mb-0">Roles</p>
                                        <small class="text-muted">(solo Administrador)</small>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-secondary w-100 btn-sm" disabled>Sin acceso</button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-center mt-4">
                            <a href="../persona/editPersona.php" class="btn btn-outline-primary me-2">Cargar nueva persona</a>
                            <a href="../ciudad/editCiudad.php" class="btn btn-outline-info">Cargar nueva ciudad</a>
                            <?php if ($esAdmin): ?>
                                <a href="../usuarios/editUsuario.php" class="btn btn-outline-warning ms-2">Crear usuario</a>
                                <a href="../roles/editRol.php" class="btn btn-outline-dark">Crear rol</a>
                            <?php endif; ?>
                        </div>
                        <?php if ($cantidadCiudades === 0): ?>
                            <div class="alert alert-warning mt-3 text-center">
                                No hay ciudades cargadas. Primero debe <a href="../ciudad/editCiudad.php" class="alert-link">crear una ciudad</a>.
                            </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <!-- SIN MÓDULOS -->
                        <div class="alert alert-secondary text-center py-4">
                            <h5 class="alert-heading">Sin módulos disponibles</h5>
                            <?php //?: lo que hace es asignar un valor predeterminado si la expresión es null ?>
                            <p class="mb-0">Su usuario (<strong><?php echo htmlspecialchars($rolesNombres ?: 'sin rol'); ?></strong>) no tiene permisos para acceder a los módulos de gestión de personas.</p>
                            <p class="small mb-0">Los módulos requieren rol tipo <strong>1 (Administrador)</strong> o <strong>2 (Directivo)</strong>.</p>
                            <hr>
                            <p class="small">Contacte al administrador para que le asigne un rol con acceso.</p>
                        </div>
                        <?php if ($esAdmin): ?>
                            <div class="row g-3 text-center">
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h1 class="display-5 text-warning"><?php echo $cantidadUsuarios; ?></h1>
                                            <p class="text-muted">Usuarios</p>
                                            <a href="../usuarios/viewUsuario.php" class="btn btn-warning">Gestionar usuarios</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-dark">
                                        <div class="card-body">
                                            <h1 class="display-5 text-dark"><?php echo $cantidadRoles; ?></h1>
                                            <p class="text-muted">Roles</p>
                                            <a href="../roles/viewRol.php" class="btn btn-dark">Gestionar roles</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
