<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../../index.php?error=login_requerido"); exit; }
$tipos=array_map('intval',$_SESSION['tipos']??[]);
if(count(array_intersect([1,2],$tipos))===0){ header("Location: ../home/index.php?error=".urlencode("Sin permisos")); exit; }

require_once '../../lib/bd/gestionBaseDatos.php';
$conexion=obtenerConexion();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='eliminar'){
        $id=(int)$_POST['id'];
        if(hayPersonasEnCiudad($conexion,$id)){ header("Location: viewCiudad.php?error=1"); exit; }
        eliminarCiudad($conexion,$id);
        header("Location: viewCiudad.php"); exit;
    }
    $latitud=(isset($_POST['latitud'])&&$_POST['latitud']!=='')?$_POST['latitud']:null;
    $longitud=(isset($_POST['longitud'])&&$_POST['longitud']!=='')?$_POST['longitud']:null;
    $error=null;
    if($latitud!==null && (!is_numeric($latitud)||$latitud<-90||$latitud>90)) $error='Latitud debe estar entre -90 y 90';
    elseif($longitud!==null && (!is_numeric($longitud)||$longitud<-180||$longitud>180)) $error='Longitud debe estar entre -180 y 180';
    if($error){
        if($accion==='actualizar' && isset($_POST['id'])) header("Location: editCiudad.php?id=".(int)$_POST['id']."&error=".urlencode($error));
        else header("Location: editCiudad.php?error=".urlencode($error));
        exit;
    }
    $datos=[
        'nombre'=>trim($_POST['nombre']),'provincia'=>trim($_POST['provincia']),'latitud'=>$latitud,'longitud'=>$longitud,
        'codigo_postal'=>trim($_POST['codigo_postal']),'descripcion'=>trim($_POST['descripcion']),
        'fecha_fundacion'=>(isset($_POST['fecha_fundacion'])&&$_POST['fecha_fundacion']!=='')?$_POST['fecha_fundacion']:null
    ];
    if($accion==='actualizar'){ $id=(int)$_POST['id']; actualizarCiudad($conexion,$id,$datos); }
    else { insertarCiudad($conexion,$datos); }
    header("Location: viewCiudad.php"); exit;
}
header("Location: ../home/index.php"); exit;
