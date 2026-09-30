<?php
/*
    viewPersona.php - Tabla con todas las personas

    ¿Navegable? Sí. Esta es una de las páginas principales del módulo.

    Hace cuatro cosas:
    1. Lista las personas (con el nombre de su ciudad, gracias al JOIN)
    2. Permite filtrar por nombre con el buscador (GET ?busqueda=...)
    3. Permite editar (botón Modificar) y eliminar cada persona
    4. Permite enviar un mail a una persona (botón + modal de Bootstrap)

    El buscador usa GET porque es una CONSULTA (no cambia nada en la base).
    Los botones de eliminar y enviar mail usan POST porque SÍ modifican
    o disparan una acción.
*/

// Librería de la base de datos (conexión + funciones de personas)
require_once '../../lib/bd/gestionBaseDatos.php';
// Librería de HTML compartido (piePagina, mostrarAlerta)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Término de búsqueda que llega por la URL (?busqueda=Julián)
// isset() evita el error cuando se entra sin buscar
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Trae todas las personas o solo las que coinciden con el término
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
            <div class="col-md-11">
                <div class="card shadow">
                    <!-- Cabecera verde con el contador de registros -->
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Personas Registradas</h2>
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">

                        <!-- Aviso de que el mail se envió (vuelve de enviarMail.php) -->
                        <?php if (isset($_GET['mail']) && $_GET['mail'] === 'ok'): ?>
                            <div class="alert alert-success">El mail se envió correctamente.</div>
                        <?php endif; ?>

                        <!--
                            Aviso de que el mail se envió, pero no se pudo anotar en el
                            archivo de log (típico tema de permisos con Apache)
                        -->
                        <?php if (isset($_GET['log']) && trim($_GET['log']) !== ''): ?>
                            <div class="alert alert-warning"><?php echo htmlspecialchars($_GET['log']); ?></div>
                        <?php endif; ?>

                        <!-- Aviso de que el mail NO se pudo enviar -->
                        <?php if (isset($_GET['mail']) && $_GET['mail'] === 'error'): ?>
                            <div class="alert alert-danger">
                                No se pudo enviar el mail.
                                <?php if (isset($_GET['detalle'])): ?>
                                    <?php echo htmlspecialchars($_GET['detalle']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!--
                            BUSCADOR:
                            - method="GET": el término viaja en la URL (?busqueda=...)
                            - value: deja el término escrito después de buscar
                        -->
                        <form method="GET" action="viewPersona.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control"
                                       placeholder="Buscar por nombre de persona..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">Buscar</button>
                            </div>
                        </form>

                        <!-- Si hay un filtro activo, se muestra con opción de quitarlo -->
                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="viewPersona.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($personas)): ?>
                            <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron personas con ese nombre.' : 'No hay personas registradas aún. Use "Nueva persona".'; ?>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Foto</th>
                                        <th>Nombre</th>
                                        <th>DNI</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Lugar de Nacimiento</th>
                                        <th>Provincia</th>
                                        <th>CV</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // foreach recorre el array $personas persona por persona
                                    foreach ($personas as $persona):
                                    ?>
                                    <tr>
                                        <td><?php echo $persona['id']; ?></td>

                                        <!--
                                            FOTO (avatar_path):
                                            En la base está guardada la ruta (files/avatars/...).
                                            Como la página está en src/persona/, hay que subir
                                            dos carpetas con ../../ para llegar a la raíz.
                                        -->
                                        <td>
                                            <?php if ($persona['avatar_path']): ?>
                                                <img src="../../<?php echo htmlspecialchars($persona['avatar_path']); ?>"
                                                     alt="Foto de <?php echo htmlspecialchars($persona['nombre']); ?>"
                                                     width="45" class="rounded-circle border">
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['email']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['telefono']); ?></td>

                                        <!-- Ciudad y provincia: vienen del JOIN con la tabla ciudades -->
                                        <td><?php echo $persona['ciudad_nombre'] ? htmlspecialchars($persona['ciudad_nombre']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $persona['ciudad_provincia'] ? htmlspecialchars($persona['ciudad_provincia']) : '<span class="text-muted">—</span>'; ?></td>

                                        <!-- CURRÍCULUM (cv_path): link para abrir o descargar el archivo -->
                                        <td>
                                            <?php if ($persona['cv_path']): ?>
                                                <a href="../../<?php echo htmlspecialchars($persona['cv_path']); ?>"
                                                   target="_blank" class="btn btn-outline-secondary btn-sm">Ver CV</a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <!-- MODIFICAR: link con ?id= (precarga el formulario) -->
                                                <a href="editPersona.php?id=<?php echo $persona['id']; ?>"
                                                   class="btn btn-warning btn-sm">Modificar</a>

                                                <!--
                                                    ENVIAR MAIL: abre el modal de Bootstrap.
                                                    Los atributos data-* llevan los datos de la persona:
                                                    JavaScript los copia al modal al hacer clic.
                                                -->
                                                <button type="button" class="btn btn-info btn-sm btn-enviar-mail"
                                                        data-bs-toggle="modal" data-bs-target="#modalMail"
                                                        data-id="<?php echo $persona['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>"
                                                        data-email="<?php echo htmlspecialchars($persona['email']); ?>">
                                                    Enviar mail
                                                </button>

                                                <!--
                                                    ELIMINAR: manda accion=eliminar por POST.
                                                    onsubmit: el confirm() de JavaScript pregunta antes de borrar.
                                                -->
                                                <form action="gestionPersona.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar esta persona?');">
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
                        <a href="../index.php" class="btn btn-outline-primary">Inicio</a>
                        <a href="../../index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!--  MODAL DE ENVÍO DE MAIL (Bootstrap 5)                   -->
    <!-- ===================================================== -->
    <!--
        Un modal es una ventana chica que aparece encima de la página.
        Se abre con data-bs-toggle="modal" en el botón.
        - data-bs-target dice QUÉ modal se abre (el id del modal)
        - el formulario del modal manda los datos a enviarMail.php
    -->
    <div class="modal fade" id="modalMail" tabindex="-1" aria-labelledby="tituloModalMail" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="enviarMail.php" method="POST">

                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="tituloModalMail">Enviar mail a la persona</h5>
                        <!-- Botón para cerrar el modal -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <!-- ID de la persona (lo completa JavaScript al hacer clic en el botón) -->
                        <input type="hidden" name="id" id="mailId">

                        <div class="mb-3">
                            <label for="mailDestinatario" class="form-label">Para (email)</label>
                            <input type="email" class="form-control" id="mailDestinatario" name="para" required>
                        </div>

                        <div class="mb-3">
                            <label for="mailAsunto" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="mailAsunto" name="asunto" required
                                   value="Contacto desde la aplicación">
                        </div>

                        <div class="mb-3">
                            <label for="mailMensaje" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="mailMensaje" name="mensaje" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /*
            Al hacer clic en un botón "Enviar mail", copiamos los datos de la persona
            (vienen en los atributos data-*) dentro del modal.
            El botón tiene: data-id, data-nombre y data-email
        */
        document.querySelectorAll('.btn-enviar-mail').forEach(function (boton) {
            boton.addEventListener('click', function () {
                document.getElementById('mailId').value = this.dataset.id;
                document.getElementById('mailDestinatario').value = this.dataset.email;
                document.getElementById('mailAsunto').value = 'Contacto a ' + this.dataset.nombre;
                document.getElementById('mailMensaje').value =
                    'Estimada/o ' + this.dataset.nombre + ':\n\nLe escribimos desde la aplicación.\n\nSaludos.';
            });
        });
    </script>
</body>
</html>
