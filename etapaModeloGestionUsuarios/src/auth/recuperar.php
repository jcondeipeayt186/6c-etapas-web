<?php
// recuperar.php - Olvidé mi clave (informativo, sin envío de mail para etapa didáctica)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar clave</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark text-center">
                    <h4 class="mb-0">Olvidé mi clave</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Por razones de seguridad, el restablecimiento de clave debe realizarlo un
                        <strong>Administrador</strong> del sistema.
                    </p>
                    <ul>
                        <li>Contacte al administrador (usuario <code>admin</code>).</li>
                        <li>El administrador puede editar su usuario en <code>Usuarios → Editar</code> y asignar una nueva clave.</li>
                        <li>Las claves se guardan hasheadas con <code>password_hash</code>; nadie puede ver su clave anterior.</li>
                    </ul>
                    <div class="alert alert-info small">
                        En un despliegue real aquí habría un flujo con token por email. Para esta etapa didáctica
                        se deja como proceso manual del administrador.
                    </div>
                    <div class="d-grid">
                        <a href="../../index.php" class="btn btn-primary">Volver al ingreso</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
