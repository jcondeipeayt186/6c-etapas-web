<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos=array_map('intval',$_SESSION['tipos']??[]);
if(count(array_intersect([1,2],$tipos))===0){ header("Location: ../home/index.php?error=".urlencode("Sin permisos")); exit; }

require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/html/funcionesHTML.php';
$conexion=obtenerConexion();
$ciudad=null; $esEdicion=false;
if(isset($_GET['id'])){ 
    $id=(int)$_GET['id'];
    $ciudad=obtenerCiudadPorId($conexion,$id);
    if($ciudad) $esEdicion=true; 
}
$username=$_SESSION['username']??'';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion?'Editar':'Nueva';?> Ciudad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username,$tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white text-center"><h4 class="mb-0"><?php echo $esEdicion?'Editar Ciudad':'Nueva Ciudad';?></h4></div>
                    <div class="card-body">
                        <?php if(isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']);?></div><?php endif; ?>
                        <?php if(isset($_GET['id']) && !$ciudad): ?><div class="alert alert-danger">Ciudad no encontrada.</div><a href="viewCiudad.php" class="btn btn-primary">Volver</a>
                        <?php else: ?>
                        <form action="gestionCiudad.php" method="POST">
                            <?php if($esEdicion): ?><input type="hidden" name="accion" value="actualizar"><input type="hidden" name="id" value="<?php echo $ciudad['id'];?>"><?php else: ?><input type="hidden" name="accion" value="crear"><?php endif; ?>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" required value="<?php echo $esEdicion?htmlspecialchars($ciudad['nombre']):'';?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Provincia</label><input type="text" class="form-control" name="provincia" required value="<?php echo $esEdicion?htmlspecialchars($ciudad['provincia']):'';?>"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Latitud</label><input type="number" step="any" class="form-control" name="latitud" value="<?php echo $esEdicion?htmlspecialchars($ciudad['latitud']):'';?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Longitud</label><input type="number" step="any" class="form-control" name="longitud" value="<?php echo $esEdicion?htmlspecialchars($ciudad['longitud']):'';?>"></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Código Postal</label><input type="text" class="form-control" name="codigo_postal" value="<?php echo $esEdicion?htmlspecialchars($ciudad['codigo_postal']):'';?>"></div>
                            <div class="mb-3"><label class="form-label">Fecha Fundación</label><input type="date" class="form-control" name="fecha_fundacion" value="<?php echo $esEdicion?htmlspecialchars($ciudad['fecha_fundacion']):'';?>"></div>
                            <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="3"><?php echo $esEdicion?htmlspecialchars($ciudad['descripcion']):'';?></textarea></div>
                            <div class="d-grid"><button type="submit" class="btn <?php echo $esEdicion?'btn-warning':'btn-primary';?>"><?php echo $esEdicion?'Guardar cambios':'Crear ciudad';?></button></div>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center"><a href="viewCiudad.php" class="btn btn-outline-info">Volver a ciudades</a> <a href="../home/index.php" class="btn btn-outline-secondary">Inicio</a></div>
                </div>
            </div>
        </div>
    </div>
    <?php piePagina(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
