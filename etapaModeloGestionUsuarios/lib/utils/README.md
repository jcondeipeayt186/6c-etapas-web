# lib/utils — Utilidades transversales

## mail.php — Librería de envío de correo

Librería **sin dependencias** para enviar mail desde cualquier parte de la app (`src/*` o `lib/*`). Orden de envío:

1. **PHPMailer** (si está instalado vía Composer `composer require phpmailer/phpmailer` → `class_exists` detecta y lo usa)
2. **SMTP puro por sockets** (`fsockopen` + `STARTTLS` + `AUTH LOGIN`) si hay `MAIL_HOST`/`MAIL_USERNAME` configurados
3. Fallback **`mail()` nativo** (requiere `sendmail`/`postfix`)

### Configuración (.env)

```ini
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_app_password_de_16_caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM=noreply@tuapp.local
MAIL_FROM_NAME="Gestión 5"
MAIL_DEBUG=false
MAIL_DRY_RUN=false
```

- `MAIL_DEBUG=true` escribe traza en `lib/utils/mail.log`
- `MAIL_DRY_RUN=true` simula envío (no contacta SMTP, solo loguea) — útil en desarrollo sin credenciales reales
- Si dejás `MAIL_HOST=localhost` y sin usuario, usa `mail()` directo

**Gmail ejemplo:** puerto 587 + tls, contraseña de aplicación (no la personal). Para Outlook: `smtp.office365.com:587`.

### Uso

```php
require_once __DIR__ . '/../../lib/utils/mail.php';

// Simple
$ok = enviarMail('destino@ejemplo.com', 'Hola', '<h1>Hola</h1><p>Bienvenido</p>');
if (!$ok) echo obtenerUltimoErrorMail();

// Con texto plano y opciones
enviarMail(
    ['a@b.com','c@d.com'],
    'Asunto',
    '<b>HTML</b>',
    'Texto plano alternativo',
    ['reply_to'=>'soporte@tuapp.local', 'cc'=>'copia@ejemplo.com']
);

// Plantilla de recuperación (usada por src/auth/recuperar.php)
$link = "https://tuapp.local/src/auth/restablecer.php?token=abc123";
enviarMailRecuperacion('usuario@ejemplo.com', 'admin', $link, 60);
```

### Helpers incluidos

- `enviarMail($para,$asunto,$html,$textoPlano,$opciones)` — principal
- `enviarMailRecuperacion($email,$username,$link,$expiraMin)` — plantilla recuperación
- `enviarMailBienvenida($email,$username,$claveTemporal)`
- `enviarMailNotificacionGenerica($para,$asunto,$html)`
- `obtenerUltimoErrorMail()` / `obtenerUltimoLogMail()` — diagnóstico
- `cargarConfigMail()` — lee `.env`

### Integración con `src/auth/recuperar.php`

El formulario de **Olvidé mi clave** ahora puede enviar mail real si completás `.env`. Si `MAIL_DRY_RUN=true` verás el contenido en `mail.log` sin enviar.

### Sin Composer

No necesitás instalar nada: la librería funciona con `mail()` o SMTP por sockets usando solo extensiones estándar (`openssl` para TLS, ya incluida en PHP 8.3). Si instalás PHPMailer ganás validación extra y mejor manejo de adjuntos.

### Prueba rápida

```bash
php -r "require 'lib/utils/mail.php'; var_dump(enviarMail('test@example.com','Prueba','<b>Test</b>'));"
cat lib/utils/mail.log
```
