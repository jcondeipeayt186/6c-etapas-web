<?php
/*
    lib/utils/mail.php - Envío de correos por SMTP

    Librería para enviar mails sin instalar nada en el servidor.
    Se conecta DIRECTO al servidor de correo (Gmail, Outlook, etc.) usando
    las variables del archivo .env:

        MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD,
        MAIL_ENCRYPTION, MAIL_FROM, MAIL_FROM_NAME,
        MAIL_DEBUG, MAIL_DRY_RUN, MAIL_LOG

    ------------------------------------------------------------------------
    ¿POR QUÉ NO USAMOS mail()?
    ------------------------------------------------------------------------
    La función mail() de PHP no habla SMTP: le entrega el mail a un programa
    del sistema llamado sendmail (o postfix) que muchas veces NO está
    instalado. En Ubuntu, por ejemplo, se ve así:

        Warning: mail(): sh: 1: /usr/sbin/sendmail: not found

    Ese es el error típico cuando los datos del .env están bien pero el mail
    no sale. Por eso acá hablamos el idioma SMTP directamente por una
    "tubería" (un socket) con fsockopen(), sin depender de nada externo.

    ------------------------------------------------------------------------
    CÓMO CONFIGURARLO (ejemplo con Gmail)
    ------------------------------------------------------------------------
    1. En la cuenta de Google hay que tener activo la verificación en 2 pasos.
    2. Generar una "contraseña de aplicación" (16 letras) en
       seguridad.google.com -> Contraseñas de aplicación.
    3. Poner en el .env:

        MAIL_HOST=smtp.gmail.com
        MAIL_PORT=587
        MAIL_ENCRYPTION=tls
        MAIL_USERNAME=micorreo@gmail.com
        MAIL_PASSWORD=la-contrasena-de-aplicacion
        MAIL_FROM=micorreo@gmail.com      (tiene que ser la misma cuenta)
        MAIL_DRY_RUN=false
        MAIL_DEBUG=true                   (para ver la traza mientras pruebas)

    ------------------------------------------------------------------------
    ¿QUÉ ES MAIL_DRY_RUN?
    - true  -> NO manda el mail. Solo escribe en el log (MAIL_LOG) lo que se
               habría enviado. Ideal para probar sin tener correo configurado.
    - false -> manda el mail de verdad.

    ------------------------------------------------------------------------
    ¿QUÉ ES MAIL_DEBUG?
    - true  -> escribe en el log toda la conversación con el servidor de
               correo. Es lo primero que hay que mirar si un mail no sale.

    ¿Dónde se ve el log?
        tail -f lib/utils/mail.log

    ------------------------------------------------------------------------
    USO
    ------------------------------------------------------------------------
        require_once '../../lib/utils/mail.php';

        $ok = enviarMail('destino@ejemplo.com', 'Asunto', '<p>Hola</p>');
        if (!$ok) {
            echo 'Error: ' . obtenerUltimoErrorMail();
        }
*/

// Para leer el .env necesitamos cargarEnv() y valorEntorno(), que están en varios.php
require_once __DIR__ . '/varios.php';

// Guarda el último error del envío y el último error del log, para poder
// mostrarlos en la página por separado
$ultimoErrorMail = '';
$ultimoErrorLog  = '';
$trazaSmtp        = '';

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
 * Devuelve el último problema con el archivo de log (permisos, por ejemplo).
 * Es distinto del error de envío: el mail puede haberse enviado bien y
 * solamente no haberse podido anotar.
 *
 * @return string Texto con el error del log (vacío si no hubo)
 */
function obtenerUltimoErrorLog() {
    global $ultimoErrorLog;
    return $ultimoErrorLog;
}

/**
 * Devuelve la conversación con el servidor de correo (útil para depurar).
 *
 * @return string Traza SMTP acumulada
 */
function obtenerTrazaSmtp() {
    global $trazaSmtp;
    return $trazaSmtp;
}

/**
 * Devuelve la ruta ABSOLUTA del archivo de log de mails.
 * La ruta sale del .env (MAIL_LOG) y es relativa a la raíz del proyecto.
 *
 * @return string Ruta absoluta (por ejemplo /var/www/proyecto/lib/utils/mail.log)
 */
function rutaLogMail() {
    cargarEnv(__DIR__ . '/../../.env');
    $log = valorEntorno('MAIL_LOG', 'lib/utils/mail.log');

    // __DIR__ es la carpeta de este archivo (lib/utils), ../.. es la raíz del proyecto
    return dirname(__DIR__, 2) . '/' . $log;
}

/**
 * Escribe una línea en el archivo de log de mails.
 *
 * Si el archivo no se puede escribir (lo común cuando el log es del usuario
 * de la terminal y el servidor web corre como www-data), guarda el motivo en
 * obtenerUltimoErrorLog() para poder avisarlo en la página.
 *
 * @param string $mensaje Texto a escribir en el log
 * @return void
 */
function escribirLogMail($mensaje) {
    global $ultimoErrorLog;

    $rutaLog = rutaLogMail();
    $linea   = date('Y-m-d H:i:s') . ' - ' . $mensaje . PHP_EOL;

    // Si el archivo todavía no existe, lo creamos (touch = crear vacío)
    if (!file_exists($rutaLog) && is_dir(dirname($rutaLog)) && is_writable(dirname($rutaLog))) {
        touch($rutaLog);
    }

    // Chequeamos permiso de escritura ANTES de escribir, así no se nos
    // rompe la página con un warning de PHP
    if (!is_writable($rutaLog)) {
        $ultimoErrorLog = 'No se pudo escribir el log de mails en ' . $rutaLog
            . '. En Linux hay que darle permiso al usuario del servidor (www-data): sudo chown www-data:www-data '
            . $rutaLog;
        return;
    }

    file_put_contents($rutaLog, $linea, FILE_APPEND);
}

/**
 * Anota una línea de la conversación SMTP.
 * Solo se escribe en el log si MAIL_DEBUG=true (o si estamos en modo prueba).
 *
 * @param string $texto   Texto de la línea
 * @param string $tipo    ">>" lo que mandamos, "<<" lo que responde el servidor
 * @param bool   $forzar  Escribe aunque MAIL_DEBUG esté en false
 * @return void
 */
function anotarTraza($texto, $tipo = '>>', $forzar = false) {
    global $trazaSmtp;

    $linea = '[SMTP ' . $tipo . '] ' . trim($texto);
    $trazaSmtp .= $linea . PHP_EOL;

    if ($forzar) {
        escribirLogMail($linea);
    } else {
        // Se escribe solo si el debug está activado (ver enviarMailSmtp)
        if (valorEntorno('MAIL_DEBUG', 'false') === 'true') {
            escribirLogMail($linea);
        }
    }
}

/**
 * Arma el cuerpo del mail: una versión en texto plano y otra en HTML.
 * Se llama "multipart/alternative" porque el cliente de correo muestra el
 * HTML si puede, y el texto plano si no.
 *
 * @param string $html      Contenido en HTML
 * @param string $texto     Contenido en texto plano
 * @return array  Array con 'encabezados' y 'cuerpo' ya armados
 */
function armarCuerpoMail($html, $texto) {
    // El "límite" es un texto inventado que separa las dos versiones
    $limite = 'bnd_' . md5(uniqid('mail', true));

    $encabezados  = "MIME-Version: 1.0\r\n";
    $encabezados .= "Content-Type: multipart/alternative; boundary=\"$limite\"\r\n";

    $cuerpo  = "--$limite\r\n";
    $cuerpo .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $cuerpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $cuerpo .= $texto . "\r\n\r\n";

    $cuerpo .= "--$limite\r\n";
    $cuerpo .= "Content-Type: text/html; charset=UTF-8\r\n";
    $cuerpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $cuerpo .= $html . "\r\n\r\n";

    $cuerpo .= "--$limite--";

    return array('encabezados' => $encabezados, 'cuerpo' => $cuerpo);
}

/**
 * Codifica un texto con acentos para que pueda ir en un encabezado del mail.
 * Los encabezados de mail son ASCII puro, así que "Prueba áéíóú" tiene que
 * viajar codificado (=?UTF-8?B?...?=) o el servidor de correo lo rechaza.
 *
 * @param string $texto Texto a codificar
 * @return string Texto listo para un encabezado
 */
function codificarEncabezadoMail($texto) {
    // Si no tiene nada raro (ni acentos ni símbolos), se deja como está
    if (preg_match('/^[\x20-\x7E]*$/', $texto)) {
        return $texto;
    }
    return '=?UTF-8?B?' . base64_encode($texto) . '?=';
}

/**
 * Envía un correo electrónico por SMTP.
 *
 * @param string $para        Email de destino (ej: juan@mail.com)
 * @param string $asunto      Asunto del mail
 * @param string $mensajeHtml Contenido del mail en HTML
 * @param string $textoPlano  Contenido en texto plano (opcional)
 * @return bool true si se envió (o se simuló con DRY_RUN)
 */
function enviarMail($para, $asunto, $mensajeHtml, $textoPlano = '') {
    global $ultimoErrorMail, $ultimoErrorLog, $trazaSmtp;
    $ultimoErrorMail = '';
    $ultimoErrorLog  = '';
    $trazaSmtp        = '';

    // Leemos la configuración del archivo .env
    cargarEnv(__DIR__ . '/../../.env');

    $remitente      = valorEntorno('MAIL_FROM', 'noreply@localhost');
    $nombreRemitente = valorEntorno('MAIL_FROM_NAME', 'Gestion Personas y Ciudades');
    $debug          = valorEntorno('MAIL_DEBUG', 'false') === 'true';
    $dryRun         = valorEntorno('MAIL_DRY_RUN', 'true') === 'true';

    // Si el texto plano viene vacío, lo sacamos del HTML (strip_tags)
    if ($textoPlano === '') {
        $textoPlano = trim(strip_tags(str_replace(array('</p>', '</h3>', '<br>', '<br/>', '<br />'), "\n", $mensajeHtml)));
    }

    // Chequeamos que el destino tenga forma de email (y no solo el navegador)
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
        $ultimoErrorMail = 'El email de destino no es válido: ' . $para;
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    // 1. Modo prueba: no se manda nada, solo se anota en el log
    if ($dryRun) {
        escribirLogMail("[SIMULADO] Para: $para | Asunto: $asunto | Desde: $remitente");
        escribirLogMail('[SIMULADO] Mensaje: ' . $textoPlano);

        // En modo prueba el log es lo único que hay, así que si no se pudo
        // escribir devolvemos false para que la página avise el problema
        // en vez de decir "se envió" sin dejar ningún rastro
        return obtenerUltimoErrorLog() === '';
    }

    // 2. Envío real por SMTP
    return enviarMailSmtp($para, $asunto, $mensajeHtml, $textoPlano, $remitente, $nombreRemitente, $debug);
}

/**
 * Hace de verdad la conversación SMTP con el servidor de correo.
 *
 * El protocolo es un "diálogo" de pasos: cada comando se escribe y el
 * servidor contesta con un número:
 *     220 = todo bien      250 = hecho      235 = login correcto
 *     334 = te pido algo   535 = login mal  550 = no existe / no permitido
 *
 * @param string $para           Destinatario
 * @param string $asunto         Asunto
 * @param string $html           Cuerpo en HTML
 * @param string $textoPlano     Cuerpo en texto plano
 * @param string $remitente      Email que envía (MAIL_FROM)
 * @param string $nombreRemitente Nombre que se ve en el From
 * @param bool   $debug          Escribir la traza en el log
 * @return bool true si el servidor confirmó el envío
 */
function enviarMailSmtp($para, $asunto, $html, $textoPlano, $remitente, $nombreRemitente, $debug) {
    global $ultimoErrorMail;

    $host       = valorEntorno('MAIL_HOST', '');
    $puerto     = (int) valorEntorno('MAIL_PORT', '587');
    $usuario    = valorEntorno('MAIL_USERNAME', '');
    $clave      = valorEntorno('MAIL_PASSWORD', '');
    $cifrado    = strtolower(valorEntorno('MAIL_ENCRYPTION', 'tls'));

    if ($host === '') {
        $ultimoErrorMail = 'No está configurado MAIL_HOST en el archivo .env, '
            . 'o se puede probar con MAIL_DRY_RUN=true para no enviar nada.';
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    // ================= 1) ABRIMOS LA TUBERÍA =================
    // Con "ssl://" la conexión ya viene cifrada (puerto 465).
    // Con "tls" abrimos en texto plano y después pedimos STARTTLS (puerto 587).
    $prefijo = ($cifrado === 'ssl') ? 'ssl://' : '';
    $socket  = @fsockopen($prefijo . $host, $puerto, $numeroError, $textoError, 15);

    if (!$socket) {
        $ultimoErrorMail = "No se pudo conectar con el servidor de correo $host:$puerto ($textoError). "
            . 'Revisá MAIL_HOST y MAIL_PORT en el .env. Si tu red bloquea el puerto 587, '
            . 'probá con MAIL_PORT=465 y MAIL_ENCRYPTION=ssl.';
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    // Que no se cuelgue si el servidor deja de responder
    stream_set_timeout($socket, 15);

    // Helpers para leer y escribir en el socket
    $leer = function () use ($socket) {
        $respuesta = '';
        // El servidor puede mandar varias líneas: la última tiene un espacio
        // en la 4ta posición ("250-OK" seguido de "250 LISTO")
        while (($linea = fgets($socket, 1024)) !== false) {
            $respuesta .= $linea;
            if (isset($linea[3]) && $linea[3] === ' ') {
                break;
            }
        }
        return $respuesta;
    };

    $decir = function ($comando) use ($socket) {
        fwrite($socket, $comando . "\r\n");
        anotarTraza($comando, '>>');
    };

    $contestar = function () use ($leer) {
        $respuesta = $leer();
        anotarTraza($respuesta, '<<');
        return $respuesta;
    };

    // ================= 2) SALUDO =================
    $respuesta = $contestar();                       // 220 (banner de bienvenida)
    if (strpos($respuesta, '220') !== 0) {
        $ultimoErrorMail = 'El servidor de correo no respondió con un saludo válido: ' . trim($respuesta);
        fclose($socket);
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    // ================= 3) NOS PRESENTAMOS (EHLO) =================
    $decir('EHLO ' . valorEntorno('MAIL_EHLO', 'localhost'));
    $respuesta = $contestar();

    // ================= 4) CIFRADO TLS =================
    if ($cifrado === 'tls') {
        if (stripos($respuesta, 'STARTTLS') === false) {
            $ultimoErrorMail = 'El servidor de correo no ofrece STARTTLS. '
                . 'Probá con MAIL_PORT=465 y MAIL_ENCRYPTION=ssl.';
            fclose($socket);
            escribirLogMail('[ERROR] ' . $ultimoErrorMail);
            return false;
        }

        $decir('STARTTLS');
        $respuesta = $contestar();                   // 220 (listo para cifrar)

        // Recién ahora se activa el cifrado sobre la misma conexión
        if (strpos($respuesta, '220') !== 0
            || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $ultimoErrorMail = 'No se pudo activar el cifrado TLS con el servidor de correo.';
            fclose($socket);
            escribirLogMail('[ERROR] ' . $ultimoErrorMail);
            return false;
        }

        // Después del cifrado hay que volver a presentarse
        $decir('EHLO ' . valorEntorno('MAIL_EHLO', 'localhost'));
        $respuesta = $contestar();
    }

    // ================= 5) LOGIN (AUTH LOGIN) =================
    if ($usuario !== '') {
        $decir('AUTH LOGIN');
        $respuesta = $contestar();                   // 334 (mandá el usuario)

        // El usuario y la clave viajan codificados en base64
        $decir(base64_encode($usuario));
        $respuesta = $contestar();                   // 334 (mandá la clave)

        $decir(base64_encode($clave));
        $respuesta = $contestar();                   // 235 (dentro) o 535 (mal)

        if (strpos($respuesta, '235') !== 0) {
            $ultimoErrorMail = 'El servidor de correo rechazó el usuario o la contraseña: '
                . trim(str_replace("\n", ' ', $respuesta))
                . ' | Ojo: en Gmail no va la contraseña normal de la cuenta, sino una '
                . '"contraseña de aplicación" de 16 letras (se genera en '
                . 'seguridad.google.com con la verificación en 2 pasos activada).';
            fclose($socket);
            escribirLogMail('[ERROR] ' . $ultimoErrorMail);
            return false;
        }
    }

    // ================= 6) REMITENTE Y DESTINATARIO =================
    $decir('MAIL FROM:<' . $remitente . '>');
    $respuesta = $contestar();                       // 250

    $decir('RCPT TO:<' . $para . '>');
    $respuesta = $contestar();                       // 250 o 550
    if (strpos($respuesta, '250') !== 0 && strpos($respuesta, '251') !== 0) {
        $ultimoErrorMail = 'El servidor de correo no acepta el destinario ' . $para . ': ' . trim($respuesta);
        fclose($socket);
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    // ================= 7) EL MENSAJE =================
    $decir('DATA');
    $respuesta = $contestar();                       // 354 (mandá el contenido)
    if (strpos($respuesta, '354') !== 0) {
        $ultimoErrorMail = 'El servidor de correo no pidió el contenido del mail: ' . trim($respuesta);
        fclose($socket);
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    $partes  = armarCuerpoMail($html, $textoPlano);
    $contenido  = 'From: ' . codificarEncabezadoMail($nombreRemitente) . ' <' . $remitente . ">\r\n";
    $contenido .= 'To: <' . $para . ">\r\n";
    $contenido .= 'Subject: ' . codificarEncabezadoMail($asunto) . "\r\n";
    $contenido .= $partes['encabezados'] . "\r\n\r\n";
    $contenido .= $partes['cuerpo'];

    // Regla del protocolo: si una línea del mensaje empieza con un punto,
    // hay que "duplicarlo" para que no se confunda con el fin del mensaje
    $contenido = preg_replace('/^\./m', '..', $contenido);

    fwrite($socket, $contenido . "\r\n.\r\n");
    anotarTraza('[mensaje completo: ' . strlen($contenido) . ' bytes]', '>>');
    $respuesta = $contestar();                       // 250 (¡mandado!) o 550

    $decir('QUIT');
    fclose($socket);

    if (strpos($respuesta, '250') !== 0) {
        $ultimoErrorMail = 'El servidor de correo no aceptó el mensaje: ' . trim(str_replace("\n", ' ', $respuesta));
        escribirLogMail('[ERROR] ' . $ultimoErrorMail);
        return false;
    }

    escribirLogMail("[ENVIADO] Para: $para | Asunto: $asunto | Desde: $remitente");
    if ($debug) {
        escribirLogMail('[DEBUG] Servidor: ' . $host . ':' . $puerto . ' | Cifrado: ' . $cifrado);
    }

    return true;
}

/**
 * Envia un mail con los datos de una persona.
 * Se usa desde viewPersona.php (botón "Enviar mail").
 *
 * @param array  $persona Datos de la persona (array con nombre, apellido, email...)
 * @param string $para    Email al que se quiere enviar
 * @param string $asunto  Asunto
 * @param string $mensaje Mensaje en texto (se convierte a HTML)
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
