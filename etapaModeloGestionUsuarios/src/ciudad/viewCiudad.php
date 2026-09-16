<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos=array_map('intval',$_SESSION['tipos']??[]);
if(count(array_intersect([1,2],$tipos))===0){ header("Location: ../home/index.php?error=".urlencode("Sin permisos Ciudades")); exit; }

require_once '../../lib/bd/conexion.php';
require_once '../../lib/bd/ciudades.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion=obtenerConexion();
$busqueda=isset($_GET['busqueda'])?trim($_GET['busqueda']):'';
$ciudades=obtenerTodasLasCiudades($conexion,$busqueda);
$username=$_SESSION['username']??'';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ciudades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username,$tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Gestión de Ciudades</h4>
                        <span class="badge bg-light text-dark"><?php echo count($ciudades); ?> ciudades</span>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['error']) && $_GET['error']==='1'): ?><div class="alert alert-danger">No se puede eliminar: hay personas en esa ciudad.</div><?php endif; ?>
                        <?php if(isset($_GET['error']) && $_GET['error']!=='1'): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']);?></div><?php endif; ?>
                        <form method="GET" action="viewCiudad.php" class="row g-2 mb-3">
                            <div class="col-md-9"><input type="text" name="busqueda" class="form-control" placeholder="Buscar ciudad..." value="<?php echo htmlspecialchars($busqueda);?>"></div>
                            <div class="col-md-3"><button type="submit" class="btn btn-info w-100">Buscar</button></div>
                        </form>
                        <?php if($busqueda!==''): ?><div class="alert alert-secondary py-2">Resultados para: <strong><?php echo htmlspecialchars($busqueda);?></strong> <a href="viewCiudad.php" class="float-end">Quitar filtro</a></div><?php endif; ?>
                        <?php if(empty($ciudades)): ?><div class="alert alert-info">No hay ciudades.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr><th>#</th><th>Ciudad</th><th>Provincia</th><th>Latitud</th><th>Longitud</th><th>Cód. Postal</th><th>Fundación</th><th>Acciones</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($ciudades as $c): ?>
                                    <tr>
                                        <td><?php echo $c['id'];?></td>
                                        <td><?php echo htmlspecialchars($c['nombre']);?></td>
                                        <td><?php echo htmlspecialchars($c['provincia']);?></td>
                                        <td><?php echo htmlspecialchars($c['latitud']);?></td>
                                        <td><?php echo htmlspecialchars($c['longitud']);?></td>
                                        <td><?php echo htmlspecialchars($c['codigo_postal']);?></td>
                                        <td><?php echo htmlspecialchars($c['fecha_fundacion']);?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="editCiudad.php?id=<?php echo $c['id'];?>" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="gestionCiudad.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar ciudad?');">
                                                    <input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?php echo $c['id'];?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif;?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="editCiudad.php" class="btn btn-primary">Nueva ciudad</a>
                        <a href="../persona/viewPersona.php" class="btn btn-outline-success">Ver personas</a>
                        <a href="../home/index.php" class="btn btn-outline-secondary">Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
