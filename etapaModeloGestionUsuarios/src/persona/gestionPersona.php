<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos=array_map('intval',$_SESSION['tipos']??[]);
if(count(array_intersect([1,2],$tipos))===0){ header("Location: ../home/index.php?error=".urlencode("Sin permisos")); exit; }

require_once '../../lib/bd/gestionBaseDatos.php';
$conexion=obtenerConexion();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='eliminar'){ $id=(int)$_POST['id']; eliminarPersona($conexion,$id); header("Location: viewPersona.php"); exit; }
    if($accion==='actualizar'){
        $id=(int)$_POST['id'];
        if(isset($_POST['nombre'])&&isset($_POST['apellido'])&&isset($_POST['dni'])){
            $datos=[
                'nombre'=>trim($_POST['nombre']),'apellido'=>trim($_POST['apellido']),'dni'=>trim($_POST['dni']),'cuit'=>trim($_POST['cuit']),
                'fecha_nacimiento'=>$_POST['fecha_nacimiento'],'email'=>trim($_POST['email']),'telefono'=>trim($_POST['telefono']),
                'direccion'=>trim($_POST['direccion']),'ciudad_id'=>($_POST['ciudad_id']!=='')?(int)$_POST['ciudad_id']:null,'observaciones'=>trim($_POST['observaciones'])
            ];
            actualizarPersona($conexion,$id,$datos);
        }
        header("Location: viewPersona.php"); exit;
    }
    if(isset($_POST['nombre'])&&isset($_POST['apellido'])&&isset($_POST['dni'])){
        $datos=[
            'nombre'=>trim($_POST['nombre']),'apellido'=>trim($_POST['apellido']),'dni'=>trim($_POST['dni']),'cuit'=>trim($_POST['cuit']),
            'fecha_nacimiento'=>$_POST['fecha_nacimiento'],'email'=>trim($_POST['email']),'telefono'=>trim($_POST['telefono']),
            'direccion'=>trim($_POST['direccion']),'ciudad_id'=>($_POST['ciudad_id']!=='')?(int)$_POST['ciudad_id']:null,'observaciones'=>trim($_POST['observaciones'])
        ];
        insertarPersona($conexion,$datos);
        header("Location: viewPersona.php"); exit;
    } else { header("Location: editPersona.php"); exit; }
}
header("Location: ../home/index.php"); exit;
