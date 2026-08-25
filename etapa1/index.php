<!DOCTYPE html>
<!-- Esta pagina es el FORMULARIO donde el usuario carga los datos de una persona -->
<!-- Usa Bootstrap 5 para el diseño responsivo (se adapta a celular, tablet, PC) -->
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Contacto</title>
    <!-- Bootstrap CSS: libreria de estilos que nos da botones, tarjetas, grillas, etc. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- container: centrador de Bootstrap que agrega margenes a los costados -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <!-- card: componente Bootstrap que crea una tarjeta con sombra -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>Formulario de Contacto</h2>
                    </div>
                    <div class="card-body">

                        <!--
                            FORMULARIO HTML:
                            - action="resultado.php"  => a donde se envian los datos al presionar "Enviar"
                            - method="POST"           => los datos van en el cuerpo del request (no en la URL)
                        -->
                        <form action="resultado.php" method="POST">

                            <!-- Fila 1: Nombre y Apellido -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <!-- name="nombre" es la clave con la que PHP recibe el valor en $_POST['nombre'] -->
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>

                            <!-- Fila 2: DNI y CUIT -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cuit" class="form-label">CUIT</label>
                                    <input type="text" class="form-control" id="cuit" name="cuit" required>
                                </div>
                            </div>

                            <!-- Fila 3: Fecha de nacimiento y Email -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <!-- type="date" muestra un selector de fecha nativo del navegador -->
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <!-- type="email" valida automaticamente que sea un email valido -->
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <!-- Fila 4: Telefono y Ciudad -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                                </div>
                            </div>

                            <!-- Direccion (ocupa todo el ancho) -->
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required>
                            </div>

                            <!-- Fila 5: Provincia y Codigo Postal -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="codigo_postal" class="form-label">Codigo Postal</label>
                                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required>
                                </div>
                            </div>

                            <!-- Observaciones: textarea permite multiples lineas -->
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>

                            <!-- Boton de envio: type="submit" envia el formulario -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS: necesario para componentes como dropdowns, modals, etc. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
