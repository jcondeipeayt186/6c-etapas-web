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

Envía correos con la función `mail()` de PHP (no hace falta instalar nada).

```php
require_once '../../lib/utils/mail.php';

$ok = enviarMail('destino@ejemplo.com', 'Asunto', '<p>Hola, mensaje</p>');

if (!$ok) {
    echo obtenerUltimoErrorMail();
}
```

Y una función ya armada para mandar un mail a una persona de la base:

```php
enviarMailAPersona($persona, 'destino@ejemplo.com', 'Asunto', 'Mensaje con acentos');
```

### Configuración en el `.env`

| Variable | Qué es |
|---|---|
| `MAIL_HOST` | Servidor de correo (Gmail: `smtp.gmail.com`, Outlook: `smtp.office365.com`) |
| `MAIL_PORT` | Puerto (587) |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | Cuenta y contraseña de aplicación |
| `MAIL_FROM` / `MAIL_FROM_NAME` | Remitente |
| `MAIL_DEBUG` | `true` escribe más detalles en `mail.log` |
| `MAIL_DRY_RUN` | `true` **NO envía**: solo anota el mail en `mail.log` |

> Para probar sin configurar correo: dejá `MAIL_DRY_RUN=true`.
> Cada intento de envío queda anotado en `lib/utils/mail.log`.
