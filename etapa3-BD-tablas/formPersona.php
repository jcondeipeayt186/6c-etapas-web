<?php
/*
    formPersona.php - Formulario de personas (ETAPA 3)

    DIFERENCIA CON LA ETAPA 2:
    El campo "Ciudad" ya NO es un cuadro de texto donde se escribe cualquier cosa.
    Ahora es un SELECT (lista desplegable) que muestra las ciudades que YA EXISTEN
    en la base de datos (tabla ciudades). El usuario elige donde nacio.

    Esto evita datos repetidos e incoherentes (por ejemplo "cordoba", "Cordoba", "Cba")
    porque solo se puede elegir entre las ciudades cargadas.

    NOTA: el codigo_postal ya NO se carga aqui. Ahora pertenece a la ciudad
    (se gestiona en formCiudad.php), no a la persona.
*/

require_once 'bd/gestionBaseDatos.php';

// Conectamos a la base de datos
$conexion = obtenerConexion();

// Consultamos TODAS las ciudades para llenar el select del formulario
$ciudades = obtenerTodasLasCiudades($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Personas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <img src="img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle">Formulario de Personas</h2>
                    </div>
                    <div class="card-body">

                        <!--
                            AVISO IMPORTANTE:
                            Para que el select de ciudad funcione, primero deben existir ciudades.
                            Si no hay ninguna, se muestra un alerta con un enlace para cargarlas.
                        -->
                        <?php if (empty($ciudades)): ?>
                            <div class="alert alert-warning">
                                No hay ciudades cargadas. Primero debe
                                <a href="ciudades.php" class="alert-link">cargar ciudades</a>.
                            </div>
                        <?php endif; ?>

                        <form action="procesando.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>

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

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!--
                                        SELECT DE CIUDAD (clave foranea):
                                        - name="ciudad_id": se envia el ID de la ciudad elegida
                                        - foreach recorre las ciudades y crea una opcion por cada una
                                        - value="$ciudad['id']" es el ID que se guarda en la BD
                                    -->
                                    <label for="ciudad_id" class="form-label">Lugar de Nacimiento</label>
                                    <select class="form-select" id="ciudad_id" name="ciudad_id" required>
                                        <option value="">-- Seleccionar ciudad --</option>
                                        <?php foreach ($ciudades as $ciudad): ?>
                                            <option value="<?php echo $ciudad['id']; ?>">
                                                <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                                (<?php echo htmlspecialchars($ciudad['provincia']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required>
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="resultado.php" class="btn btn-outline-success">Ver personas registradas</a>
                        <a href="ciudades.php" class="btn btn-outline-secondary">Gestionar ciudades</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
