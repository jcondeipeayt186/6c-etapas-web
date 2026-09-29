<?php
/*
    lib/utils/mail.php - Envío de correos

    Librería simple para enviar mails sin instalar nada.
    Usa la función mail() de PHP y los datos del archivo .env:

        MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD,
        MAIL_FROM, MAIL_FROM_NAME, MAIL_DEBUG, MAIL_DRY_RUN

    ¿Qué es MAIL_DRY_RUN?
    - true  -> NO manda el mail. Solo escribe lo que se habría enviado
               en lib/utils/mail.log. Ideal para probar sin configurar correo.
    - false -> intenta mandar el mail de verdad.

    Uso:
        require_once '../../lib/utils/mail.php';
        $ok = enviarMail('destino@ejemplo.com', 'Asunto', '<p>Hola, mensaje</p>');
        if (!$ok) {
            echo "Error: " . obtenerUltimoErrorMail();
        }
*/

// Para leer el .env necesitamos la función cargarEnv() que está en varios.php
require_once __DIR__ . '/varios.php';

// Guarda el último error para poder mostrarlo en la página
$ultimoErrorMail = '';

/**
 * Devuelve el mensaje del último error de envío.
 *
 * @return string Texto con el error (vacío si no hubo error)
 */
function obtenerUltimoErrorMail() {
    global $ultimoErrorMail;
    return $ultimoErrorMail;
}

/**
 * Escribe una línea en el archivo lib/utils/mail.log.
 * Sirve para ver qué pasó con los mails (y de paso no expose contraseñas).
 *
 * @param string $mensaje Texto a escribir en el log
 * @return void
 */
function escribirLogMail($mensaje) {
    $linea = date('Y-m-d H:i:s') . ' - ' . $mensaje . PHP_EOL;
    file_put_contents(__DIR__ . '/mail.log', $linea, FILE_APPEND);
}

/**
 * Envía un correo electrónico.
 *
 * @param string $para        Email de destino (ej: juan@mail.com)
 * @param string $asunto      Asunto del mail
 * @param string $mensajeHtml Contenido del mail en HTML
 * @param string $textoPlano  Contenido en texto plano (opcional)
 * @return bool true si se envió (o se simuló con DRY_RUN)
 */
function enviarMail($para, $asunto, $mensajeHtml, $textoPlano = '') {
    global $ultimoErrorMail;
    $ultimoErrorMail = '';

    // Leemos la configuración del archivo .env
    cargarEnv(__DIR__ . '/../../.env');

    $remitente     = valorEntorno('MAIL_FROM', 'noreply@localhost');
    $nombreRemitente = valorEntorno('MAIL_FROM_NAME', 'Gestion App');
    $debug         = valorEntorno('MAIL_DEBUG', 'false') === 'true';
    $dryRun        = valorEntorno('MAIL_DRY_RUN', 'true') === 'true';

    // Si el texto plano viene vacío, lo sacamos del HTML
    if ($textoPlano === '') {
        $textoPlano = trim(strip_tags($mensajeHtml));
    }

    // 1. Modo prueba: no se manda nada, solo se anota en el log
    if ($dryRun) {
        escribirLogMail("[SIMULADO] Para: $para | Asunto: $asunto | Desde: $remitente");
        escribirLogMail("[SIMULADO] Mensaje: $textoPlano");
        return true;
    }

    // 2. Armamos los encabezados del mail
    $encabezados = "From: $nombreRemitente <$remitente>\r\n";
    $encabezados .= "MIME-Version: 1.0\r\n";
    $encabezados .= "Content-Type: text/html; charset=UTF-8\r\n";
    $encabezados .= "Reply-To: $remitente\r\n";

    // Si el texto tiene saltos de línea, mail() corta el asunto (línea de encabezado)
    $asunto = str_replace(["\r", "\n"], ' ', $asunto);

    // 3. Enviamos con la función mail() de PHP
    $enviado = @mail($para, $asunto, $mensajeHtml, $encabezados);

    if ($enviado) {
        escribirLogMail("[ENVIADO] Para: $para | Asunto: $asunto");
        if ($debug) {
            escribirLogMail("[DEBUG] From: $remitente");
        }
    } else {
        $ultimoErrorMail = "No se pudo enviar el mail (revisá la configuración del servidor de correo).";
        escribirLogMail("[ERROR] $ultimoErrorMail | Destino: $para");
    }

    return $enviado;
}

/**
 * Envia un mail con los datos de una persona.
 * Se usa desde viewPersona.php (botón "Enviar mail").
 *
 * @param array $persona     Datos de la persona (array con nombre, apellido, email...)
 * @param string $para       Email al que se quiere enviar
 * @param string $asunto     Asunto
 * @param string $mensaje    Mensaje en texto (se convierte a HTML)
 * @return bool true si se envió
 */
function enviarMailAPersona($persona, $para, $asunto, $mensaje) {
    $nombreCompleto = trim($persona['nombre'] . ' ' . $persona['apellido']);

    // Armamos un mail un poquito más lindo que un texto pelado
    $html = '<div style="font-family:Arial,sans-serif;border:1px solid #ddd;'
          . 'border-radius:8px;max-width:600px">'
          . '<div style="background:#0d6efd;color:#fff;padding:12px;text-align:center">'
          . '<h3 style="margin:0">' . textoSeguro($asunto) . '</h3></div>'
          . '<div style="padding:20px">'
          . '<p>Estimado/a <strong>' . textoSeguro($nombreCompleto) . '</strong>:</p>'
          . '<p>' . nl2br(textoSeguro($mensaje)) . '</p>'
          . '<hr>'
          . '<p style="color:#666;font-size:12px">Datos de contacto de la persona:<br>'
          . 'Email: ' . textoSeguro($persona['email']) . '<br>'
          . 'Teléfono: ' . textoSeguro($persona['telefono']) . '</p>'
          . '</div></div>';

    return enviarMail($para, $asunto, $html);
}
