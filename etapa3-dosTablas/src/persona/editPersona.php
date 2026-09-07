<?php
/*
    editPersona.php - Formulario de alta y edición de personas (ETAPA 3 dosTablas)

    Este formulario sirve para DOS cosas (mismo archivo, dos modos):
    1. CREAR una persona nueva  -> cuando se accede sin ?id=  (ej: editPersona.php)
    2. EDITAR una persona        -> cuando se accede con ?id=   (ej: editPersona.php?id=5)

    Diferencias con la Etapa 2-FiltroyUpdate:
    - El campo "Ciudad" ya NO es un <input type="text"> donde se escribe cualquier cosa
    - Ahora es un <select> (lista desplegable) que muestra las ciudades que YA EXISTEN
      en la tabla ciudades. El usuario elige el lugar de nacimiento.
    - Esto evita datos repetidos ("Córdoba" vs "cordoba" vs "Cba") porque solo se puede
      elegir entre las ciudades cargadas. Es la normalización a clave foránea.
    - codigo_postal y provincia ya NO se cargan acá: pertenecen a la ciudad (ver editCiudad.php)

    En el caso de edición, se busca la persona en la BD y se precargan sus datos (value="...").
    El botón cambia de "Crear persona" a "Guardar cambios" y se envía un hidden accion=actualizar.
*/

// Incluimos la librería de base de datos (obtenerConexion, obtenerPersonaPorId, obtenerTodasLasCiudades)
require_once '../../lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Consultamos TODAS las ciudades para llenar el <select> del formulario
// El usuario debe elegir entre estas ciudades (clave foránea ciudad_id)
$ciudades = obtenerTodasLasCiudades($conexion);

// Variables para el modo edición
$persona = null;    // Contendrá la persona a editar o null si es alta
$esEdicion = false; // true si ?id= viene en la URL y la persona existe

// Si la URL trae ?id=, estamos editando una persona existente
if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // (int) por seguridad: "hola" se convierte en 0
    $persona = obtenerPersonaPorId($conexion, $id); // SELECT * WHERE id = ?
    if ($persona) {
        $esEdicion = true; // Solo si se encontró la persona
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Persona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <!-- Cabecera azul: título dinámico según el modo -->
                    <div class="card-header bg-primary text-white text-center">
                        <img src="../../img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle"><?php echo $esEdicion ? 'Modificar Persona' : 'Nueva Persona'; ?></h2>
                    </div>
                    <div class="card-body">

                        <!-- Aviso si no hay ciudades cargadas: no se puede crear personas -->
                        <?php if (empty($ciudades)): ?>
                            <div class="alert alert-warning">
                                No hay ciudades cargadas. Primero debe
                                <a href="../ciudad/editCiudad.php" class="alert-link">cargar una ciudad</a>
                                para poder asignar el lugar de nacimiento.
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje de error que puede venir desde gestionPersona.php (ej: validación) -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Caso: se pidió editar una persona que no existe -->
                        <?php if (isset($_GET['id']) && !$esEdicion): ?>
                            <div class="alert alert-danger">La persona no existe o el ID es inválido.</div>
                            <a href="viewPersona.php" class="btn btn-primary w-100">Volver al listado</a>
                        <?php else: ?>

                        <!--
                            El formulario siempre envía a gestionPersona.php
                            - Si es alta: no lleva id, gestionPersona hará INSERT
                            - Si es edición: lleva id oculto + accion=actualizar, gestionPersona hará UPDATE
                        -->
                        <form action="gestionPersona.php" method="POST">
                            <?php if ($esEdicion): ?>
                                <!-- Campos ocultos para el UPDATE -->
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                            <?php else: ?>
                                <!-- En modo alta, indicamos explícitamente accion=crear (gestionPersona lo espera) -->
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <!-- value precargado si es edición, vacío si es alta. htmlspecialchars evita romper el HTML -->
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
                                    <!-- type=date espera formato YYYY-MM-DD como lo guarda MySQL -->
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
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['telefono']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!--
                                        SELECT DE CIUDAD (clave foránea):
                                        - name="ciudad_id": se envía el ID de la ciudad elegida (no el nombre)
                                        - foreach recorre las ciudades y crea una <option> por cada una
                                        - value="$ciudad['id']" es el ID que se guarda en personas.ciudad_id
                                        - selected si es la ciudad de la persona que estamos editando
                                    -->
                                    <label for="ciudad_id" class="form-label">Lugar de Nacimiento</label>
                                    <select class="form-select" id="ciudad_id" name="ciudad_id" required>
                                        <option value="">-- Seleccionar ciudad --</option>
                                        <?php foreach ($ciudades as $ciudad): ?>
                                            <option value="<?php echo $ciudad['id']; ?>"
                                                <?php echo ($esEdicion && $persona['ciudad_id'] == $ciudad['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                                (<?php echo htmlspecialchars($ciudad['provincia']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required
                                       value="<?php echo $esEdicion ? htmlspecialchars($persona['direccion']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <!-- El textarea no usa value, el contenido va entre las etiquetas -->
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo $esEdicion ? htmlspecialchars($persona['observaciones']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <!-- El texto y color del botón cambian según el modo -->
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear persona'; ?>
                                </button>
                            </div>

                            <?php if ($esEdicion): ?>
                            <!-- Botón para cancelar la edición y volver al alta -->
                            <div class="d-grid mt-2">
                                <a href="editPersona.php" class="btn btn-outline-secondary">Cancelar edición</a>
                            </div>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewPersona.php" class="btn btn-outline-success">Ver personas registradas</a>
                        <a href="../../index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
