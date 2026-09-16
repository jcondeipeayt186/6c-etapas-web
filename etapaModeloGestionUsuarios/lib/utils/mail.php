<?php
/*
    lib/utils/mail.php - Librería de envío de correo (ETAPA 5)

    Objetivo: centralizar el envío de mails para toda la app (recuperar clave,
    notificaciones, avisos). No requiere composer si no está instalado PHPMailer:
    intenta usar PHPMailer si existe, si no usa SMTP puro por sockets, y como
    último fallback usa mail() nativo.

    Configuración vía .env (ver .env.example):
        MAIL_HOST=smtp.gmail.com
        MAIL_PORT=587
        MAIL_USERNAME=tu_correo@gmail.com
        MAIL_PASSWORD=tu_app_password
        MAIL_ENCRYPTION=tls   # tls | ssl | none
        MAIL_FROM=noreply@tuapp.local
        MAIL_FROM_NAME="Gestión 5"
        MAIL_DEBUG=false      # true loguea a lib/utils/mail.log
        MAIL_DRY_RUN=false    # true no envía realmente, solo loguea (útil en desarrollo sin SMTP)

    Uso básico:
        require_once __DIR__ . '/mail.php';
        $ok = enviarMail('destino@ejemplo.com', 'Asunto', '<h1>Hola</h1><p>Texto</p>');
        if ($ok) echo "Enviado"; else echo "Error: " . obtenerUltimoErrorMail();

    Con texto plano alternativo:
        enviarMail('a@b.com', 'Asunto', '<b>HTML</b>', 'Texto plano');

    Para recuperar clave (ejemplo integrado con usuarios):
        enviarMailRecuperacion('usuario@ejemplo.com', 'admin', $linkRecuperacion);
*/

if (!function_exists('cargarEnv')) {
    // Reutiliza la función de conexion.php si ya existe, si no define una mínima
    function cargarEnvMail($ruta) {
        if (!file_exists($ruta)) return;
        foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || $linea[0] === '#') continue;
            if (strpos($linea, '=') === false) continue;
            [$k,$v] = explode('=', $linea, 2);
            $k = trim($k); $v = trim(trim($v), "\"'");
            if (!array_key_exists($k, $_ENV)) {
                $_ENV[$k] = $v;
                putenv("$k=$v");
            }
        }
    }
}

function cargarConfigMail() {
    // Cargar .env si no está cargado (busca en raíz del proyecto)
    $candidatos = [
        __DIR__ . '/../../.env',
        __DIR__ . '/../.env',
        __DIR__ . '/.env',
        dirname(__DIR__, 2) . '/.env',
        getcwd() . '/.env',
    ];
    foreach ($candidatos as $p) {
        if (file_exists($p)) {
            if (function_exists('cargarEnv')) cargarEnv($p);
            else cargarEnvMail($p);
            break;
        }
    }

    $cfg = [
        'host'       => $_ENV['MAIL_HOST']        ?? getenv('MAIL_HOST')        ?: 'localhost',
        'port'       => (int)($_ENV['MAIL_PORT']  ?? getenv('MAIL_PORT')        ?: 25),
        'username'   => $_ENV['MAIL_USERNAME']    ?? getenv('MAIL_USERNAME')    ?: '',
        'password'   => $_ENV['MAIL_PASSWORD']    ?? getenv('MAIL_PASSWORD')    ?: '',
        'encryption' => strtolower($_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?: 'tls'),
        'from'       => $_ENV['MAIL_FROM']        ?? getenv('MAIL_FROM')        ?: 'noreply@localhost',
        'from_name'  => $_ENV['MAIL_FROM_NAME']   ?? getenv('MAIL_FROM_NAME')   ?: 'Gestión 5',
        'debug'      => filter_var($_ENV['MAIL_DEBUG']   ?? getenv('MAIL_DEBUG')   ?: false, FILTER_VALIDATE_BOOLEAN),
        'dry_run'    => filter_var($_ENV['MAIL_DRY_RUN'] ?? getenv('MAIL_DRY_RUN') ?: false, FILTER_VALIDATE_BOOLEAN),
    ];
    // Compat alias comunes (MAIL_USER, etc.)
    if ($cfg['username'] === '' && getenv('MAIL_USER')) $cfg['username'] = getenv('MAIL_USER');
    if ($cfg['password'] === '' && getenv('MAIL_PASS')) $cfg['password'] = getenv('MAIL_PASS');
    return $cfg;
}

$GLOBALS['_ultimo_error_mail'] = '';
$GLOBALS['_ultimo_log_mail'] = '';

function obtenerUltimoErrorMail() {
    return $GLOBALS['_ultimo_error_mail'] ?? '';
}

function obtenerUltimoLogMail() {
    return $GLOBALS['_ultimo_log_mail'] ?? '';
}

function logMail($msg) {
    $cfg = cargarConfigMail();
    $line = date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL;
    $GLOBALS['_ultimo_log_mail'] .= $line;
    if ($cfg['debug'] || $cfg['dry_run']) {
        @file_put_contents(__DIR__ . '/mail.log', $line, FILE_APPEND);
    }
}

/**
 * Envía un correo. Intenta en orden: PHPMailer > SMTP puro > mail()
 *
 * @param string $para Email destino (o "Nombre <email>" o array de emails)
 * @param string $asunto Asunto
 * @param string $html Cuerpo HTML
 * @param string $textoPlano Cuerpo texto plano (opcional, se genera si vacío)
 * @param array $opciones ['from'=>..., 'from_name'=>..., 'reply_to'=>..., 'cc'=>..., 'bcc'=>...]
 * @return bool true si se envió (o simuló en dry_run)
 */
function enviarMail($para, $asunto, $html, $textoPlano = '', $opciones = []) {
    $cfg = cargarConfigMail();
    $GLOBALS['_ultimo_error_mail'] = '';

    // Normalizar destinatarios
    if (is_string($para)) $para = [$para];
    $para = array_filter(array_map('trim', $para));
    if (empty($para)) {
        $GLOBALS['_ultimo_error_mail'] = 'Destinatario vacío';
        return false;
    }
    if ($textoPlano === '') {
        $textoPlano = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>','</li>'], ["\n","\n","\n","\n","\n"], $html)));
    }

    $from = $opciones['from'] ?? $cfg['from'];
    $fromName = $opciones['from_name'] ?? $cfg['from_name'];

    // Dry run: no enviar, solo loguear
    if ($cfg['dry_run']) {
        logMail("[DRY_RUN] Para: " . implode(', ', $para) . " | Asunto: $asunto | From: $from");
        logMail("[DRY_RUN] HTML: " . substr($html, 0, 500));
        return true;
    }

    // 1) PHPMailer si está disponible (composer)
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return enviarMailPHPMailer($cfg, $para, $asunto, $html, $textoPlano, $from, $fromName, $opciones);
    }

    // 2) SMTP puro si hay host configurado (distinto de localhost sin auth)
    if ($cfg['host'] !== 'localhost' && $cfg['host'] !== '127.0.0.1' && $cfg['username'] !== '') {
        $ok = enviarMailSMTP($cfg, $para, $asunto, $html, $textoPlano, $from, $fromName);
        if ($ok) return true;
        // si SMTP falla, intenta fallback mail() y deja el error
        logMail("SMTP falló, intentando mail() nativo. Error: " . obtenerUltimoErrorMail());
    }

    // 3) Fallback mail() nativo
    return enviarMailNativo($para, $asunto, $html, $textoPlano, $from, $fromName);
}

function enviarMailPHPMailer($cfg, $para, $asunto, $html, $textoPlano, $from, $fromName, $opciones) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        if ($cfg['debug']) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str,$level){ logMail("[PHPMailer] $str"); };
        }
        if ($cfg['host'] !== 'localhost') {
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->Port = $cfg['port'];
            $mail->SMTPAuth = $cfg['username'] !== '';
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'ssl' ? 'ssl' : ($cfg['encryption'] === 'tls' ? 'tls' : '');
            $mail->CharSet = 'UTF-8';
        }
        $mail->setFrom($from, $fromName);
        foreach ($para as $dest) $mail->addAddress($dest);
        if (!empty($opciones['reply_to'])) $mail->addReplyTo($opciones['reply_to']);
        if (!empty($opciones['cc'])) foreach((array)$opciones['cc'] as $cc) $mail->addCC($cc);
        if (!empty($opciones['bcc'])) foreach((array)$opciones['bcc'] as $bcc) $mail->addBCC($bcc);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $html;
        $mail->AltBody = $textoPlano;
        $mail->send();
        logMail("[PHPMailer] Enviado a " . implode(', ', $para) . " asunto: $asunto");
        return true;
    } catch (Exception $e) {
        $GLOBALS['_ultimo_error_mail'] = "PHPMailer: " . $e->getMessage();
        logMail($GLOBALS['_ultimo_error_mail']);
        return false;
    }
}

function enviarMailNativo($para, $asunto, $html, $textoPlano, $from, $fromName) {
    $boundary = md5(uniqid(time()));
    $headers  = "From: " . ($fromName ? "\"$fromName\" <$from>" : $from) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $headers .= "X-Mailer: Gestion5 Mail Utils\r\n";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $textoPlano . "\r\n\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $html . "\r\n\r\n";
    $body .= "--$boundary--";

    $dest = implode(', ', $para);
    // mail() espera asunto sin saltos de línea
    $asunto = str_replace(["\r","\n"], '', $asunto);
    $ok = @mail($dest, $asunto, $body, $headers);
    if ($ok) {
        logMail("[mail()] Enviado a $dest asunto: $asunto");
    } else {
        $GLOBALS['_ultimo_error_mail'] = "mail() nativo falló (verifica sendmail/postfix en el servidor)";
        logMail($GLOBALS['_ultimo_error_mail']);
    }
    return $ok;
}

/**
 * SMTP puro por sockets (sin librerías externas). Soporta STARTTLS y AUTH LOGIN.
 * Útil cuando no hay PHPMailer ni sendmail configurado.
 */
function enviarMailSMTP($cfg, $para, $asunto, $html, $textoPlano, $from, $fromName) {
    $host = $cfg['host'];
    $port = $cfg['port'];
    $user = $cfg['username'];
    $pass = $cfg['password'];
    $enc  = $cfg['encryption'];

    $boundary = md5(uniqid(time()));
    $headers  = "From: " . ($fromName ? "\"$fromName\" <$from>" : $from) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $headers .= "X-Mailer: Gestion5 SMTP\r\n";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $textoPlano . "\r\n\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $html . "\r\n\r\n";
    $body .= "--$boundary--";

    // Conexión
    $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = @fsockopen(($enc === 'ssl' ? 'ssl://' : '') . $host, $port, $errno, $errstr, 10);
    if (!$socket) {
        $GLOBALS['_ultimo_error_mail'] = "SMTP fsockopen falló: $errstr ($errno) a $remote";
        logMail($GLOBALS['_ultimo_error_mail']);
        return false;
    }

    $getResponse = function() use ($socket) {
        $resp = '';
        while ($line = fgets($socket, 512)) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $resp;
    };
    $send = function($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
        logMail("[SMTP >>] $cmd");
    };

    $resp = $getResponse(); // banner
    logMail("[SMTP <<] $resp");
    $send("EHLO localhost");
    $resp = $getResponse();
    logMail("[SMTP <<] $resp");

    if ($enc === 'tls' && stripos($resp, 'STARTTLS') !== false) {
        $send("STARTTLS");
        $resp = $getResponse();
        logMail("[SMTP <<] $resp");
        if (strpos($resp, '220') === 0) {
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $GLOBALS['_ultimo_error_mail'] = "STARTTLS crypto fail";
                fclose($socket);
                return false;
            }
            $send("EHLO localhost");
            $resp = $getResponse();
            logMail("[SMTP << TLS] $resp");
        }
    }

    if ($user !== '') {
        $send("AUTH LOGIN");
        $resp = $getResponse();
        logMail("[SMTP <<] $resp");
        $send(base64_encode($user));
        $resp = $getResponse();
        logMail("[SMTP <<] $resp");
        $send(base64_encode($pass));
        $resp = $getResponse();
        logMail("[SMTP <<] $resp");
        if (strpos($resp, '235') !== 0) {
            $GLOBALS['_ultimo_error_mail'] = "SMTP AUTH falló: $resp";
            fclose($socket);
            return false;
        }
    }

    $send("MAIL FROM:<$from>");
    $resp = $getResponse(); logMail("[SMTP <<] $resp");
    foreach ($para as $dest) {
        $send("RCPT TO:<$dest>");
        $resp = $getResponse(); logMail("[SMTP <<] $resp");
        if (strpos($resp, '250') !== 0 && strpos($resp, '251') !== 0) {
            $GLOBALS['_ultimo_error_mail'] = "SMTP RCPT falló para $dest: $resp";
            fclose($socket);
            return false;
        }
    }
    $send("DATA");
    $resp = $getResponse(); logMail("[SMTP <<] $resp");
    $data = "From: " . ($fromName ? "\"$fromName\" <$from>" : $from) . "\r\n";
    $data .= "To: " . implode(', ', $para) . "\r\n";
    $data .= "Subject: " . str_replace(["\r","\n"], '', $asunto) . "\r\n";
    $data .= $headers . "\r\n" . $body . "\r\n.";
    fwrite($socket, $data . "\r\n");
    logMail("[SMTP >>] [DATA ~".strlen($data)." bytes]");
    $resp = $getResponse(); logMail("[SMTP <<] $resp");
    $send("QUIT");
    fclose($socket);

    if (strpos($resp, '250') === 0) {
        logMail("[SMTP] Enviado OK a " . implode(', ', $para));
        return true;
    } else {
        $GLOBALS['_ultimo_error_mail'] = "SMTP DATA falló: $resp";
        return false;
    }
}

// --- Helpers de alto nivel ---

/**
 * Plantilla para mail de recuperación de clave (ejemplo)
 */
function enviarMailRecuperacion($emailDestino, $username, $linkRecuperacion, $expiraMin = 60) {
    $asunto = "Recuperación de clave - Gestión 5";
    $html = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden'>
      <div style='background:#0d6efd;color:white;padding:16px;text-align:center'><h2>Recuperar clave</h2></div>
      <div style='padding:20px'>
        <p>Hola <strong>" . htmlspecialchars($username) . "</strong>,</p>
        <p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón:</p>
        <p style='text-align:center'><a href='" . htmlspecialchars($linkRecuperacion) . "' style='display:inline-block;background:#0d6efd;color:white;padding:12px 24px;border-radius:6px;text-decoration:none'>Restablecer clave</a></p>
        <p>O copia este link: <br><small><a href='" . htmlspecialchars($linkRecuperacion) . "'>" . htmlspecialchars($linkRecuperacion) . "</a></small></p>
        <p><small>Este enlace expira en $expiraMin minutos. Si no fuiste tú, ignora este correo.</small></p>
      </div>
      <div style='background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#6c757d'>Gestión 5 - IPEAyT 186 - " . date('Y') . "</div>
    </div>";
    $texto = "Hola $username\n\nPara restablecer tu clave visita: $linkRecuperacion\nExpira en $expiraMin min.\n\nSi no fuiste tú, ignora este correo.";
    return enviarMail($emailDestino, $asunto, $html, $texto);
}

function enviarMailBienvenida($emailDestino, $username, $claveTemporal = null) {
    $asunto = "Bienvenido a Gestión 5";
    $html = "<h2>Bienvenido, " . htmlspecialchars($username) . "</h2><p>Tu usuario fue creado correctamente.</p>";
    if ($claveTemporal) $html .= "<p>Clave temporal: <code>" . htmlspecialchars($claveTemporal) . "</code> (cambiala al ingresar).</p>";
    $html .= "<p>Ingresá en <a href='#'>la plataforma</a>.</p>";
    return enviarMail($emailDestino, $asunto, $html);
}

function enviarMailNotificacionGenerica($para, $asunto, $mensajeHtml) {
    $html = "<div style='font-family:Arial,sans-serif;padding:20px'>" . $mensajeHtml . "</div>";
    return enviarMail($para, $asunto, $html);
}
