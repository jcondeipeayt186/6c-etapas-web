<?php
/*
    viewPersona.php - Tabla con todas las personas (ETAPA 3 dosTablas)

    Esta página es NAVEGABLE (el usuario la ve en el navegador).
    Hace tres cosas:
    1. Consulta todas las personas con JOIN a ciudades (para mostrar ciudad_nombre y provincia)
    2. Permite FILTRAR por nombre con un buscador (heredado de Etapa 2-Filtro)
    3. Permite EDITAR (botón Modificar → editPersona.php?id=X) y ELIMINAR cada persona

    El término de búsqueda llega por GET (?busqueda=...), porque un buscador
    "no guarda datos" sino que pide una consulta, por eso usamos GET y no POST.
    - GET deja el término en la URL: se puede compartir el link, recargar la página, etc.
    - POST se usa para INSERT/UPDATE/DELETE (gestionPersona.php)
*/

// Incluimos la librería de base de datos (obtenerConexion, obtenerTodasLasPersonas)
require_once '../../lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Recibimos el término de búsqueda desde la URL (si existe)
// isset() evita "undefined index" la primera vez que se entra sin buscar
// trim() saca espacios extra: "  Jul " → "Jul"
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// La función recibe el término: si está vacío, trae todas las personas;
// si no, filtra por nombre usando LIKE '%termino%'
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
                    <!-- Cabecera verde con contador de registros -->
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Personas Registradas</h2>
                        <!-- count() cuenta cuántos elementos hay en el array $personas -->
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">

                        <!--
                            BUSCADOR (heredado de etapa2-BD-Filtro):
                            - method="GET": el término viaja en la URL (?busqueda=...)
                            - action="viewPersona.php": recarga esta misma página con el filtro
                            - value="...": deja el término escrito después de buscar (htmlspecialchars para seguridad)
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

                        <!-- Si hay un filtro activo, mostramos un cartel con opción de quitarlo -->
                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="viewPersona.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje cuando no hay personas (dos casos: tabla vacía vs sin coincidencias) -->
                        <?php if (empty($personas)): ?>
                            <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron personas con ese nombre.' : 'No hay personas registradas aún. Use "Nueva persona".'; ?>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <!-- table-striped: filas con colores alternados | table-hover: resalta al pasar el mouse -->
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>DNI</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Lugar de Nacimiento</th>
                                        <th>Provincia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // foreach recorre cada elemento del array $personas
                                    // En cada vuelta, $persona contiene una persona (array asociativo)
                                    foreach ($personas as $persona):
                                    ?>
                                    <tr>
                                        <td><?php echo $persona['id']; ?></td>
                                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['email']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['telefono']); ?></td>
                                        <!--
                                            CIUDAD VÍA JOIN:
                                            $persona['ciudad_nombre'] viene de la tabla ciudades
                                            gracias al LEFT JOIN en gestionBaseDatos.php.
                                            Si la persona no tiene ciudad, mostramos un guión.
                                        -->
                                        <td><?php echo $persona['ciudad_nombre'] ? htmlspecialchars($persona['ciudad_nombre']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $persona['ciudad_provincia'] ? htmlspecialchars($persona['ciudad_provincia']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td>
                                            <!-- Dos botones uno al lado del otro (flex + gap) -->
                                            <div class="d-flex gap-1">
                                                <!--
                                                    Botón MODIFICAR:
                                                    - Link con ?id= : lleva a editPersona.php?id=X
                                                    - editPersona.php detecta el id, busca la persona y precarga el formulario
                                                    - Usa GET porque solo consulta datos (no modifica la BD todavía)
                                                -->
                                                <a href="editPersona.php?id=<?php echo $persona['id']; ?>" class="btn btn-warning btn-sm">Modificar</a>

                                                <!--
                                                    Botón ELIMINAR:
                                                    - Usa un campo oculto (hidden) para enviar accion=eliminar + id
                                                    - onsubmit: confirm() de JavaScript pregunta "¿Seguro?" antes de enviar
                                                    - Al enviar, gestionPersona.php detecta accion=eliminar y hace DELETE
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
