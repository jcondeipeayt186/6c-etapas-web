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
│   │   ├── script_consultas.sql  # Ejemplos de SELECT
│   │   └── script_delete.sql     # Ejemplos de DELETE
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
        ├── viewModulo.php
        ├── editModulo.php
        ├── gestionModulo.php
        └── readme.md         # Cómo usarlas (y su relación con el patrón MVP)
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
| `lib/bd/script_inserts.sql` | Inserta 20 ciudades y 50 personas de ejemplo |
| `lib/bd/script_consultas.sql` | Consultas de ejemplo para practicar: simples, `LIKE`, `JOIN`, `GROUP BY`, agregadas |
| `lib/bd/script_update.sql` | Ejemplos de `UPDATE` |
| `lib/bd/script_delete.sql` | Ejemplos de `DELETE` (incluye el caso de la clave foránea) |

> **Si ya tenías la base de la Etapa 3** y no querés borrarla, con estas dos
> sentencias le agregás las columnas nuevas:
> ```sql
> ALTER TABLE personas ADD COLUMN avatar_path VARCHAR(255);
> ALTER TABLE personas ADD COLUMN cv_path VARCHAR(255);
> ```

---

## 5. Librerías separadas por módulo

```
lib/bd/gestionBaseDatos.php  ->  conexión PDO + carga del .env
lib/bd/personas-bd.php       ->  INSERT / SELECT / UPDATE / DELETE de personas
lib/bd/ciudades-bd.php       ->  INSERT / SELECT / UPDATE / DELETE de ciudades
```

`gestionBaseDatos.php` hace `require_once` de los dos módulos al final, así que
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

### 6.3 Enviar un mail a una persona

* En `viewPersona.php` cada fila tiene un botón **"Enviar mail"**.
* Ese botón abre un **modal de Bootstrap** (ventana flotante) con los campos:
  destinatario, asunto y mensaje. Los datos de la persona se copian solos
  desde los atributos `data-*` del botón.
* El modal manda los datos a `src/persona/enviarMail.php`, que busca la
  persona en la base, llama a `enviarMailAPersona()` y redirige al listado con
  un cartel de éxito o de error.

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

---

## 8. Crear un módulo nuevo: `src/template/`

En `src/template/` hay tres archivos modelo (`viewModulo.php`, `editModulo.php`,
`gestionModulo.php`) para copiar cuando se agrega un módulo. El `readme.md` de
esa carpeta explica el paso a paso y cómo se relaciona con el **patrón MVP**
(Modelo – Vista – Presentador).

| Pieza | Archivos en este proyecto |
|---|---|
| Modelo (datos) | `lib/bd/personas-bd.php`, `lib/bd/ciudades-bd.php` |
| Vista (HTML) | `viewPersona.php`, `editPersona.php` |
| Presentador (recibe y redirige) | `gestionPersona.php`, `enviarMail.php` |

---

## 9. Ideas para seguir

* **Paginación:** mostrar la tabla de a 10 filas (`LIMIT 10 OFFSET ?`).
* **Ordenar por columna:** click en el nombre de una columna para cambiar
  entre `ASC` y `DESC`.
* **Validaciones en PHP:** avisar si el DNI ya existe antes de insertar.
* **Borrado de archivos al reemplazar:** cuando se sube una foto nueva,
  eliminar la anterior del servidor.
* **Exportar a PDF o Excel:** descargar el listado de personas.
* **Usuarios y roles:** agregar login y permisos (como en el proyecto
  `etapaModeloGestionUsuarios`).
