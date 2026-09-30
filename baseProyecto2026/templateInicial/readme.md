# templateInicial — Base del proyecto "Gestión de Personas y Ciudades"

> Proyecto base en **PHP + MySQL + Bootstrap 5** para empezar a trabajar.
> Viene con la base de datos ya normalizada en **dos tablas** (`personas` y
> `ciudades`), la configuración en un archivo **.env**, librerías separadas por
> módulo, **subida de archivos** y **envío de mails**.

---

## 1. ¿Qué hace la aplicación?

| Funcionalidad | Dónde se usa | Qué hace en la base |
|---|---|---|
| **Portada** | `index.php` | Solo muestra el cartel y el botón **INGRESAR** (no usa la base) |
| **Inicio / tablero** | `src/index.php` | Muestra cuántas personas y ciudades hay | `SELECT COUNT(*)` |
| **Listar personas con filtro** | `src/persona/viewPersona.php` | Tabla con foto, CV y ciudad de cada persona | `SELECT ... JOIN ... WHERE nombre LIKE ?` |
| **Buscar ciudades (JSON)** | `src/ciudad/buscarCiudad.php` | Devuelve ciudades que empiezan con un texto (lo usa el buscador del formulario) | `SELECT ... WHERE nombre LIKE ? LIMIT 20` |
| **Crear persona** | `src/persona/editPersona.php` sin `?id=` | Formulario con ciudad, foto y CV | `INSERT INTO personas` |
| **Modificar persona** | `src/persona/editPersona.php?id=X` | Formulario precargado | `UPDATE personas ... WHERE id = ?` |
| **Eliminar persona** | Botón "Eliminar" | Borra también los archivos del servidor | `DELETE FROM personas WHERE id = ?` |
| **Enviar mail** | Botón "Enviar mail" (modal) | Manda un correo a la persona elegida | (no toca la base) |
| **Listar ciudades** | `src/ciudad/viewCiudad.php` | Tabla con **cuántas personas nacieron en cada ciudad** | `SELECT ... COUNT(p.id) ... GROUP BY c.id` |
| **Crear / editar ciudad** | `src/ciudad/editCiudad.php` | Formulario de ciudades | `INSERT` / `UPDATE` |
| **Eliminar ciudad** | Botón "Eliminar" | Solo si no tiene personas (lo impide la clave foránea) | `DELETE FROM ciudades WHERE id = ?` |
| **Agregar un módulo (ejercicio)** | `src/template/` | Copiar las plantillas y crear un módulo nuevo: en el [paso a paso](#8-agregar-un-módulo-nuevo-el-caso-de-los-pa%C3%ADses) se hace con **países**, que además agrega una relación nueva | `CREATE TABLE pais` + `ALTER TABLE ciudades` |

> El módulo de países **no viene hecho**: es el ejercicio de la sección 8.
> Ahí está el paso a paso completo (SQL, modelo, páginas, relación con
> ciudades y pruebas).

---

## 2. Estructura del proyecto

```
templateInicial/
├── .env                     # Configuración (base de datos, mail, archivos) - NO se sube al repo
├── .env.example             # Plantilla del .env para copiar y completar
├── .gitignore               # Ignora el .env y los archivos subidos por los usuarios
├── index.php                # PORTADA (solo HTML, sin base de datos)
├── readme.md                # Este archivo
│
├── img/                     # Imágenes de la portada
│   ├── LogoCasta.png
│   ├── image.png
│   ├── cartel.png
│   └── mysql.png
│
├── files/                   # Archivos que suben los usuarios
│   ├── avatars/             # fotos de las personas
│   └── cv/                  # currículums
│
├── lib/                     # Librerías (no se navegan, se incluyen)
│   ├── bd/
│   │   ├── gestionBaseDatos.php  # La CONEXIÓN (lee el .env)
│   │   ├── personas-bd.php       # Consultas de la tabla personas
│   │   ├── ciudades-bd.php       # Consultas de la tabla ciudades
│   │   ├── script.sql            # Crea la base y las tablas
│   │   ├── script_inserts.sql    # Datos de ejemplo
│   │   ├── script_update.sql     # Ejemplos de UPDATE
│   │   ├── actualizar_base.sql   # Para bases que ya existían (ALTER TABLE)
│   │   ├── script_consultas.sql  # Ejemplos de SELECT
│   │   ├── script_delete.sql     # Ejemplos de DELETE
│   │   └── paises-bd.php         # (ejercicio de la sección 8) tabla pais
│   ├── html/
│   │   └── funcionesHTML.php     # piePagina(), mostrarAlerta()
│   └── utils/
│       ├── varios.php            # leer el .env, formato de fechas, escapar textos
│       ├── archivos.php          # subida de archivos (avatar y CV)
│       ├── mail.php              # envío de correos
│       ├── mail.log              # registro de los mails enviados
│       └── readme.md             # detalle de estas librerías
│
└── src/                     # Páginas y controladores de cada módulo
    ├── index.php             # Inicio / tablero
    ├── persona/
    │   ├── viewPersona.php   # Listado + filtro + mail
    │   ├── editPersona.php   # Formulario (alta/edición) + combo de ciudades + archivos
    │   ├── gestionPersona.php# INSERT / UPDATE / DELETE (sin HTML)
    │   └── enviarMail.php    # Envía el mail del modal (sin HTML)
    ├── ciudad/
    │   ├── viewCiudad.php    # Listado + cantidad de personas por ciudad
    │   ├── editCiudad.php    # Formulario de ciudades
    │   ├── gestionCiudad.php # INSERT / UPDATE / DELETE (sin HTML)
    │   └── buscarCiudad.php  # Devuelve ciudades en JSON (para el buscador)
    └── template/             # PLANTILLAS para crear un módulo nuevo
        ├── viewModulo.php       # Listado
        ├── editModulo.php       # Formulario
        ├── gestionModulo.php    # INSERT / UPDATE / DELETE
        ├── avisoPlantilla.php   # Guía paso a paso (con el ejemplo de países)
        └── readme.md         # Cómo usarlas (y su relación con el patrón MVP)
```

Al terminar el ejercicio de la [sección 8](#8-agregar-un-módulo-nuevo-el-caso-de-los-pa%C3%ADses)
sumarías dos carpetas más:

```
    ├── pais/                # (ejercicio) módulo nuevo
    │   ├── viewPais.php
    │   ├── editPais.php
    │   └── gestionPais.php
    └── ciudad/
        └── (los archivos ya existentes, con la columna pais_id)
```

### Navegables vs. no navegables

| Tipo | Archivos | ¿El usuario los ve? |
|---|---|---|
| **Páginas (vista)** | `index.php`, `src/index.php`, `view*`, `edit*` | Sí: HTML, tablas, formularios |
| **Procesadores** | `gestion*`, `enviarMail.php`, `buscarCiudad.php` | No: reciben datos, trabajan y redirigen |
| **Librerías** | `lib/**` | No: se incluyen con `require_once` |

---

## 3. Configuración: el archivo `.env`

Todos los datos configurables (base de datos, correo, carpetas) están en
**`.env`**, en la raíz del proyecto. Así **no hay contraseñas escritas en el
código**: si cambian tus datos, solo editás ese archivo.

```ini
# Base de datos (la usa lib/bd/gestionBaseDatos.php)
DB_HOST=localhost
DB_NAME=contactos3
DB_USER=root
DB_PASS=
DB_PORT=3306
DB_CHARSET=utf8mb4

# Correo (la usa lib/utils/mail.php)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM=noreply@tuapp.local
MAIL_FROM_NAME="Gestion Personas y Ciudades"
MAIL_DEBUG=true
MAIL_DRY_RUN=true

# Archivos adjuntos (la usa lib/utils/archivos.php)
CARPETA_ARCHIVOS=files
TAMANIO_MAXIMO_MB=3
```

**La primera vez:**

```bash
cp .env.example .env     # copiar la plantilla
```

y después completar con tus datos.

> `MAIL_DRY_RUN=true` **no manda el mail**: solo lo anota en
> `lib/utils/mail.log`. Perfecto para probar sin configurar correo.

### Cómo lee el `.env` el programa

Todo pasa por dos funciones de `lib/utils/varios.php`:

```php
cargarEnv(__DIR__ . '/../../.env');            // lee el archivo y llena $_ENV
$host = valorEntorno('DB_HOST', 'localhost');   // toma el valor (o el de defecto)
```

`obtenerConexion()` (en `lib/bd/gestionBaseDatos.php`) es la **única** función
que arma la conexión PDO. Todas las demás reciben esa conexión ya hecha:

```php
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion, '');
```

---

## 4. La base de datos: dos tablas relacionadas

> La base tiene **dos tablas**. La [sección 8](#8-agregar-un-módulo-nuevo-el-caso-de-los-pa%C3%ADses)
> agrega una tercera (`pais`) como ejercicio, para ver cómo se agrega una
> relación nueva sin romper lo que ya funciona.

### Tabla `ciudades`

```sql
CREATE TABLE ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    latitud DECIMAL(9,6),
    longitud DECIMAL(10,6),
    codigo_postal VARCHAR(10),   -- el código postal es de la CIUDAD, no de la persona
    descripcion TEXT,
    fecha_fundacion DATE
);
```

### Tabla `personas`

```sql
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    ciudad_id INT,                     -- clave foránea a ciudades(id)
    observaciones TEXT,
    avatar_path VARCHAR(255),          -- ruta de la foto
    cv_path VARCHAR(255),              -- ruta del currículum
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
```

**La idea (normalización):** cada ciudad se escribe **una sola vez**. Las
personas no guardan el nombre de la ciudad como texto, guardan su **número**
(`ciudad_id`). Por eso no pueden aparecer "Rio Cuarto", "río cuarto" y
"RIO CUARTO" como si fueran tres ciudades distintas.

Para mostrar el nombre de la ciudad se usa un **JOIN**:

```sql
SELECT p.*, c.nombre AS ciudad_nombre
FROM personas p
LEFT JOIN ciudades c ON p.ciudad_id = c.id;
```

Y para contar cuántas personas nacieron en cada ciudad, un `COUNT` con
`GROUP BY` (es la consulta que usa la columna "Personas" de `viewCiudad.php`):

```sql
SELECT c.id, COUNT(p.id) AS cantidad
FROM ciudades c
LEFT JOIN personas p ON p.ciudad_id = c.id
GROUP BY c.id;
```

### Scripts SQL

| Archivo | Qué hace |
|---|---|
| `lib/bd/script.sql` | Crea la base `contactos3` y las dos tablas (con la clave foránea y las columnas de archivos) |
| `lib/bd/actualizar_base.sql` | Para bases que **ya existían**: agrega las columnas nuevas sin borrar nada |
| `lib/bd/script_inserts.sql` | Inserta 20 ciudades y 50 personas de ejemplo |
| `lib/bd/script_consultas.sql` | Consultas de ejemplo para practicar: simples, `LIKE`, `JOIN`, `GROUP BY`, agregadas |
| `lib/bd/script_update.sql` | Ejemplos de `UPDATE` |
| `lib/bd/script_delete.sql` | Ejemplos de `DELETE` (incluye el caso de la clave foránea) |

> **Si ya tenías la base de la Etapa 3** y no querés borrarla, corré
> `lib/bd/actualizar_base.sql`: te agrega las columnas `avatar_path` y `cv_path`
> sin tocar el resto de los datos.
>
> ```bash
> mysql -u root -p contactos3 < lib/bd/actualizar_base.sql
> ```
>
> Si te salen avisos `Undefined array key "avatar_path"` en el log de Apache,
> es que falta correr ese archivo.

---

## 5. Librerías separadas por módulo

```
lib/bd/gestionBaseDatos.php  ->  conexión PDO + carga del .env
lib/bd/personas-bd.php       ->  INSERT / SELECT / UPDATE / DELETE de personas
lib/bd/ciudades-bd.php       ->  INSERT / SELECT / UPDATE / DELETE de ciudades
lib/bd/paises-bd.php          ->  (ejercicio) lo mismo para países
```

`gestionBaseDatos.php` hace `require_once` de cada módulo al final, así que
las páginas solo necesitan incluir **un** archivo:

```php
require_once '../../lib/bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion, 'Jul');
```

**¿Por qué separarlo?** Para que cada archivo haga una sola cosa: si mañana
cambia la tabla `personas`, se toca **un solo archivo** y ningún formulario
se entera.

---

## 6. Detalles de las funcionalidades nuevas

### 6.1 Elegir la ciudad de nacimiento: `<select>` o buscador

En `editPersona.php` el componente depende de cuántas ciudades haya:

| Cantidad de ciudades | Componente que se usa |
|---|---|
| Hasta **50** | Un `<select>` normal con todas las ciudades |
| Más de **50** | Un `<input type="text">` **+ `<datalist>`** con búsqueda |

En el segundo caso:

1. El usuario escribe en el input (a partir de **3 letras** se busca).
2. JavaScript (`fetch`) le pide las ciudades a `src/ciudad/buscarCiudad.php`,
   que responde en JSON.
3. Las sugerencias se cargan en el `<datalist>` (las muestra el navegador).
4. Al elegir una, se completa el campo oculto `ciudad_id` con el **ID**.

Un `<datalist>` es un `<select>` que el navegador muestra como sugerencias
automáticas mientras se escribe: es HTML puro, no hace falta ninguna librería
extra.

En **los dos casos** lo que se guarda en la base es siempre el **ID**
(`personas.ciudad_id`), nunca el texto escrito.

El límite de 50 está en una variable al principio del formulario, por si
alguien quiere cambiarlo:

```php
$limiteParaLista = 50;
$usarBuscador = count($ciudades) > $limiteParaLista;
```

### 6.2 Archivos adjuntos (foto y currículum)

* En el formulario: `<input type="file" name="avatar">` y `name="cv"`.
* El `<form>` necesita `enctype="multipart/form-data"` (si no, no viaja nada).
* Los archivos se guardan con `move_uploaded_file()` en:
  * `files/avatars/` → la foto
  * `files/cv/` → el currículum
* En la base de datos se guarda **solo la ruta** (`avatar_path`, `cv_path`).
* Al editar, si no se sube un archivo nuevo, se conserva el anterior (viaja
  en los campos ocultos `avatar_actual` y `cv_actual`).
* Al eliminar una persona, también se borran sus archivos del servidor.

Todo eso lo hace `lib/utils/archivos.php`, que además controla el tamaño
máximo y los tipos de archivo permitidos.

**El error más común: "No se pudo guardar el archivo en el servidor".**
No es un error del código: es que **el servidor web no tiene permiso de
escritura** en la carpeta `files/`. En Linux, Apache corre como el usuario
`www-data`, y si la carpeta es del usuario de la terminal, `move_uploaded_file()`
falla con `Permission denied`. La solución está en la [sección 7.1](#71-con-apache-en-ubuntu).

Otro límite que confunde: el de PHP, no el del `.env`.

| Límite | Dónde se define | Default en Ubuntu |
|---|---|---|
| `TAMANIO_MAXIMO_MB` | `.env` (lo chequea nuestro código) | 3 |
| `upload_max_filesize` | `php.ini` (lo chequea PHP antes) | **2M** |
| `post_max_size` | `php.ini` (tamaño total del formulario) | 8M |

Si el cartel dice *"código de error: 1"*, el archivo pesaba más que
`upload_max_filesize` y PHP ni siquiera lo dejó llegar al código. Con fotos de
celular (3 a 8 MB) esto pasa siempre hasta que se cambia el `php.ini`.

### 6.3 Enviar un mail a una persona

* En `viewPersona.php` cada fila tiene un botón **"Enviar mail"**.
* Ese botón abre un **modal de Bootstrap** (ventana flotante) con los campos:
  destinatario, asunto y mensaje. Los datos de la persona se copian solos
  desde los atributos `data-*` del botón.
* El modal manda los datos a `src/persona/enviarMail.php`, que busca la
  persona en la base, llama a `enviarMailAPersona()` y redirige al listado con
  un cartel de éxito o de error.

**Ver el log de los mails.** Con `MAIL_DRY_RUN=true` el mail **no se envía**:
solo se anota lo que se habría enviado en el archivo `MAIL_LOG`
(por defecto `lib/utils/mail.log`). Para verlo en vivo:

```bash
tail -f lib/utils/mail.log
```

```
2026-09-30 10:15:22 - [SIMULADO] Para: juan@mail.com | Asunto: Consulta | Desde: noreply@tuapp.local
2026-09-30 10:15:22 - [SIMULADO] Mensaje: Estimado/a Juan Perez: Buenos días, queríamos consultarte...
```

Con `MAIL_DEBUG=true` también se anota **toda la conversación con el servidor
de correo**, que es lo primero que hay que mirar cuando un mail no sale:

```
2026-09-30 19:46:53 - [SMTP <<] 220 smtp.gmail.com ESMTP gsmtp
2026-09-30 19:46:53 - [SMTP >>] EHLO localhost
2026-09-30 19:46:53 - [SMTP <<] 250-smtp.gmail.com at your service
2026-09-30 19:46:53 - [SMTP >>] STARTTLS
2026-09-30 19:46:53 - [SMTP <<] 220 2.0.0 Ready to start TLS
2026-09-30 19:46:53 - [SMTP >>] AUTH LOGIN
2026-09-30 19:46:53 - [SMTP <<] 235 2.7.0 Accepted
2026-09-30 19:46:53 - [SMTP <<] 250 2.0.0 OK  ... - gsmtp
2026-09-30 19:46:53 - [ENVIADO] Para: juan@mail.com | Asunto: Consulta
```

Si el archivo queda vacío o el cartel dice que no se pudo escribir el log, es
el mismo problema de permisos que con los adjuntos (ver 7.1). Para mandar
mail de verdad poné `MAIL_DRY_RUN=false` y configurá el resto (abajo).

#### Por qué el proyecto no usa `mail()`

La función `mail()` de PHP **no habla SMTP**: le pasa el mail a un programa
del sistema llamado `sendmail` (o postfix) que en muchos servidores —Ubuntu
sin instalar postfix, contenedores, Windows— **no existe**. Cuando eso pasa,
los datos del `.env` están impecable pero el mail no sale, y en el log de
Apache aparece:

```
Warning: mail(): sh: 1: /usr/sbin/sendmail: not found
```

`lib/utils/mail.php` por eso se conecta **directo al servidor de correo** con
`fsockopen()` y habla el protocolo SMTP a mano (`EHLO`, `STARTTLS`,
`AUTH LOGIN`, `MAIL FROM`, `RCPT TO`, `DATA`). No hay que instalar nada en el
servidor: alcanza con los datos del `.env`.

#### Configuración para Gmail

| En el `.env` | Valor |
|---|---|
| `MAIL_HOST` | `smtp.gmail.com` |
| `MAIL_PORT` | `587` (con `tls`) o `465` (con `ssl`) |
| `MAIL_ENCRYPTION` | `tls` con puerto 587, `ssl` con puerto 465 |
| `MAIL_USERNAME` | la cuenta, por ejemplo `juan@gmail.com` |
| `MAIL_PASSWORD` | una **contraseña de aplicación** (ver abajo) |
| `MAIL_FROM` | la misma cuenta que `MAIL_USERNAME` (Gmail no permite Other) |
| `MAIL_DRY_RUN` | `false` para mandar de verdad |

En Gmail **no va la contraseña normal** de la cuenta. Hay que:

1. Activar la **verificación en 2 pasos** en la cuenta de Google.
2. Entrar en <https://myaccount.google.com/apppasswords> y generar una
   contraseña de aplicación (16 letras).
3. Copiar esa contraseña en `MAIL_PASSWORD`.

Si el login falla, el log muestra `535` y la función devuelve un mensaje que
dice justamente eso. Mail Pop/IMAP bloqueado, cuenta de prueba o sin
disponibilidad de Internet en la facultad también dan el mismo `535`.

### 6.4 Columna "Personas" en `viewCiudad.php`

Muestra cuántas personas nacieron en cada ciudad, usando
`contarPersonasPorCiudad()` de `lib/bd/ciudades-bd.php`.

---

## 7. Cómo ponerlo en marcha

```bash
# 1. Crear la base de datos y las tablas
mysql -u root -p < lib/bd/script.sql

# 2. Cargar datos de ejemplo (opcional)
mysql -u root -p contactos3 < lib/bd/script_inserts.sql

# 3. Crear el archivo de configuración
cp .env.example .env
#    y completar con tus datos

# 4. Levantar el servidor PHP desde la carpeta del proyecto
cd templateInicial
php -S localhost:8000

# 5. Abrir en el navegador
#    http://localhost:8000/index.php                 -> Portada
#    http://localhost:8000/src/index.php             -> Inicio (tablero)
#    http://localhost:8000/src/persona/viewPersona.php -> Personas
#    http://localhost:8000/src/ciudad/viewCiudad.php   -> Ciudades
```

Si aparece *"Error de conexion"*, casi siempre es que el `.env` no coincide con
tu instalación de MySQL, o que todavía no se ejecutó `lib/bd/script.sql`.

### 7.1 Con Apache en Ubuntu

Con `php -S` no hay problema de permisos, porque el servidor corre con tu
usuario. **Con Apache sí**: Apache corre como el usuario del sistema
`www-data`, y por eso las carpetas donde el programa tiene que escribir tienen
que pertenecer a ese usuario.

#### a) Permisos de escritura (obligatorio)

El programa escribe en dos lugares: la carpeta `files/` (fotos y CV) y el
archivo de log `lib/utils/mail.log`.

```bash
# Desde la carpeta donde copiaste el proyecto (ej: /var/www/html/2026/templateInicial)
sudo chown -R www-data:www-data files
sudo chown www-data:www-data lib/utils/mail.log
```

> Si volvés a copiar el proyecto con `rsync` (o lo descargás de nuevo), los
> archivos quedan con tu usuario otra vez: hay que **repetir el `chown`**.
> Alternativa que no hay que repetir, usando permisos ACL:
> ```bash
> sudo setfacl -R -m u:www-data:rwx files
> sudo setfacl -m u:www-data:rw lib/utils/mail.log
> ```

<details>
<summary><strong>Si trabajás con el proyecto en el repositorio y lo copiás al sitio</strong> (opcional)</summary>

Un mismo proyecto se puede guardar en un repositorio (por ejemplo en GitHub) y
después copiarse a la carpeta que sirve Apache. Para pasar los cambios sin pisar
tu `.env` (que tiene las contraseñas) ni los archivos que subió la gente:

```bash
rsync -av --no-g \
  --exclude '.env' --exclude 'lib/utils/mail.log' --exclude 'files/' \
  /ruta/del/repo/templateInicial/ \
  /var/www/html/2026/templateInicial/
```

Tres exclusiones, tres motivos:

| Exclusión | Motivo |
|---|---|
| `.env` | tiene tu contraseña de la base y del correo |
| `lib/utils/mail.log` | si `rsync` lo reemplaza, vuelve a ser tuyo y perdés el `chown` de `www-data` |
| `files/` | son las fotos y los CV que subieron los usuarios: nunca se tocan |

Por eso el comando **no lleva `--delete`**: los archivos de `files/` son del
usuario `www-data` y un `rsync` que corre como tu usuario no puede borrarlos.
Si alguna vez borraste un archivo del proyecto, borralo a mano en el sitio.

</details>

#### b) Límites de tamaño de PHP

En Ubuntu el límite por archivo viene en **2 MB**, muy por debajo de una foto
de celular:

```bash
sudo nano /etc/php/8.3/apache2/php.ini
```
```ini
; en [php.ini]
file_uploads = On
upload_max_filesize = 8M
post_max_size = 16M
max_file_uploads = 20
```
```bash
sudo systemctl restart apache2
```

#### c) Base de datos

```bash
# Si la base no existe todavía, creala desde cero
mysql -u root -p < lib/bd/script.sql
mysql -u root -p contactos3 < lib/bd/script_inserts.sql

# Si la base ya existía (por ejemplo la de la Etapa 3), solo actualizala
mysql -u root -p contactos3 < lib/bd/actualizar_base.sql
```

#### d) ¿Dónde quedaron los logs de error?

Con Apache, los warnings de PHP **no se ven en la página** (porque
`display_errors = Off`): se guardan en el log del servidor.

```bash
sudo tail -f /var/log/apache2/error.log
```

Ahí es donde aparece, por ejemplo, `move_uploaded_file(): Permission denied`,
o `Undefined array key "avatar_path"` si falta correr la actualización de la base.

---

## 8. Agregar un módulo nuevo: el caso de los **países**

En `src/template/` hay tres archivos modelo (`viewModulo.php`, `editModulo.php`,
`gestionModulo.php`) para copiar cuando se agrega un módulo, más
`avisoPlantilla.php`, que muestra una guía paso a paso si se abre una plantilla
sin terminar (en vez del error `Call to undefined function` de PHP).

Esta sección usa como ejemplo el módulo **países**, que además trae algo que
los demás no tienen: una **relación con otro módulo**. No alcanza con copiar
archivos, hay que tocar el módulo ciudades entero.

El resultado quedaría así:

```
pais (id, nombre, codigo_iso, capital, poblacion, descripcion)
  |
  |  1 pais -> N ciudades      ciudades.pais_id  (clave foránea)
  v
ciudades (id, nombre, provincia, pais_id, ...)
  |
  |  1 ciudad -> N personas     personas.ciudad_id (clave foránea)
  v
personas (id, nombre, ..., ciudad_id)
```

### Antes de empezar

Una decisión de diseño que cambia todo lo demás: **¿una ciudad puede estar en
varios países?** No (belongs to: N ciudades son de 1 país). Por eso
`pais_id` va **dentro de** la tabla `ciudades`, y no al revés. Si se llegara a
pedir que una ciudad tenga varios países, eso ya sería una relación N:N, que en
SQL se resuelve con una tabla puente.

### Paso 1. La tabla `pais` y la relación

```sql
CREATE TABLE pais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo_iso CHAR(3) NOT NULL,   -- ISO 3166-1: ARG, BRA, ESP, MEX...
    capital VARCHAR(100),
    poblacion BIGINT,
    descripcion TEXT,
    UNIQUE (codigo_iso)            -- no puede repetirse el código
);

-- La ciudad ahora sabe a qué país pertenece
ALTER TABLE ciudades ADD COLUMN pais_id INT NULL AFTER provincia;
ALTER TABLE ciudades
    ADD CONSTRAINT fk_ciudades_pais
    FOREIGN KEY (pais_id) REFERENCES pais(id);
```

Dos detalles que importan:

- **`NULL` y no `NOT NULL`**: si ya tenías ciudades cargadas, una columna
  obligatoria te obliga a asignar un país a cada una antes de poder crear la
  tabla. Con `NULL` primero creás la columna y después completás:
  ```sql
  UPDATE ciudades SET pais_id = 1 WHERE provincia = 'Córdoba';
  ```
- **`UNIQUE (codigo_iso)`**: el código ISO identifica a un país en el mundo,
  así que no puede repetirse. Es una restricción de la base, no del formulario.

### Paso 2. El modelo: `lib/bd/paises-bd.php`

Es el archivo donde vive todo el SQL de países. Lo más rápido es copiar
`lib/bd/ciudades-bd.php` y renombrar las funciones:

```php
function insertarPais($conexion, $datos) {              // INSERT
function obtenerTodosLosPaises($conexion, $busqueda) {  // SELECT con filtro
function obtenerPaisPorId($conexion, $id) {              // SELECT por id
function contarPaises($conexion) {                       // COUNT(*) para el inicio
function contarCiudadesPorPais($conexion) {              // GROUP BY
function hayCiudadesEnPais($conexion, $id) {             // ¿tiene ciudades?
function actualizarPais($conexion, $id, $datos) {        // UPDATE
function eliminarPais($conexion, $id) {                  // DELETE
```

Las dos últimas deserve un comentario, porque son las que salvan los datos:

```php
/**
 * Cuenta cuántas ciudades hay en cada país.
 * Devuelve un array "id de país" => "cantidad de ciudades".
 * Se usa en viewPais.php para la columna "Ciudades".
 *
 * LEFT JOIN: también suma 0 a los países que no tienen ninguna ciudad
 * (si solo contáramos las ciudades, esos países no aparecerían).
 */
function contarCiudadesPorPais($conexion) {
    $sql = "SELECT pa.id, COUNT(c.id) AS cantidad
            FROM pais pa
            LEFT JOIN ciudades c ON c.pais_id = pa.id
            GROUP BY pa.id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $conteos = [];
    foreach ($stmt->fetchAll() as $fila) {
        $conteos[$fila['id']] = (int) $fila['cantidad'];
    }
    return $conteos;
}

/**
 * Indica si un país tiene ciudades asociadas.
 * Se consulta ANTES de borrar: la clave foránea no permite
 * borrar un país que todavía tenga ciudades.
 */
function hayCiudadesEnPais($conexion, $id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM ciudades WHERE pais_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}
```

### Paso 3. Que el modelo esté cargado en todas las páginas

Una sola línea en `lib/bd/gestionBaseDatos.php`, al lado de las otras dos. Por
esto ninguna página tiene que incluir `paises-bd.php` a mano:

```php
require_once __DIR__ . '/personas-bd.php';
require_once __DIR__ . '/ciudades-bd.php';
require_once __DIR__ . '/paises-bd.php';   // <- la nueva
```

### Paso 4. Las páginas del módulo

```bash
cp -r src/template src/pais
```

```
src/template/viewModulo.php     ->  src/pais/viewPais.php
src/template/editModulo.php     ->  src/pais/editPais.php
src/template/gestionModulo.php  ->  src/pais/gestionPais.php
```

Después cambiá los nombres de las funciones y las palabras `MODULO` / `modulo`
por `PAIS` / `pais`:

| En la plantilla | En el módulo países |
|---|---|
| `obtenerTodosLosModulos()` | `obtenerTodosLosPaises()` |
| `obtenerModuloPorId()` | `obtenerPaisPorId()` |
| `crearModulo()` | `insertarPais()` |
| `actualizarModulo()` | `actualizarPais()` |
| `eliminarModulo()` | `eliminarPais()` |

Y dos cosas más que hacen que el módulo se parezca a los otros:

- En **`viewPais.php`**, una columna con `contarCiudadesPorPais()`, con la
  misma lógica de `?:` que usa `viewCiudad.php` con las personas.
- En **`gestionPais.php`**, el borrado tiene que consultar
  `hayCiudadesEnPais()` antes de eliminar y mandar el error por la URL, igual
  que hace `gestionCiudad.php` con `hayPersonasEnCiudad()`.

Ahora sí: si abrís `src/pais/viewPais.php` y ya escribiste las funciones, esta
página andará. Si todavía falta alguna, `avisoPlantilla.php` te muestra esta
misma guía en lugar de un error 500.

### Paso 5. La relación: adaptar el módulo ciudades

Esta es la parte que se olvida. Si creaste la columna `pais_id` pero no tocás
ciudades, nadie puede elegir un país al cargar una ciudad.

**a) Guardar el `pais_id`** en `lib/bd/ciudades-bd.php`. En
`insertarCiudad()` y `actualizarCiudad()`, sumar la columna a la lista:

```php
$sql = "INSERT INTO ciudades
            (nombre, provincia, pais_id, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt->execute([
    $datos['nombre'],
    $datos['provincia'],
    $datos['pais_id'],   // el nuevo
    // ... los demás
]);
```

**b) El formulario** en `editCiudad.php`. Un `<select>` armado con
`obtenerTodosLosPaises()`, precargado en modo edición:

```php
$paises = obtenerTodosLosPaises($conexion);
```

```html
<label for="pais_id" class="form-label">País</label>
<select class="form-select" id="pais_id" name="pais_id" required>
    <option value="">-- Seleccionar país --</option>
    <?php foreach ($paises as $pais): ?>
        <option value="<?php echo $pais['id']; ?>"
            <?php echo ($esEdicion && $ciudad['pais_id'] == $pais['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($pais['nombre']); ?>
            (<?php echo htmlspecialchars($pais['codigo_iso']); ?>)
        </option>
    <?php endforeach; ?>
</select>
```

**c) El presentador** en `gestionCiudad.php`: sumar el campo al array `$datos`
y validarlo, como ya se hace con la latitud:

```php
$paisId = (isset($_POST['pais_id']) && $_POST['pais_id'] !== '') ? (int) $_POST['pais_id'] : null;

if ($paisId === null) {
    $error = 'Tenés que elegir el país de la ciudad';
}

$datos = [
    'nombre'    => trim($_POST['nombre']),
    'provincia' => trim($_POST['provincia']),
    'pais_id'   => $paisId,   // el nuevo
    // ...
];
```

**d) El listado** en `viewCiudad.php`. Para mostrar la columna " País", el
`SELECT` tiene que traer el nombre del país con un `LEFT JOIN`:

```php
function obtenerTodasLasCiudades($conexion, $busqueda = '') {
    $sql = "SELECT c.*, pa.nombre AS pais_nombre
            FROM ciudades c
            LEFT JOIN pais pa ON c.pais_id = pa.id";

    if ($busqueda !== '') {
        $sql .= " WHERE c.nombre LIKE ?";
    }
    $sql .= " ORDER BY c.nombre ASC";
    // ...
}
```

Con eso, en la tabla de `viewCiudad.php` alcanza con agregar dos cosas: un
`<th>País</th>` y su celda:

```php
<td><?php echo htmlspecialchars($ciudad['pais_nombre'] ?? 'Sin país'); ?></td>
```

`LEFT JOIN` y no `JOIN` a propósito: si una ciudad quedó sin `pais_id` (porque
la tabla se creó sobre datos viejos), igual tiene que aparecer en el listado.

**e) El buscador** de `editPersona.php`. `obtenerCiudadesPorNombre()` también
necesita el `JOIN`, así el JSON trae `pais_nombre` y la sugerencia se ve más
completa:

```php
$sql = "SELECT c.id, c.nombre, c.provincia, pa.nombre AS pais_nombre
        FROM ciudades c
        LEFT JOIN pais pa ON c.pais_id = pa.id
        WHERE c.nombre LIKE ?
        ORDER BY c.nombre ASC
        LIMIT $limite";
```

Y en el `<script>` de `editPersona.php`, sumar el país a la etiqueta:

```javascript
const etiqueta = ciudad.nombre + ' (' + ciudad.provincia + ', ' + ciudad.pais_nombre + ')';
```

### Paso 6. La entrada desde el inicio

Un módulo que no se puede llegar desde el menú no existe para el usuario. En
`src/index.php`:

```php
$cantidadPaises = contarPaises($conexion);
```

```html
<div class="col-md-4">
    <div class="card border-warning h-100">
        <div class="card-body">
            <h1 class="display-4 text-warning mb-0"><?php echo $cantidadPaises; ?></h1>
            <p class="text-muted mb-0">Países registrados</p>
        </div>
        <div class="card-footer bg-white">
            <a href="pais/viewPais.php" class="btn btn-warning w-100">Gestionar países</a>
        </div>
    </div>
</div>
```

Y el mismo aviso que ya está para las ciudades, adaptado:

```php
<?php if ($cantidadPaises === 0): ?>
    <div class="alert alert-warning mt-3 text-center mb-0">
        No hay países cargados. Primero creá un
        <a href="pais/editPais.php" class="alert-link">país</a>
        para poder asignarlo a las ciudades.
    </div>
<?php endif; ?>
```

### Paso 7. Probar que la relación quedó consistente

| Prueba | Cómo se hace | Qué tiene que pasar |
|---|---|---|
| Crear un país | `editPais.php` sin `?id=` | Aparece en el listado y en el `SELECT` de ciudades |
| Crear una ciudad con país | `editCiudad.php` | Guarda el `pais_id` y `viewCiudad.php` muestra el nombre del país |
| Cambiar el nombre de un país | `editPais.php?id=1` | Cambia en **todas** las ciudades de ese país (dato escrito una sola vez) |
| Borrar un país con ciudades | Botón Eliminar | No borra: avisa que tiene ciudades asociadas |
| Borrar un país sin ciudades | Botón Eliminar | Borra y vuelve al listado |
| No elegir país en una ciudad | Enviar el formulario vacío | Vuelve al formulario con el error de validación |

La cuarta prueba es la que verifica la clave foránea: `gestionPais.php` tiene
que preguntar con `hayCiudadesEnPais()` **antes** de llamar a `eliminarPais()`.
Si esa consulta falta, MySQL lanza el error 1451 y el usuario ve una pantalla
blanca en vez de un mensaje.

### Lo que NO hay que tocar

`editPersona.php` y `viewPersona.php` no necesitan cambios para guardar el
país: la persona sigue teniendo únicamente `ciudad_id`, y el país se hereda
siguiendo la relación (persona → ciudad → país). Ese es el punto de normalizar
los datos: **el país está escrito una sola vez**, en la tabla `pais`.

Si en algún momento querés mostrarlo en el listado de personas, alcanza con
encadenar un JOIN más en `obtenerTodasLasPersonas()`:

```sql
SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia,
       pa.nombre AS pais_nombre
FROM personas p
LEFT JOIN ciudades c  ON p.ciudad_id  = c.id
LEFT JOIN pais pa      ON c.pais_id   = pa.id
```

### Cómo queda el proyecto al terminar

| Pieza | Archivos |
|---|---|
| Modelo (datos) | `lib/bd/personas-bd.php`, `lib/bd/ciudades-bd.php`, `lib/bd/paises-bd.php` |
| Vista (HTML) | `viewPersona.php`, `editPersona.php`, `viewCiudad.php`, `editCiudad.php`, `viewPais.php`, `editPais.php` |
| Presentador (recibe y redirige) | `gestionPersona.php`, `enviarMail.php`, `gestionCiudad.php`, `gestionPais.php` |

---

## 9. Ideas para seguir

* **Terminar el ejercicio de países** ([sección 8](#8-agregar-un-módulo-nuevo-el-caso-de-los-pa%C3%ADses)):
  escribir `lib/bd/paises-bd.php` y crear `src/pais/`.
* **Paginación:** mostrar la tabla de a 10 filas (`LIMIT 10 OFFSET ?`).
* **Ordenar por columna:** click en el nombre de una columna para cambiar
  entre `ASC` y `DESC`.
* **Validaciones en PHP:** avisar si el DNI ya existe antes de insertar.
* **Borrado de archivos al reemplazar:** cuando se sube una foto nueva,
  eliminar la anterior del servidor.
* **Exportar a PDF o Excel:** descargar el listado de personas.
* **Usuarios y roles:** agregar login y permisos (como en el proyecto
  `etapaModeloGestionUsuarios`).
