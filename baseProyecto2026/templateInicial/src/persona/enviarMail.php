<?php
/*
    enviarMail.php - Envía un mail a una persona de la tabla personas

    ¿Navegable? NO. No tiene HTML: recibe los datos del modal de
    viewPersona.php, envía el mail y vuelve al listado.

    Lo que hace:
    1. Recibe por POST el id de la persona y los datos del mail
    2. Busca la persona en la base (para tener su nombre, email, etc.)
    3. Llama a enviarMailAPersona() de lib/utils/mail.php
    4. Redirige a viewPersona.php con un cartel de éxito o de error

    ¿Por qué dos redirecciones y no mostrar el resultado acá?
    Para que el usuario no pueda recargar la página y mande el mail
    varias veces (eso se llama el "problema del reenvío" de los formularios).
*/

// Librería de la base de datos (persona) y del envío de mails
require_once '../../lib/bd/gestionBaseDatos.php';
require_once '../../lib/utils/mail.php';

// Solo hacemos algo si los datos llegaron por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: viewPersona.php");
    exit;
}

// Datos que vienen del modal
$id       = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$para     = isset($_POST['para']) ? trim($_POST['para']) : '';
$asunto   = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
$mensaje  = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

// Comprobaciones básicas antes de seguir
if ($id === 0 || $para === '' || $asunto === '' || $mensaje === '') {
    header("Location: viewPersona.php?mail=error&detalle=" . urlencode("Faltan datos para enviar el mail."));
    exit;
}

// Buscamos la persona en la base
$conexion = obtenerConexion();
$persona = obtenerPersonaPorId($conexion, $id);

if (!$persona) {
    header("Location: viewPersona.php?mail=error&detalle=" . urlencode("La persona no existe."));
    exit;
}

// Enviamos el mail
$enviado = enviarMailAPersona($persona, $para, $asunto, $mensaje);

// El error del log es aparte: el mail puede haberse enviado bien y
// solamente no haberse podido anotar (por permisos, por ejemplo)
$errorDelLog = obtenerUltimoErrorLog();
$detalle     = $errorDelLog;

if ($enviado) {
    header("Location: viewPersona.php?mail=ok&log=" . urlencode($errorDelLog));
} else {
    if ($errorDelLog !== '') {
        $detalle = obtenerUltimoErrorMail() . ' | ' . $errorDelLog;
    } else {
        $detalle = obtenerUltimoErrorMail();
    }
    header("Location: viewPersona.php?mail=error&detalle=" . urlencode($detalle));
}
exit;
