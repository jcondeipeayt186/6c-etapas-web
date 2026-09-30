# lib/utils — Utilidades de la aplicación

Funciones "sueltas" que se usan en varias partes del proyecto y que **no**
son ni la base de datos ni el HTML.

| Archivo | Para qué sirve | Funciones principales |
|---|---|---|
| `varios.php` | Leer el `.env`, formatear fechas, escapar textos | `cargarEnv()`, `valorEntorno()`, `fechaFormato()`, `textoSeguro()` |
| `archivos.php` | Guardar los archivos que sube el usuario (avatar y CV) | `guardarArchivoSubido()`, `borrarArchivo()`, `carpetaArchivos()` |
| `mail.php` | Enviar correos | `enviarMail()`, `enviarMailAPersona()`, `obtenerUltimoErrorMail()` |
| `mail.log` | Textual donde se anota lo que pasa con cada mail | (se genera solo) |

---

## 1. `varios.php`

### `cargarEnv($ruta)` y `valorEntorno($nombre, $porDefecto)`

Lee el archivo `.env` de la raíz del proyecto y guarda cada línea
`CLAVE=valor` en el array `$_ENV`.

```php
require_once 'lib/utils/varios.php';
cargarEnv(__DIR__ . '/../../.env');

$host = valorEntorno('DB_HOST', 'localhost');   // si no está, usa "localhost"
```

### `fechaFormato($fecha)`

MySQL guarda las fechas como `2026-05-14`. Esta función las muestra
en formato argentino: `14/05/2026`.

```php
echo fechaFormato($persona['fecha_nacimiento']);   // 14/05/2026
```

### `textoSeguro($texto)`

Equivale a `htmlspecialchars()`. Evita que un dato guardado en la base
rompa el HTML. **Usarlo siempre** que se muestre un dato de la base.

---

## 2. `archivos.php`

Guarda en el servidor los archivos que el usuario elige en un
`<input type="file">`.

```
files/
├── avatars/    -> fotos de las personas
└── cv/         -> currículums
```

En la tabla `personas` solo se guardan las rutas (`avatar_path`, `cv_path`),
nunca el archivo en sí.

```php
require_once '../../lib/utils/archivos.php';

// En el formulario: <input type="file" name="avatar">
$ruta = guardarArchivoSubido($_FILES['avatar'], 'avatars', 'avatar');

if ($ruta === null) {
    echo obtenerUltimoErrorArchivo();   // "Tipo de archivo no permitido..."
}
```

La función ya controla:
- que el archivo no sea demasiado grande (`TAMANIO_MAXIMO_MB` del `.env`),
- que la extensión esté permitida (jpg, png, pdf, doc, docx...),
- que la carpeta destino exista (la crea si falta),
- que el nombre sea único (le agrega fecha y número al final).

Cuando se borra una persona conviene borrar también sus archivos:

```php
borrarArchivo($persona['cv_path']);
```

---

## 3. `mail.php`

Envía correos hablando **SMTP directamente** (con `fsockopen()`), sin instalar
nada en el servidor y sin depender de `mail()`.

```php
require_once '../../lib/utils/mail.php';

$ok = enviarMail('destino@ejemplo.com', 'Asunto', '<p>Hola, mensaje</p>');

if (!$ok) {
    echo obtenerUltimoErrorMail();     // el error del envío
}
if (obtenerUltimoErrorLog() !== '') {
    echo obtenerUltimoErrorLog();      // el mail salió, pero no se pudo anotar
}
```

Y una función ya armada para mandar un mail a una persona de la base:

```php
enviarMailAPersona($persona, 'destino@ejemplo.com', 'Asunto', 'Mensaje con acentos');
```

### ¿Por qué no `mail()`?

`mail()` no habla SMTP: le entrega el mail a `/usr/sbin/sendmail`, que en
muchos servidores no está instalado. El síntoma es el siguiente:

```
Warning: mail(): sh: 1: /usr/sbin/sendmail: not found
```

O sea: los datos del `.env` están bien, pero el mail no sale. Esta librería
evita ese problema conectándose directo al servidor de correo.

### El diálogo SMTP

`enviarMailSmtp()` hace, en orden, los pasos del protocolo (cada comando
espera la respuesta del servidor, que empieza con un número):

| Paso | Se manda | Respuesta buena |
|---|---|---|
| 1 | (saludo del servidor) | `220` |
| 2 | `EHLO localhost` | `250` |
| 3 | `STARTTLS` y activate el cifrado | `220` |
| 4 | `EHLO localhost` (de nuevo) | `250` |
| 5 | `AUTH LOGIN` + usuario y clave en base64 | `235` |
| 6 | `MAIL FROM:<remitente>` | `250` |
| 7 | `RCPT TO:<destinatario>` | `250` |
| 8 | `DATA` + el mensaje + `.` | `250` |
| 9 | `QUIT` | — |

Los errores típicos:

| En el log | Qué significa |
|---|---|
| `535` en `AUTH LOGIN` | usuario o contraseña incorrectos. En Gmail hace falta una **contraseña de aplicación**, no la contraseña normal |
| `SMTP fsockopen falló` | no hay salida a internet o el puerto está bloqueado. Probá `MAIL_PORT=465` con `MAIL_ENCRYPTION=ssl` |
| `no ofrece STARTTLS` | probá con `ssl` en el puerto 465 |
| `no acepta el destinario` | el email escrito no existe o no lo acepta el servidor |

### Configuración en el `.env`

| Variable | Qué es |
|---|---|
| `MAIL_HOST` | Servidor de correo (Gmail: `smtp.gmail.com`, Outlook: `smtp.office365.com`) |
| `MAIL_PORT` | Puerto (587 con `tls`, 465 con `ssl`) |
| `MAIL_ENCRYPTION` | `tls` (puerto 587), `ssl` (puerto 465) o `none` |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | Cuenta y **contraseña de aplicación** |
| `MAIL_FROM` / `MAIL_FROM_NAME` | Remitente (en Gmail, la misma cuenta que `MAIL_USERNAME`) |
| `MAIL_DEBUG` | `true` escribe toda la traza SMTP en el log |
| `MAIL_DRY_RUN` | `true` **NO envía**: solo anota el mail en el log |
| `MAIL_LOG` | Dónde queda ese log (por defecto `lib/utils/mail.log`) |

> Para probar sin configurar correo: dejá `MAIL_DRY_RUN=true`.
> Cada intento de envío queda anotado en `lib/utils/mail.log` y se ve con:
> ```bash
> tail -f lib/utils/mail.log
> ```
>
> **Ojo con los permisos:** el log lo escribe el servidor web, no el usuario de
> la terminal. Con Apache (que corre como `www-data`) hay que hacer una vez:
> ```bash
> sudo chown www-data:www-data lib/utils/mail.log
> ```
> Si no, `escribirLogMail()` no puede escribir y `obtenerUltimoErrorLog()`
> devuelve el comando exacto que hay que ejecutar.
