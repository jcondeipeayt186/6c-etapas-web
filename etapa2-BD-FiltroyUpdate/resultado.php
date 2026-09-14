<?php
/*
    RESULTADO.PHP - Pagina que muestra TODAS las personas guardadas en la base de datos

    A diferencia de la etapa 1 (que mostraba solo 1 persona),
    aca se consultan TODOS los registros de la tabla personas y se muestran en una tabla HTML.
*/

// Incluimos el archivo con la conexion y funciones de la BD
require_once 'bd/gestionBaseDatos.php';

// Obtenemos la conexion a MySQL
$conexion = obtenerConexion();

        // Consultamos TODAS las personas guardadas en la base de datos
        // La funcion nos devuelve un array con cada persona como un array asociativo
        //$personas = obtenerTodasLasPersonas($conexion);

// Recibimos el termino de busqueda desde la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
//https://www.w3schools.com/php/phptryit.asp?filename=tryphp_oper_ternary

// La funcion recibe el termino: si esta vacio, trae todas las personas;
// si no, filtra por nombre usando LIKE
$personas = obtenerPersonasConFiltro($conexion, $busqueda);

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
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Personas Registradas</h2>
                        <!-- count() cuenta cuantos elementos hay en el array $personas -->
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">


                    <!--
                            BUSCADOR:
                            - method="GET": el termino viaja en la URL (?busqueda=...)
                            - action="resultado.php": recarga esta misma pagina con el filtro
                            - value=... : deja el termino escrito despues de buscar
                        -->
                        <form method="GET" action="resultado.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control"
                                       placeholder="Buscar por nombre de persona..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">Buscar</button>
                            </div>
                        </form>


                        <?php if (empty($personas)): ?>
                      
                            <!-- Si no hay personas en la BD, mostramos un mensaje informativo -->
                            <!-- <div class="alert alert-info">No hay personas registradas aun.</div> -->
                             <div class="alert alert-info">
                                <?php echo ($busqueda !== '') ? 'No se encontraron personas con ese nombre.' : 'No hay personas registradas aun.'; ?>
                            </div>
                                           
                      
                      
                            <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <!-- table-striped: filas con colores alternados -->
                                <!-- table-hover: resalta la fila cuando pasas el mouse -->
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>DNI</th>
                                        <th>CUIT</th>
                                        <th>Nacimiento</th>
                                        <th>Email</th>
                                        <th>Telefono</th>
                                        <th>Ciudad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // foreach recorre cada elemento del array $personas
                                    // En cada vuelta, $persona contiene una persona (array asociativo)
                                    // Podemos acceder a sus datos con $persona['nombre'], $persona['apellido'], etc.
                                    foreach ($personas as $persona):
                                    ?>
                                    <tr>
                                        <td><?php echo $persona['id']; ?></td>
                                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['dni']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['cuit']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['fecha_nacimiento']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['email']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['ciudad']); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <!--
                                                    Boton MODIFICAR:
                                                    - Es un simple link <a> con ?id=
                                                    - Al hacer click, lleva a index.php?id=X
                                                    - index.php detecta el id, busca la persona y precarga el formulario
                                                    - Usa GET porque es una consulta (no modifica nada aun)
                                                -->
                                                <a href="index.php?id=<?php echo $persona['id']; ?>" class="btn btn-warning btn-sm">Modificar</a>

                                                <!--
                                                    Formulario de ELIMINAR:
                                                    - Usa un campo oculto (hidden) para enviar la accion "eliminar"
                                                    - El campo hidden "id" envia el ID de la persona a eliminar
                                                    - onsubmit: pregunta "Seguro?" antes de enviar (confirm de JavaScript)
                                                    - Al enviar, procesando.php detecta que accion=eliminar y borra el registro
                                                -->
                                                <form action="procesando.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Seguro que deseas eliminar esta persona?');">
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
                        <a href="index.php" class="btn btn-primary">Volver al formulario</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
