<?php
/*
    INDEX.PHP - Formulario de alta y edicion (ETAPA 2 con Filtro + Update)

    Este formulario sirve para DOS cosas:
    1. CREAR una persona nueva  -> cuando se accede sin ?id= (ej: index.php)
    2. EDITAR una persona        -> cuando se accede con ?id= (ej: index.php?id=5)

    En el caso de edicion, se busca la persona en la base de datos y
    se precargan sus datos en los campos del formulario.
    El boton cambia de "Enviar" a "Modificar" y se envia un campo oculto
    con la accion "actualizar" y el id de la persona.
*/

require_once 'bd/gestionBaseDatos.php';

// Conectamos a la base de datos
$conexion = obtenerConexion();

// Variables para el modo edicion
$persona = null;
$esEdicion = false;

// Si la URL trae ?id=, estamos editando una persona existente
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $persona = obtenerPersonaPorId($conexion, $id);
    // Si se encontro la persona, activamos el modo edicion
    if ($persona) {
        $esEdicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Modificar Persona' : 'Formulario de Contacto'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <!-- Icono de MySQL: indica que esta aplicacion usa base de datos MySQL -->
                        <img src="img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle"><?php echo $esEdicion ? 'Modificar Persona' : 'Formulario de Contacto'; ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="d-grid">
                                        <a href="resultado.php" class="btn btn-secondary">Ver Personas</a>
                                 </div>
                            </div>
                        </div>

                        <?php if (isset($_GET['id']) && !$esEdicion): ?>
                            <!-- Si se pidio editar una persona que no existe -->
                            <div class="alert alert-danger">La persona no existe o el ID es invalido.</div>
                            <a href="resultado.php" class="btn btn-primary w-100 mb-3">Volver al listado</a>
                        <?php else: ?>

                        <!--
                            DIFERENCIA CON LA ETAPA 1:
                            El action apunta a "procesando.php" en vez de "resultado.php"
                            porque primero hay que GUARDAR los datos en la BD,
                            y despues redirigir a resultado.php para mostrarlos
                        -->
                        <form action="procesando.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <!-- Campos ocultos para el UPDATE: accion + id de la persona -->
                                <!-- Sin estos campos, procesando.php no sabria que es una modificacion -->
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['nombre']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['apellido']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['dni']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cuit" class="form-label">CUIT</label>
                                    <input type="text" class="form-control" id="cuit" name="cuit" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['cuit']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['fecha_nacimiento']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['telefono']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['ciudad']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required
                                       value="<?php echo $esEdicion ? htmlspecialchars($persona['direccion']) : ''; ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provincia" class="form-label">Provincia</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['provincia']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="codigo_postal" class="form-label">Codigo Postal</label>
                                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['codigo_postal']) : ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo $esEdicion ? htmlspecialchars($persona['observaciones']) : ''; ?></textarea>
                            </div>
                            <div class="d-grid">
                                <!-- El texto del boton cambia segun el modo -->
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Modificar' : 'Enviar'; ?>
                                </button>
                            </div>
                            <?php if ($esEdicion): ?>
                            <div class="d-grid mt-2">
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar edicion</a>
                            </div>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
