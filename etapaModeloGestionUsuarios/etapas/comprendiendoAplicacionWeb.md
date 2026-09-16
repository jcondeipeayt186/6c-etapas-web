# Comprendiendo la Aplicación Web: de `etapa2-BD-Filtro` a `etapa3-dosTablas`

> **Objetivo de este documento:** explicar qué contiene cada archivo, para qué sirve, cómo se relacionan, y cómo agregar algo nuevo respetando las convenciones del proyecto: **HTML reutilizable vía `lib/`**, **acceso a datos vía `lib/bd/gestionBaseDatos.php`**, y la **distinción frontend (navegable) vs backend (no navegable)** y **cuándo usar GET vs POST / formulario vs procesamiento**.

---

## 1. Idea general de la aplicación

El proyecto es un **ABM de Personas y Ciudades** con PHP + MySQL + Bootstrap 5.

- **Etapa 2** resolvía un solo problema: **guardar personas** (una tabla `personas`) y **filtrarlas por nombre** (`LIKE '%...%'`).
- **Etapa 3** resuelve el problema de **normalización**: `ciudad` deja de ser texto libre repetido en cada persona y pasa a ser **entidad propia** (`ciudades`) relacionada por **clave foránea `ciudad_id`**. Todo lo aprendido (filtro, UPDATE) se conserva pero **reorganizado en módulos** (`lib/` + `src/`).

```
Navegador (cliente)  ──POST/GET──>  PHP (servidor)  ──SQL/PDO──>  MySQL
   formulario/tabla              gestion*.php + gestionBaseDatos.php    tablas
        ▲                              │
        └──────── HTML generado ◄───────┘
```

---

## 2. Etapa 2-BD-Filtro: qué hay y para qué sirve cada archivo

> Punto de partida. Todo está en la **raíz** de la etapa, sin subcarpetas de módulos. Es intencionalmente simple.

```
etapa2-BD-Filtro/
├── index.php                # Frontend navegable
├── procesando.php           # Backend NO navegable
├── resultado.php            # Frontend navegable
├── bd/
│   ├── gestionBaseDatos.php # Librería backend (DAO)
│   └── script.sql           # Esquema BD (una tabla)
├── img/mysql.png
└── agregandoFiltroEtapa2-BD.md
```

| Archivo | Tipo | Objetivo | Responsabilidad clave |
|---|---|---|---|
| **`index.php`** | Frontend navegable | **Mostrar el formulario de alta** de personas. `action="procesando.php" method="POST"`. No lee ni escribe en BD. | Capturar entrada del usuario. 12 campos con `name="..."` que luego serán `$_POST['...']`. Bootstrap para layout. Link a `resultado.php`. |
| **`procesando.php`** | Backend NO navegable | **Cerebro detrás de escena**. Sin HTML. Recibe `POST`, decide `INSERT` o `DELETE` y **redirige** con `header("Location: ..."); exit;` | `require_once bd/gestionBaseDatos.php` → `obtenerConexion()` → si `accion=eliminar` → `eliminarPersona()` ; si no → armar `$datos[]` con `trim()` y `insertarPersona()`. Valida `REQUEST_METHOD === POST` y campos obligatorios. |
| **`resultado.php`** | Frontend navegable (híbrido) | **Listar y filtrar** personas. Muestra `table` + buscador + botón Eliminar por fila. | Arriba: `require_once bd/gestionBaseDatos.php; $busqueda = $_GET['busqueda'] ?? ''; $personas = obtenerPersonasConFiltro($conexion, $busqueda);` Abajo: `foreach`, `count()`, `htmlspecialchars()`, mensaje diferenciado si `empty()`. |
| **`bd/gestionBaseDatos.php`** | Librería backend (**DAO** = *Data Access Object*, ver §6.1) | **Único lugar que habla SQL**. DAO con PDO + prepared statements (`?` + `execute([])`). | `obtenerConexion()` (PDO + try/catch + `ERRMODE_EXCEPTION`), `insertarPersona()`, `obtenerTodasLasPersonas()`, `obtenerPersonasConFiltro()` (construye `WHERE p.nombre LIKE ?` dinámico), `eliminarPersona()`. |
| **`bd/script.sql`** | Recurso BD | **Crear `contactos` y `personas`** (`id INT AUTO_INCREMENT PRIMARY KEY`, `VARCHAR`, `DATE`, `TEXT`). | Se ejecuta 1 vez en phpMyAdmin. Define qué columnas existen. |

### Flujo Etapa 2

```
index.php --POST--> procesando.php --INSERT--> MySQL
                          |
                          └--header--> resultado.php --SELECT (con o sin LIKE)--> MySQL --foreach--> <table>
                 resultado.php --POST (accion=eliminar, id)--> procesando.php --DELETE--> MySQL --header--> resultado.php
                 resultado.php --GET (?busqueda=Jul)--> resultado.php (se filtra a sí misma)
```

**Patrón de filtro aprendido aquí (agregandoFiltroEtapa2-BD.md):** 2 cambios mínimos:
1. Backend: función nueva `obtenerPersonasConFiltro($conexion, $busqueda)` con `LIKE '%term%'` opcional.
2. Frontend: `$busqueda = trim($_GET['busqueda'] ?? '')` + `<form method="GET" action="resultado.php">` con `value="<?= htmlspecialchars($busqueda) ?>"` + mensaje `empty()` diferenciado.

---

## 3. Etapa 3-dosTablas: la misma funcionalidad, pero organizada en módulos

> Evolución que **aprovecha que hay más módulos** (ahora son 2 entidades). Aparecen `lib/` y `src/` para escalar sin copiar/pegar código.

```
etapa3-dosTablas/
├── index.php                      # Portada / Dashboard (navegable)
├── img/mysql.png
├── lib/                           # BACKEND COMPARTIDO (librerías NO navegables)
│   ├── bd/
│   │   ├── gestionBaseDatos.php   # DAO para PERSONAS + CIUDADES (JOIN, COUNT, hayPersonasEnCiudad)
│   │   ├── script.sql             # CREATE contactos3 + ciudades + personas (FK)
│   │   ├── script_inserts.sql     # 20 ciudades + 50 personas
│   │   ├── script_update.sql      # Ejemplos UPDATE
│   │   ├── script_consultas.sql   # Progresión SELECT → JOIN → GROUP BY
│   │   └── script_delete.sql      # DELETE + explicación FK
│   └── html/
│       └── funcionesHTML.php      # Vista compartida: piePagina(), mostrarAlerta()
└── src/                           # POR ENTIDAD (frontend navegable + backend no navegable)
    ├── persona/
    │   ├── editPersona.php        # Formulario alta/edición (GET ?id= para precargar)
    │   ├── viewPersona.php        # Listado + filtro LIKE + Modificar/Eliminar
    │   └── gestionPersona.php     # INSERT/UPDATE/DELETE personas (sin HTML, redirige)
    └── ciudad/
        ├── editCiudad.php         # Formulario alta/edición ciudades
        ├── viewCiudad.php         # Listado + filtro + CRUD ciudades
        └── gestionCiudad.php      # INSERT/UPDATE/DELETE ciudades (valida lat/long, verifica FK)
```

### 3.1 Qué cambió respecto a Etapa 2

| Aspecto | Etapa 2 (una tabla) | Etapa 3 (dos tablas normalizadas) |
|---|---|---|
| **BD** | `personas.ciudad VARCHAR(100)` texto libre repetido | `ciudades` (nombre, provincia, latitud, longitud, codigo_postal, ...) + `personas.ciudad_id INT FOREIGN KEY → ciudades(id)` |
| **Dato repetido** | `"Río Cuarto"` 30 veces | `ciudad_id=1` 30 veces (INT más liviano) |
| **Consistencia** | `"Rio Cuarto"` vs `"Río Cuarto"` → 2 ciudades distintas | Solo IDs válidos (la FK lo garantiza) |
| **Formulario persona** | `<input type="text" name="ciudad">` | `<select name="ciudad_id">` poblado con `obtenerTodasLasCiudades()` → `foreach <option value="<?= id ?>">` |
| **Listado personas** | `SELECT * FROM personas` | `SELECT p.*, c.nombre AS ciudad_nombre ... FROM personas p LEFT JOIN ciudades c ON p.ciudad_id=c.id` |
| **Portada** | `index.php` era directamente el formulario | `index.php` es dashboard con `contarPersonas()` + `contarCiudades()` y accesos |
| **Reutilización HTML** | No había | `lib/html/funcionesHTML.php` → `piePagina()` invocado en 5 páginas navegables |
| **Organización** | Archivos sueltos en raíz | `lib/` (compartido) + `src/<entidad>/` (view/edit/gestion por entidad) |

### 3.2 Rol de cada archivo en Etapa 3

#### `index.php` (portada)
- **Navegable.** `require lib/bd/gestionBaseDatos.php + lib/html/funcionesHTML.php` → `$cantidadPersonas = contarPersonas()` / `$cantidadCiudades = contarCiudades()` (`SELECT COUNT(*)`) → dos cards `display-3` con botones `Gestionar personas / Gestionar ciudades` + aviso si `$cantidadCiudades===0` (no se pueden crear personas sin ciudades).

#### `lib/bd/gestionBaseDatos.php` (el DAO del proyecto)
- **No navegable. Corazón del sistema. DAO = Data Access Object** (ver §6.1): el único archivo que contiene SQL. Contiene:
  - **Conexión:** `obtenerConexion()` (PDO, charset utf8mb4, excepciones).
  - **Personas:** `insertarPersona()` (10 `?` con `ciudad_id` FK), `obtenerTodasLasPersonas($busqueda='')` (LEFT JOIN + LIKE opcional), alias `obtenerPersonasConFiltro()` para compatibilidad, `obtenerPersonaPorId()` (`fetch()` una fila), `actualizarPersona()` (`UPDATE ... WHERE id=?`, id al final), `eliminarPersona()`, `contarPersonas()`.
  - **Ciudades:** `insertarCiudad()`, `obtenerTodasLasCiudades($busqueda='')` (`ORDER BY nombre ASC`), `obtenerCiudadPorId()`, `actualizarCiudad()`, `eliminarCiudad()`, `hayPersonasEnCiudad()` (`COUNT(*) WHERE ciudad_id=?` → `>0` bloquea DELETE), `contarCiudades()`.

#### `lib/html/funcionesHTML.php`
- **No navegable pero genera HTML.** `piePagina()` (usa `date('Y')` y clases `bg-dark`) + `mostrarAlerta($texto,$tipo)` con `htmlspecialchars()`. Todas las páginas navegables hacen `require_once '../../lib/html/funcionesHTML.php'` y antes de `</body>` llaman `<?php piePagina(); ?>`. Cambiar el footer en un solo lugar actualiza 5 páginas.

#### `lib/bd/script.sql` (+ inserts/consultas/delete/update)
- Define normalización: `ciudades` con `DECIMAL(9,6)`/`DECIMAL(10,6)` y `personas.ciudad_id INT FOREIGN KEY`. Los scripts adicionales son material didáctico progresivo (no necesarios para que la app funcione pero útiles para practicar SQL puro).

#### `src/persona/editPersona.php` y `src/ciudad/editCiudad.php`
- **Navegables. Formularios duales (alta/edición en un mismo archivo).** Patrón:
  ```php
<?php
  $ciudades = obtenerTodasLasCiudades($conexion); // para el <select> (solo persona)
  $esEdicion = false; $persona = null;
  if (isset($_GET['id'])) { $persona = obtenerPersonaPorId($conexion, (int)$_GET['id']); $esEdicion = $persona ? true : false; }
  ```
  HTML: `<form action="gestionPersona.php" method="POST">` con `<input type="hidden" name="accion" value="<?= $esEdicion ? 'actualizar':'crear' ?>">` + `<input type="hidden" name="id" value="<?= $persona['id'] ?>">` solo en edición. Inputs con `value="<?= $esEdicion ? htmlspecialchars($persona['campo']) : '' ?>"` y `textarea` con contenido entre etiquetas. Botón cambia color/texto (`btn-primary` vs `btn-warning`). Ciudad como `<select>` con `selected` si `persona['ciudad_id']==ciudad['id']`.

#### `src/persona/viewPersona.php` y `src/ciudad/viewCiudad.php`
- **Navegables. Tablas con buscador.** Patrón idéntico a `resultado.php` de Etapa 2 pero ya dentro de su módulo:
  ```php
<?php
  $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
  $personas = obtenerTodasLasPersonas($conexion, $busqueda);
  ```
  + `<form method="GET" action="viewPersona.php">` (se filtra a sí misma) con `value="<?= htmlspecialchars($busqueda) ?>"` y cartel `Quitar filtro`. `if(empty($personas))` diferenciado + `foreach` con `htmlspecialchars()`. Ciudad mostrada vía JOIN (`ciudad_nombre`/`ciudad_provincia` con fallback `—`). Columna Acciones: link `editPersona.php?id=X` (GET) + `<form method="POST" action="gestionPersona.php">` con `accion=eliminar` + `id` hidden + `onsubmit="return confirm(...)"`.

#### `src/persona/gestionPersona.php` y `src/ciudad/gestionCiudad.php`
- **NO navegables. Sin HTML.** Reciben solo `POST` y redirigen. Tres ramas según `$_POST['accion']`:
  - `eliminar`: `(int)$_POST['id']` → verifica FK (solo ciudades) → `eliminarX()` → `header("Location: viewX.php"); exit;`
  - `actualizar`: valida campos obligatorios, arma `$datos` con `trim()` y casteos `(int)`/`null`, llama `actualizarX($conexion,$id,$datos)` → redirige.
  - `crear` (fallback): mismo armado de `$datos` → `insertarX()` → redirige.
  - Si acceso por GET directo → `header("Location: ../../index.php"); exit;`
  - `gestionCiudad.php` añade **validación de latitud/longitud** (`is_numeric` + rangos -90/90 y -180/180) y redirige con `?error=urlencode(...)` si falla, para no provocar error MySQL `Out of range`.

---

## 4. ¿Qué es PDO? Explicado para estudiantes que lo ven por primera vez

> **PDO = PHP Data Objects (Objetos de Datos de PHP).** Es la **librería oficial de PHP para hablar con bases de datos**. No es MySQL, no es SQL: es el **traductor / cable** que usa PHP para conectarse a MySQL (y también a PostgreSQL, SQLite, etc.) de forma segura y ordenada.

### 4.1 Analogía simple

Imaginate que **PHP es vos**, **MySQL es una biblioteca enorme** con ficheros y **PDO es el bibliotecario**:

- Vos no entrás directo al depósito a revolver fichas.
- Le pedís al bibliotecario: *"Traeme la ficha de la persona con id=5"* o *"Guardá esta ficha nueva"*.
- El bibliotecario (PDO) traduce tu pedido a SQL, va al depósito, lo ejecuta y te trae el resultado en un formato que PHP entiende (arrays).

Sin PDO, PHP no sabría cómo "hablar" con MySQL.

### 4.2 ¿Por qué PDO y no otra cosa?

En PHP existen dos formas históricas de conectar a MySQL: `mysqli` (solo MySQL) y `PDO` (universal). En este proyecto usamos **PDO** porque:

| Ventaja de PDO | Qué significa para vos |
|---|---|
| **Funciona con varias BD** | Si mañana cambiás de MySQL a SQLite/PostgreSQL, el código casi no cambia. `mysqli` solo sirve para MySQL. |
| **Prepared statements con `?`** | Te obliga a usar `?` + `execute([])`, que es la defensa contra **inyección SQL** (ver 4.4). |
| **Manejo de errores con excepciones** | Si la BD está apagada o la consulta falla, lanza una excepción que podés atrapar con `try/catch` y mostrar un mensaje claro. |
| **API consistente** | Siempre es `prepare()` → `execute()` → `fetch()` / `fetchAll()` / `fetchColumn()`, no importa qué tabla consultes. |

### 4.3 El único lugar donde aparece PDO: `obtenerConexion()`

Todo el proyecto crea **una sola conexión** y la reutiliza. Mirá el código real de `lib/bd/gestionBaseDatos.php` (idéntico en Etapa 2 y 3, solo cambian usuario/BD):
```php
<?php
function obtenerConexion() {
    $host = "localhost";        // dónde está MySQL (en XAMPP es localhost)
    $usuario = "root";          // usuario de MySQL
    $contrasena = "";           // contraseña (vacía en XAMPP)
    $baseDatos = "contactos3";  // nombre de la BD (ver lib/bd/script.sql)

    try {
        // DSN = Data Source Name: "qué motor, dónde, qué BD y qué charset"
        $conexion = new PDO(
            "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );
        // Que PDO lance excepciones si algo falla (sin esto los errores son silenciosos)
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion; // ¡Este objeto es el "bibliotecario" listo para trabajar!
    } catch (PDOException $e) {
        // Si no pudo conectar (MySQL apagado, contraseña mal, BD no existe)
        die("Error de conexion: " . $e->getMessage());
    }
}
```

Desglose línea por línea:

- `new PDO("mysql:host=...;dbname=...;charset=utf8mb4", ...)` → crea el objeto conexión. El primer string es el **DSN**: `mysql:` (motor), `host=` (servidor), `dbname=` (BD), `charset=utf8mb4` (para que `ñ`, tildes y emojis no se rompan).
- `setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)` → le dice *"si algo sale mal, avisame con una excepción"*. Sin esto, un `INSERT` fallido no avisaría.
- `try { ... } catch (PDOException $e) { ... }` → `try` intenta conectar, `catch` captura el error si falla. `die()` detiene todo y muestra el mensaje. En producción no se mostraría detalle, pero para aprender es útil.
- `return $conexion` → devolvés el objeto. Todas las demás funciones lo reciben como parámetro: `insertarPersona($conexion, $datos)`.

En cada página que necesita BD se hace:

```php
<?php
require_once '../../lib/bd/gestionBaseDatos.php';
$conexion = obtenerConexion(); // una vez por página
$personas = obtenerTodasLasPersonas($conexion, $busqueda);
```

### 4.4 El corazón de PDO: `prepare()` + `execute([])` con `?` (prepared statements)

**Nunca** se arma SQL pegando texto del usuario con `.`. Siempre se usa **placeholder `?`**:

```php
<?php
// ❌ MAL: vulnerable a inyección SQL (el usuario puede mandar `' OR '1'='1` y ver/borrar todo)
$sql = "SELECT * FROM personas WHERE nombre = '" . $_GET['busqueda'] . "'";

// ✅ BIEN: el ? es un hueco que luego se rellena de forma segura
$sql = "SELECT * FROM personas WHERE nombre LIKE ?";
$stmt = $conexion->prepare($sql);      // 1) PDO analiza el SQL, sin datos
$stmt->execute(['%' . $busqueda . '%']); // 2) PDO envía los datos SEPARADOS del SQL
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC); // 3) trae resultados
```

Los 3 pasos siempre:

1. **Escribir SQL con `?`** en lugar de valores: `INSERT INTO personas (nombre, ciudad_id) VALUES (?, ?)` o `UPDATE ciudades SET nombre=? WHERE id=?`.
2. **`$conexion->prepare($sql)`** → PDO prepara/analiza la consulta (pero no la ejecuta). Es como escribir la carta sin poner los nombres.
3. **`$stmt->execute([$valor1, $valor2])`** → PDO reemplaza cada `?` por su valor **en orden**, escapándolo automáticamente. Los datos viajan por un carril y el SQL por otro: **nunca se mezclan**, por eso un atacante no puede inyectar código.

> **¿Qué es inyección SQL?** Si concatenás texto, un usuario puede escribir `'; DROP TABLE personas; --` y convertir tu `SELECT` en un `DROP`. Con `?` ese texto se guarda como dato literal `'; DROP...'` y no se ejecuta.

### 4.5 Cómo trae datos: `fetch()` vs `fetchAll()` vs `fetchColumn()`

Todas las funciones DAO terminan con uno de estos tres:

| Método | Qué trae | Cuándo se usa en el proyecto | Ejemplo |
|---|---|---|---|
| `fetchAll(PDO::FETCH_ASSOC)` | **Todas** las filas → array de arrays asociativos | Listados `viewPersona.php`, `viewCiudad.php` | `return $stmt->fetchAll(PDO::FETCH_ASSOC); // [0=>['id'=>1,'nombre'=>'Juan'], 1=>...]` |
| `fetch(PDO::FETCH_ASSOC)` | **Una** sola fila → array asociativo o `null` | Precargar formulario `editPersona.php?id=5` → `obtenerPersonaPorId()` | `return $stmt->fetch(PDO::FETCH_ASSOC); // ['id'=>5,'nombre'=>'Juan']` |
| `fetchColumn()` | **Un** solo valor (primera columna de la primera fila) | Estadísticas `COUNT(*)` → `contarPersonas()` / `contarCiudades()` | `return (int)$stmt->fetchColumn(); // 42` |

`PDO::FETCH_ASSOC` significa *"devolvé cada fila como array asociativo con claves = nombres de columna"*, por eso luego usás `$persona['nombre']`, `$ciudad['provincia']`, `$persona['ciudad_nombre']` (alias del `JOIN`).

### 4.6 Ciclo de vida de una consulta en el proyecto

```
PHP (viewPersona.php)                    PDO (obtenerConexion)              MySQL
       │                                          │                            │
       │  $conexion = obtenerConexion()           │                            │
       │─────────────────────────────────────────>│──conecta (DSN)──────────>  │
       │                                          │                            │
       │  $stmt = $conexion->prepare("SELECT ... WHERE nombre LIKE ?")         │
       │─────────────────────────────────────────>│──analiza SQL────────────>  │
       │                                          │                            │
       │  $stmt->execute(['%Jul%'])               │                            │
       │─────────────────────────────────────────>│──envía datos separados──>  │──ejecuta seguro
       │                                          │                            │
       │  $filas = $stmt->fetchAll(ASSOC)         │                            │
       │<─────────────────────────────────────────│<──devuelve arrays────────  │
       │  foreach ($filas as $p) echo $p['nombre']│                            │
```

### 4.7 Errores comunes con PDO y cómo evitarlos

- **Olvidar `prepare`/`execute` y concatenar:** `WHERE id = $_GET['id']` → inyección. Siempre `WHERE id = ?` + `execute([(int)$id])`.
- **Desordenar los `?` y el array:** el orden del `execute([...])` debe coincidir exactamente con el orden de los `?` en el SQL. El `WHERE id = ?` suele ser el **último** `?`, por eso `id` va al final en `UPDATE`.
- **No usar `htmlspecialchars()` al mostrar:** PDO te protege al **guardar**, pero al **mostrar** necesitás `htmlspecialchars()` para evitar XSS (`<script>`).
- **Hacer `echo` antes de `header("Location:")`:** PDO no tiene la culpa, pero si imprimiste algo, `header()` falla con *headers already sent*. Los `gestion*.php` no tienen HTML justamente por esto.

---

## 5. Frontend vs Backend: análisis

| Dimensión | Frontend (lo que el usuario ve y toca) | Backend (lo que trabaja detrás) |
|---|---|---|
| **Archivos** | `index.php`, `src/*/edit*.php`, `src/*/view*.php` | `src/*/gestion*.php`, `lib/bd/gestionBaseDatos.php`, `lib/html/funcionesHTML.php`, `lib/bd/*.sql` |
| **¿Navegable?** | **Sí**: el usuario escribe la URL y navega con links/botones. | **No**: si se accede directo por URL hace `header(Location: index.php)` o error. No tienen `<!DOCTYPE>` completo. |
| **¿Tiene HTML?** | Sí, documento completo Bootstrap (`card`, `table`, `form`, `badge`). | No (excepto `funcionesHTML.php` que **retorna** fragmentos HTML, pero no es página). |
| **Responsabilidad** | Mostrar datos, capturar entrada, ofrecer navegación y confirmaciones (`confirm()`). Llama a `lib/` con `require_once`. | Conectarse a MySQL, validar, ejecutar `INSERT/SELECT/UPDATE/DELETE` con prepared statements, y **redirigir**. |
| **Analogía** | Mostrador y mesas del restaurante | Cocina y depósito |

**Regla de oro:** no todo `.php` es una página. `lib/` y `gestion*.php` son "empleados" que el frontend contrata vía `require_once`.

---

## 6. Cómo agregar algo nuevo: guía práctica

### 6.1 Principio rector

> **¿Qué es un "principio rector"?** Es la **regla guía** del proyecto: un acuerdo que dice **dónde va cada cosa** para que todos los que programen lo hagan igual y el código no se desordene. Si lo respetás, cualquier persona que abra el proyecto sabrá dónde buscar y dónde agregar. Si no lo respetás (por ejemplo, ponés SQL suelto en una vista), creás duplicación y errores difíciles de arreglar.

> **Principio rector de este proyecto:**
> **Si vas a generar HTML reutilizable → usá / extendé `lib/html/funcionesHTML.php`.**
> **Si vas a acceder a BD → usá / extendé `lib/bd/gestionBaseDatos.php` (el DAO).**
> **Si vas a crear una página que el usuario navega → ponela en `src/<entidad>/` y que sea de las dos clases: `edit*.php` (formulario) o `view*.php` (listado+filtro).**
> **Si vas a procesar datos de un formulario → creá/extendé `src/<entidad>/gestion*.php` (sin HTML, solo POST + `header(Location:)`).**

#### ¿Qué es DAO?

> **DAO = Data Access Object (Objeto de Acceso a Datos).** Es un **patrón de diseño**: significa *"concentrar TODO el código que habla con la base de datos en un solo archivo/objeto"*. En este proyecto el DAO es **`lib/bd/gestionBaseDatos.php`**. Ventajas:
> - **Un solo lugar para cambiar SQL:** si cambiás una tabla, tocás una función, no 10 archivos.
> - **Reutilización:** cualquier `view` o `gestion` hace `require_once` y llama `obtenerTodasLasCiudades()` sin copiar SQL.
> - **Seguridad centralizada:** todos los `prepare()` + `?` + `execute()` están ahí, no dispersos.
> - **Orden:** el resto del código (vistas y procesadores) no mezcla HTML con SQL.
>
> Sin DAO, cada `viewPersona.php` y `editCiudad.php` tendría su propio `SELECT` copiado y ante un cambio olvidarías uno.

### 6.2 Caso A: Nueva entidad (ej: `materias` o `profesores`)

1. **BD:** crear tabla en `lib/bd/script.sql` (o nuevo `script_materias.sql`) y ejecutarla. Definir PK y FK si corresponde. Añadir inserts en `script_inserts.sql`.
2. **DAO:** en `lib/bd/gestionBaseDatos.php` añadir bloque `/* FUNCIONES DE MATERIAS */`: `insertarMateria()`, `obtenerTodasLasMaterias($busqueda)`, `obtenerMateriaPorId()`, `actualizarMateria()`, `eliminarMateria()`, `contarMaterias()`, y `hayXEnMateria()` si hay FK.
3. **Listado:** crear `src/materia/viewMateria.php` copiando `viewCiudad.php` (cambiar `obtenerTodasLasCiudades` → `obtenerTodasLasMaterias`, `name="busqueda"`, columnas). Incluir `require '../../lib/bd/gestionBaseDatos.php'` + `require '../../lib/html/funcionesHTML.php'` + `piePagina()`.
4. **Formulario:** crear `src/materia/editMateria.php` copiando `editCiudad.php` (lógica `$esEdicion` con `$_GET['id']`, `value="<?= htmlspecialchars(...) ?>"`, hidden `accion` + `id`). Si tiene FK (ej: materia pertenece a profesor), añadir `<select>` poblado con `obtenerTodasLasProfesores()`.
5. **Procesador:** crear `src/materia/gestionMateria.php` copiando `gestionCiudad.php` (tres ramas `eliminar/actualizar/crear`, validar campos, `header(Location: viewMateria.php)`).
6. **Portada:** en `index.php` añadir card con `contarMaterias()` y botones a `src/materia/viewMateria.php` y `src/materia/editMateria.php`.

### 6.3 Caso B: Nuevo campo a una entidad existente

1. Alterar tabla (`ALTER TABLE personas ADD COLUMN legajo VARCHAR(20)`) o recrear script.
2. Actualizar `insertarPersona()` y `actualizarPersona()` en `lib/bd/gestionBaseDatos.php` (añadir `?` en SQL y valor en `execute([])` **en el mismo orden**).
3. Añadir `<input name="legajo">` en `editPersona.php` con `value="<?= $esEdicion ? htmlspecialchars($persona['legajo']) : '' ?>"`.
4. Añadir `'legajo' => trim($_POST['legajo'])` en `gestionPersona.php` en los dos arrays (`crear` y `actualizar`).
5. Añadir `<th>Legajo</th>` y `<td><?= htmlspecialchars($persona['legajo']) ?></td>` en `viewPersona.php`.

### 6.4 Caso C: Nuevo HTML reutilizable

No copiar el footer 5 veces. En `lib/html/funcionesHTML.php`:

```php
<?php
function barraNavegacion($activo = '') {
  echo '<nav class="navbar navbar-expand-lg bg-primary"> ... </nav>';
}
function mostrarAlerta($texto,$tipo='info'){ ... }
```

Y en cada página navegable:

```php
<?php
require_once '../../lib/html/funcionesHTML.php';
barraNavegacion('personas');
piePagina();
```

### 6.5 Caso D: Nueva consulta/filtro u ordenamiento

1. Añadir función en `lib/bd/gestionBaseDatos.php` que use `LIKE`, `ORDER BY`, `GROUP BY` o `JOIN` según necesidad, siempre con `?` + `execute([])`.
2. En `view*.php` leer parámetro GET: `$orden = $_GET['orden'] ?? 'id'` → **validar contra lista blanca** (ej: `in_array($orden, ['nombre','provincia'])`) y pasarlo a la función DAO.
3. Mantener `method="GET"` para filtros/orden porque son **consultas** (idempotentes, compartibles por URL). Usar `method="POST"` solo para `INSERT/UPDATE/DELETE`.

---

## 7. HTML, formularios y procesamiento: cuándo y cómo

### 7.1 ¿Cuándo usar un formulario?

| Situación | Método | Action | Ejemplo |
|---|---|---|---|
| **Crear / Modificar / Eliminar** (cambia la BD) | `POST` | `gestionX.php` (archivo sin HTML) | `<form action="gestionPersona.php" method="POST"><input type="hidden" name="accion" value="crear">` |
| **Buscar / Filtrar / Ordenar** (solo consulta) | `GET` | La **misma página** que lista (`viewX.php`) | `<form method="GET" action="viewPersona.php"><input name="busqueda">` |
| **Ir a editar** (precargar formulario) | `GET` (link, no form) | `editX.php?id=X` | `<a href="editPersona.php?id=<?= $p['id'] ?>">Modificar</a>` |
| **Eliminar** (fila con confirmación) | `POST` con `hidden` | `gestionX.php` | `<form method="POST" onsubmit="return confirm('¿Seguro?')"> <input type="hidden" name="accion" value="eliminar">` |

**Regla:** `GET` deja el dato en la URL (`?busqueda=Jul&id=5`) → recargable, compartible, va a historial. `POST` lo manda en el cuerpo → no aparece en URL, es para operaciones que **modifican**.

### 7.2 Cómo obtener datos del formulario y procesarlos

**En la página que muestra el formulario (`edit*.php`):**
- Para precargar (edición): `if(isset($_GET['id'])) { (int)$_GET['id'] → obtenerPorId() → value="<?= htmlspecialchars($dato) ?>" }`
- Para mostrar errores de validación: `if(isset($_GET['error'])) echo htmlspecialchars($_GET['error'])`

**En el procesador (`gestion*.php`):**
```php
<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../../index.php"); exit; }
$accion = $_POST['accion'] ?? '';
if ($accion === 'eliminar') { $id=(int)$_POST['id']; eliminar(...); header("Location: viewX.php"); exit; }
if ($accion === 'actualizar') { /* validar → actualizar → header */ }
if (isset($_POST['nombre'])) { /* crear → insertar → header */ }
```
Patrones obligatorios:
- `trim()` para textos, `(int)` para IDs/FKs, `null` para opcionales vacíos (`$_POST['campo']!=='' ? valor : null`).
- `htmlspecialchars()` **siempre** al **mostrar** datos del usuario (previene XSS). `?` + `execute([])` **siempre** al **guardar** (previene inyección SQL).
- Después de `header("Location: ...")` siempre `exit;` y **nunca** haber hecho `echo` antes (error *headers already sent*).

**En la página que filtra (`view*.php`):**
```php
<?php
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filas = obtenerTodas($conexion, $busqueda); // DAO arma WHERE LIKE solo si $busqueda!==''
```
Y en el HTML: `value="<?= htmlspecialchars($busqueda) ?>"` para dejar escrito lo buscado.

### 7.3 Flujo completo de "Modificar persona" (ejemplo de punta a punta)

```
1. Usuario click "Modificar" en viewPersona.php
   └─ GET editPersona.php?id=5

2. editPersona.php (frontend)
   ├─ require lib/bd/gestionBaseDatos.php + lib/html/funcionesHTML.php
   ├─ $persona = obtenerPersonaPorId($conexion, 5)   -- SELECT ... LEFT JOIN ...
   ├─ $ciudades = obtenerTodasLasCiudades($conexion) -- para el <select>
   └─ renderiza <form action="gestionPersona.php" method="POST">
        + hidden accion=actualizar + hidden id=5 + inputs value="..."

3. Usuario cambia teléfono y pulsa "Guardar cambios"
   └─ POST gestionPersona.php (accion=actualizar, id=5, nombre, ..., ciudad_id=2)

4. gestionPersona.php (backend sin HTML)
   ├─ $datos = [ 'nombre'=>trim($_POST['nombre']), ..., 'ciudad_id'=>(int)$_POST['ciudad_id'] ]
   ├─ actualizarPersona($conexion, 5, $datos)         -- UPDATE ... WHERE id=?
   └─ header("Location: viewPersona.php"); exit;

5. viewPersona.php (frontend) recarga
   ├─ $personas = obtenerTodasLasPersonas($conexion)  -- SELECT ... JOIN ...
   └─ foreach dibuja tabla actualizada + piePagina()
```

---

## 8. Checklist para agregar una funcionalidad sin romper nada

- [ ] ¿Creé/actualicé la tabla en `lib/bd/script.sql` y la ejecuté?
- [ ] ¿Todas las consultas nuevas usan `?` + `execute([])` (nunca `".$_GET['x']."` en SQL)?
- [ ] ¿Puse la lógica SQL solo en `lib/bd/gestionBaseDatos.php` (no SQL suelto en `view/edit`)?
- [ ] ¿El HTML reutilizable está en `lib/html/funcionesHTML.php` y no copiado en 5 archivos?
- [ ] ¿El formulario usa `method="POST"` para guardar/borrar y `method="GET"` para buscar?
- [ ] ¿Valido en PHP antes de mandar a MySQL (rangos, `is_numeric`, `trim`, `htmlspecialchars` al mostrar)?
- [ ] ¿Uso `(int)` para IDs, `htmlspecialchars()` al imprimir, y `header()+exit` sin echo previo?
- [ ] ¿El `<select>` de FK se puebla desde BD y guarda `id` (no texto)?
- [ ] ¿Si hay FK, verifico `hayPersonasEnCiudad()` antes de `DELETE` y muestro `?error=1`?
- [ ] ¿Probé los 4 casos: entrar sin `?busqueda`, buscar existente, buscar inexistente, limpiar filtro?

---

## 9. Resumen en una frase

- **Etapa 2** te enseña el **ciclo mínimo web con BD**: formulario (frontend) → procesador (backend) → tabla (frontend) + filtro GET/LIKE.
- **Etapa 3** te enseña a **escalar** ese ciclo: `lib/` centraliza BD+HTML reutilizable, `src/<entidad>/` aísla cada módulo en `view` (GET, lista+filtro) / `edit` (GET `?id=` + POST `accion`) / `gestion` (POST + validación + `header`), y la **normalización con JOIN/FK** reemplaza texto repetido por relaciones.
