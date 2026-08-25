<!DOCTYPE html>
<!-- Esta pagina MUESTRA los datos que el usuario envio desde index.php -->
<!-- No usa base de datos, solo recibe los datos por POST y los muestra -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h2>Datos Ingresados</h2>
                    </div>
                    <div class="card-body">

                        <?php
                        // $_SERVER['REQUEST_METHOD'] indica como llegaron los datos a esta pagina
                        // Si es POST, significa que el formulario fue enviado correctamente
                        if ($_SERVER['REQUEST_METHOD'] === 'POST'):
                        ?>

                        <!--
                            TABLA QUE MUESTRA LOS DATOS:
                            $_POST es un array superglobal de PHP que contiene todos los datos
                            enviados por el formulario via method="POST"
                            Ejemplo: $_POST['nombre'] contiene lo que el usuario escribio en el campo nombre
                        -->
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="table-light" style="width: 35%;">Nombre</th>
                                    <!--
                                        htmlspecialchars() convierte caracteres especiales a HTML
                                        por ejemplo: < se convierte en &lt;
                                        Esto previene ataques de INYECCION DE CODIGO (XSS)
                                    -->
                                    <td><?php echo htmlspecialchars($_POST['nombre']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Apellido</th>
                                    <td><?php echo htmlspecialchars($_POST['apellido']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">DNI</th>
                                    <td><?php echo htmlspecialchars($_POST['dni']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">CUIT</th>
                                    <td><?php echo htmlspecialchars($_POST['cuit']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Fecha de Nacimiento</th>
                                    <td><?php echo htmlspecialchars($_POST['fecha_nacimiento']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Email</th>
                                    <td><?php echo htmlspecialchars($_POST['email']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Telefono</th>
                                    <td><?php echo htmlspecialchars($_POST['telefono']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Direccion</th>
                                    <td><?php echo htmlspecialchars($_POST['direccion']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Ciudad</th>
                                    <td><?php echo htmlspecialchars($_POST['ciudad']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Provincia</th>
                                    <td><?php echo htmlspecialchars($_POST['provincia']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Codigo Postal</th>
                                    <td><?php echo htmlspecialchars($_POST['codigo_postal']); ?></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Observaciones</th>
                                    <!--
                                        nl2br() convierte los saltos de linea (\n) en etiquetas <br>
                                        asi se respeta el formato multilinea del textarea
                                    -->
                                    <td><?php echo nl2br(htmlspecialchars($_POST['observaciones'])); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <?php else: ?>
                        <!--
                            Si alguien accede a esta pagina directamente (sin enviar el formulario),
                            mostramos un mensaje de aviso con un enlace para volver al formulario
                        -->
                        <div class="alert alert-warning">No se recibieron datos. <a href="index.php">Volver al formulario</a></div>
                        <?php endif; ?>

                    </div>
                    <div class="card-footer text-center">
                        <a href="index.php" class="btn btn-primary">Volver al formulario</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
