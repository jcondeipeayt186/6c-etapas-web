<?php
/*
    index.php - Portada + Login (ETAPA 5 - Gestión Usuarios)

    Esta es la página pública: muestra imagen de portada (image.png + cartel.png)
    y el formulario de ingreso: usuario, clave, Ingresar, Olvidé mi clave.
    Si ya está logueado, redirige a src/home/index.php
*/
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: src/home/index.php");
    exit;
}
$mensaje = $_GET['msg'] ?? ''; //?? operador de fusión de null (null coalescing operator) para asignar un valor predeterminado si no existe
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar a la aplicación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .portada-img { max-height: 460px; object-fit: contain; }
        .cartel-img { max-height: 120px; object-fit: contain; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <!-- Cartel superior -->
                <div class="text-center mb-3">
                    <img src="img/LogoCasta.png" alt="Cartel" class="cartel-img img-fluid rounded">
                </div>

                <div class="card shadow overflow-hidden">
                    <div class="row g-0">
                        <!-- Columna imagen portada -->
                        <div class="col-md-7 bg-white d-flex align-items-center justify-content-center p-3">
                            <img src="img/image.png" alt="Imagen de portada" class="portada-img img-fluid rounded">
                        </div>
                        <!-- Columna login -->
                        <div class="col-md-5 bg-light p-4">
                            <h3 class="text-center mb-1 text-primary">Ingresar a la aplicación</h3>
                            <p class="text-center text-muted small mb-4">Gestión de Personas y Ciudades</p>

                            <?php if ($error === 'login_requerido'): ?>
                                <div class="alert alert-warning py-2">Debe iniciar sesión para continuar.</div>
                            <?php endif; ?>
                            <?php if ($error === 'credenciales'): ?>
                                <div class="alert alert-danger py-2">Usuario o clave incorrectos.</div>
                            <?php endif; ?>
                            <?php if ($error === 'logout'): ?>
                                <div class="alert alert-info py-2">Sesión cerrada correctamente.</div>
                            <?php endif; ?>
                            <?php if ($mensaje === 'recuperar'): ?>
                                <div class="alert alert-info py-2">Contacte al administrador para restablecer su clave.</div>
                            <?php endif; ?>
                            <?php if (isset($_GET['error']) && $_GET['error'] !== 'login_requerido' && $_GET['error'] !== 'credenciales' && $_GET['error'] !== 'logout'): ?>
                                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($_GET['error']); ?></div>
                            <?php endif; ?>

                            <form action="src/auth/login.php" method="POST" autocomplete="off">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="ej: admin">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Clave</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required placeholder="clave">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Mostrar u ocultar clave" title="Mostrar/ocultar">
                                            <i class="bi bi-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-grid mb-2">
                                    <button type="submit" class="btn btn-primary">Ingresar</button>
                                </div>
                                <div class="text-center">
                                    <a href="src/auth/recuperar.php" class="small text-decoration-none">Olvidé mi clave</a>
                                </div>
                            </form>

                            <hr class="my-3">
                            <div class="small text-muted">
                                <strong>Usuarios de prueba:</strong><br>
                                admin / admin123 (Administrador)<br>
                                directivo / direc123 (Directivo)<br>
                                operador / oper123 (Operador - sin módulos)
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Etapa 5 - Dos Tablas + Gestión de Usuarios y Roles (N:M) &middot; PHP + MySQL + Bootstrap 5
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    </script>
</body>
</html>
