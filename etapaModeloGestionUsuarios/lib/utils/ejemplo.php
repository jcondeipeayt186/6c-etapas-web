<?php
// ejemplo.php - Prueba rápida de la librería de mail (ejecutar: php lib/utils/ejemplo.php)
require_once __DIR__ . '/mail.php';

$cfg = cargarConfigMail();
echo "Config: host={$cfg['host']} port={$cfg['port']} from={$cfg['from']} dry_run=".($cfg['dry_run']?'true':'false').PHP_EOL;

// En dry_run no necesita SMTP real
$dest = $argv[1] ?? 'test@example.com';//argv[1] tendra valor si se ejecuta desde consola. Y se ejecuta de la siguiente forma: php lib/utils/ejemplo.php valor_del_destinatario
$asunto = 'Prueba Envio Mail desde PHP - ' . date('Y-m-d H:i:s');
$html = '<h2>Prueba de mail</h2><p>Esto es un correo de <strong>prueba</strong> desde lib/utils/mail.php.</p><p>Si ves esto en mail.log con DRY_RUN, la librería funciona.</p>';

$ok = enviarMail($dest, $asunto, $html);
echo $ok ? "OK enviado (o simulado en dry_run)\n" : "FAIL: " . obtenerUltimoErrorMail() . "\n";
echo "Log:\n" . obtenerUltimoLogMail() . "\n";

// Ejemplo plantilla recuperación
// enviarMailRecuperacion('alumno@ejemplo.com', 'admin', 'http://localhost/src/auth/restablecer.php?token=xyz');
