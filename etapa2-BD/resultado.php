<?php
/*
    RESULTADO.PHP - Pagina que muestra TODAS las personas guardadas en la base de datos

    A diferencia de la etapa 1 (que mostraba solo 1 persona),
    aca se consultan TODOS los registros de la tabla personas y se muestran en una tabla HTML.
*/

// Incluimos el archivo con la conexion y funciones de la BD
require_once 'bd/gestionBaseDatos.php';
include 'librerias/funcionesHTML.php';

// Obtenemos la conexion a MySQL
$conexion = obtenerConexion();

// Consultamos TODAS las personas guardadas en la base de datos
// La funcion nos devuelve un array con cada persona como un array asociativo
$personas = obtenerTodasLasPersonas($conexion);
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
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Personas Registradas</h2>
                        <!-- count() cuenta cuantos elementos hay en el array $personas -->
                        <span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
                    </div>
                    <div class="card-body">
                        <!-- https://www.php.net/manual/es/control-structures.alternative-syntax.php
                        Sintaxis alternativa de control de flujo (if, foreach, etc.) para usar en HTML
                        En lugar de usar llaves {}, usamos : y endif; o endforeach;
                        
                        En PHP puedes usar la sintaxis alternativa para las estructuras de control, 
                        la cual utiliza dos puntos (:) en lugar de la llave de apertura ({) y termina
                        con endif; en lugar de la llave de cierre (}).Esta sintaxis es muy común y 
                        recomendada cuando mezclas código PHP con código HTML, ya que mejora la legibilidad.
                        -->     
                        <?php if (empty($personas)): ?>
                            <!-- Si no hay personas en la BD, mostramos un mensaje informativo -->
                            <div class="alert alert-info">No hay personas registradas aun.</div>
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

    <?php
        piePagina();
    ?>

</body>
</html>
