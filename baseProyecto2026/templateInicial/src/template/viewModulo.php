<?php
/*
    viewModulo.php - PLANTILLA del listado de un módulo nuevo

    Este archivo NO funciona tal cual: es una PLANTILLA.
    Sirve para copiarla cuando se agrega un módulo (por ejemplo "paises")
    y tener la estructura lista, sin tener que escribir todo desde cero.

    CÓMO USARLA:
    1. Copiá esta carpeta:  src/template/  ->  src/paises/
    2. Renombrá los archivos:
          viewModulo.php      -> viewPais.php
          editModulo.php      -> editPais.php
          gestionModulo.php   -> gestionPais.php
    3. Cambiá las palabras "MODULO" / "modulo" por el nombre de tu módulo.
    4. Creá la tabla en la base y sus funciones en lib/bd/modulos-bd.php

    QUÉ HACE ESTA PÁGINA (es la VISTA del módulo):
    - Muestra todos los registros en una tabla
    - Tiene un buscador por nombre (GET)
    - Cada fila tiene botones: Modificar / Eliminar
*/

// 1. Incluimos las librerías que vamos a necesitar
require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/html/funcionesHTML.php';
require_once '../../lib/utils/varios.php';

// 1.b Aviso: si la función del módulo todavía no existe, mostramos el mensaje
// de "plantilla sin terminar" en vez de un error 500 de PHP.
if (!function_exists('obtenerTodosLosModulos')) {
    require_once 'avisoPlantilla.php';
    mostrarAvisoPlantilla('obtenerTodosLosModulos');
}

// 2. Conectamos con la base de datos
$conexion = obtenerConexion();

// 3. Leemos el término de búsqueda de la URL (?busqueda=...)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// 4. Traemos los datos
// (Reemplazar obtenerTodosLosModulos por el nombre de tu función)
$modulos = obtenerTodosLosModulos($conexion, $busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de MODULOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">MODULOS</h2>
                        <span class="badge bg-light text-dark"><?php echo count($modulos); ?> registros</span>
                    </div>
                    <div class="card-body">

                        <!-- BUSCADOR: viaja por GET porque es una consulta -->
                        <form method="GET" action="viewModulo.php" class="row g-2 mb-3">
                            <div class="col-md-9">
                                <input type="text" name="busqueda" class="form-control"
                                       placeholder="Buscar..."
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Buscar</button>
                            </div>
                        </form>

                        <?php if ($busqueda !== ''): ?>
                            <div class="alert alert-secondary">
                                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                                <a href="viewModulo.php" class="float-end">Quitar filtro</a>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($modulos)): ?>
                            <div class="alert alert-info">No hay registros para mostrar.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modulos as $modulo): ?>
                                    <tr>
                                        <td><?php echo $modulo['id']; ?></td>
                                        <td><?php echo htmlspecialchars($modulo['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($modulo['descripcion']); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <!-- MODIFICAR: lleva el id por la URL -->
                                                <a href="editModulo.php?id=<?php echo $modulo['id']; ?>"
                                                   class="btn btn-warning btn-sm">Modificar</a>

                                                <!-- ELIMINAR: POST con accion=eliminar -->
                                                <form action="gestionModulo.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar?');">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $modulo['id']; ?>">
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
                        <a href="editModulo.php" class="btn btn-primary">Nuevo registro</a>
                        <a href="../index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
