<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos = array_map('intval', $_SESSION['tipos'] ?? []);
if (count(array_intersect([1,2], $tipos))===0) { header("Location: ../home/index.php?error=" . urlencode("Sin permisos")); exit; }

require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/html/funcionesHTML.php';

$conexion = obtenerConexion();
$ciudades = obtenerTodasLasCiudades($conexion);
$persona = null; $esEdicion=false;
if (isset($_GET['id'])) {
    $id=(int)$_GET['id'];
    $persona=obtenerPersonaPorId($conexion,$id);
    if($persona) $esEdicion=true;
}
$username=$_SESSION['username']??'';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion?'Editar':'Nueva';?> Persona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php navbar($username,$tipos); ?>
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0"><?php echo $esEdicion?'Modificar Persona':'Nueva Persona';?></h4>
                    </div>
                    <div class="card-body">
                        <?php if(empty($ciudades)): ?><div class="alert alert-warning">No hay ciudades. <a href="../ciudad/editCiudad.php" class="alert-link">Crear ciudad</a> primero.</div><?php endif; ?>
                        <?php if(isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']);?></div><?php endif; ?>
                        <?php if(isset($_GET['id']) && !$esEdicion): ?>
                            <div class="alert alert-danger">Persona no encontrada.</div>
                            <a href="viewPersona.php" class="btn btn-primary w-100">Volver</a>
                        <?php else: ?>
                        <form action="gestionPersona.php" method="POST">
                            <?php if($esEdicion): ?>
                                <input type="hidden" name="accion" value="actualizar"><input type="hidden" name="id" value="<?php echo $persona['id'];?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" required value="<?php echo $esEdicion?htmlspecialchars($persona['nombre']):'';?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Apellido</label><input type="text" class="form-control" name="apellido" required value="<?php echo $esEdicion?htmlspecialchars($persona['apellido']):'';?>"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">DNI</label><input type="text" class="form-control" name="dni" required value="<?php echo $esEdicion?htmlspecialchars($persona['dni']):'';?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">CUIT</label><input type="text" class="form-control" name="cuit" required value="<?php echo $esEdicion?htmlspecialchars($persona['cuit']):'';?>"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Fecha Nac.</label><input type="date" class="form-control" name="fecha_nacimiento" required value="<?php echo $esEdicion?htmlspecialchars($persona['fecha_nacimiento']):'';?>"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required value="<?php echo $esEdicion?htmlspecialchars($persona['email']):'';?>"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Teléfono</label><input type="tel" class="form-control" name="telefono" required value="<?php echo $esEdicion?htmlspecialchars($persona['telefono']):'';?>"></div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lugar de Nacimiento</label>
                                    <select class="form-select" name="ciudad_id" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach($ciudades as $c): ?>
                                            <option value="<?php echo $c['id'];?>" <?php echo ($esEdicion && $persona['ciudad_id']==$c['id'])?'selected':'';?>><?php echo htmlspecialchars($c['nombre']);?> (<?php echo htmlspecialchars($c['provincia']);?>)</option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3"><label class="form-label">Dirección</label><input type="text" class="form-control" name="direccion" required value="<?php echo $esEdicion?htmlspecialchars($persona['direccion']):'';?>"></div>
                            <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" rows="3"><?php echo $esEdicion?htmlspecialchars($persona['observaciones']):'';?></textarea></div>
                            <div class="d-grid"><button type="submit" class="btn <?php echo $esEdicion?'btn-warning':'btn-primary';?>"><?php echo $esEdicion?'Guardar cambios':'Crear persona';?></button></div>
                            <?php if($esEdicion): ?><div class="d-grid mt-2"><a href="editPersona.php" class="btn btn-outline-secondary">Cancelar edición</a></div><?php endif;?>
                        </form>
                        <?php endif;?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewPersona.php" class="btn btn-outline-success">Ver personas</a>
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
