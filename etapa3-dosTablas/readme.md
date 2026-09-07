# Etapa 3 - Dos Tablas (Personas + Ciudades)

> **Evolución de `etapa2-BD-FiltroyUpdate` + `etapa3-BD-tablas`**
> Proyecto educativo PHP + MySQL + Bootstrap 5 que demuestra cómo pasar de **una sola tabla** a **dos tablas relacionadas**, manteniendo todas las funcionalidades previas (filtro y actualización de personas).

---

## 1. ¿Qué contiene este proyecto?

### Idea en una frase
Gestionar **personas** y **ciudades** en una base de datos MySQL, donde cada persona apunta a la ciudad donde nació mediante una **clave foránea** (`ciudad_id`).

### Funcionalidades completas
| Funcionalidad | ¿Cómo se usa? | Operación SQL |
|---|---|---|
| **Ver dashboard** | `index.php` muestra cuántas personas y ciudades hay (`COUNT(*)`) | `SELECT COUNT(*)` |
| **Listar personas con filtro** | `src/persona/viewPersona.php` con buscador por nombre | `SELECT ... JOIN ... WHERE nombre LIKE '%...%'` |
| **Crear persona** | `src/persona/editPersona.php` sin `?id=` → formulario vacío | `INSERT INTO personas` |
| **Modificar persona** | Mismo formulario con `?id=X` → precargado, botón "Guardar cambios" | `UPDATE personas SET ... WHERE id = ?` |
| **Eliminar persona** | Botón "Eliminar" en la tabla | `DELETE FROM personas WHERE id = ?` |
| **Listar ciudades con filtro** | `src/ciudad/viewCiudad.php` con buscador por nombre | `SELECT * FROM ciudades WHERE nombre LIKE ...` |
| **Crear ciudad** | `src/ciudad/editCiudad.php` sin `?id=` | `INSERT INTO ciudades` |
| **Modificar ciudad** | Mismo formulario con `?id=X` | `UPDATE ciudades SET ... WHERE id = ?` |
| **Eliminar ciudad** | Botón "Eliminar" (solo si no tiene personas) | `DELETE FROM ciudades WHERE id = ?` (con verificación `hayPersonasEnCiudad`) |
| **Elegir ciudad al crear persona** | `<select>` en `editPersona.php` poblado desde la tabla ciudades | `SELECT * FROM ciudades` para el desplegable |

Todas las páginas navegables incluyen un **pie de página** común generado por la función `piePagina()` de `lib/html/funcionesHTML.php`.

---

## 2. La mejora: normalización de la base de datos

### 2.1 Cómo era en la Etapa 2 (una sola tabla)

```sql
-- Tabla personas (Etapa 2)
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    dni VARCHAR(20),
    ciudad VARCHAR(100),      -- ← texto libre, se repetía en cada fila
    provincia VARCHAR(100),   -- ← texto libre
    codigo_postal VARCHAR(10),-- ← texto libre
    observaciones TEXT
);
```

**Problemas:**

1.  **Datos repetidos:** si 30 personas son de "Río Cuarto", el string `"Río Cuarto"` se guarda 30 veces. La BD ocupa más y es más lenta.
2.  **Inconsistencia:** el usuario puede escribir `"Rio Cuarto"`, `"Río Cuarto"`, `"RIO CUARTO"`, `"Río 4to"`. Para MySQL son 4 ciudades distintas, aunque para nosotros es la misma. Los filtros y estadísticas fallan.
3.  **Origen no genuino:** `ciudad`, `provincia` y `codigo_postal` son **atributos del lugar**, no de la persona. Una persona *nace* en una ciudad, pero la provincia y el código postal describen a la ciudad, no a ella.
4.  **Actualización costosa:** si cambia el código postal de una ciudad, hay que hacer `UPDATE personas SET codigo_postal = 'X5800' WHERE ciudad = 'Río Cuarto'` y tocar 30 filas. Si olvidás una, queda inconsistente.

### 2.2 Cómo es ahora en la Etapa 3 (dos tablas normalizadas)

```sql
-- Tabla ciudades: cada ciudad se escribe UNA sola vez
CREATE TABLE ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    latitud DECIMAL(9,6),
    longitud DECIMAL(10,6),
    codigo_postal VARCHAR(10),  -- ← pertenece a la ciudad
    descripcion TEXT,
    fecha_fundacion DATE
);

-- Tabla personas: solo guarda una referencia a la ciudad
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    -- ... otros campos ...
    ciudad_id INT,                              -- ← clave foránea (número, no texto)
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
```

**Ventajas:**

| Antes (desnormalizado) | Ahora (normalizado) |
|---|---|
| `ciudad = "Río Cuarto"` repetido 30 veces | `ciudad_id = 1` repetido 30 veces (un INT pesa menos que un VARCHAR) |
| Cualquier texto vale, incluso con errores | Solo valen IDs que **existen** en `ciudades` (la FK lo garantiza) |
| Cambiar un dato de la ciudad = actualizar N filas | Cambiar un dato de la ciudad = `UPDATE ciudades SET ... WHERE id = 1` (una sola fila) |
| No se puede contar "personas por provincia" sin limpiar textos | `JOIN ciudades ON personas.ciudad_id = ciudades.id GROUP BY provincia` funciona perfecto |

**Ejemplo visual:**

```
ciudades:
  1 | Río Cuarto | Córdoba | 5800 | -33.12 | -64.34
  2 | Córdoba    | Córdoba | 5000 | -31.42 | -64.18
  7 | Rosario    | Santa Fe| 2000 | -32.95 | -60.69

personas:
  1 | Julián | Conde | ciudad_id=1 → Río Cuarto
  2 | María  | Pérez | ciudad_id=2 → Córdoba
  8 | Sofía  | González | ciudad_id=7 → Rosario
```

Para mostrar "Julián - Río Cuarto - Córdoba" se hace:

```sql
SELECT p.nombre, c.nombre AS ciudad, c.provincia
FROM personas p JOIN ciudades c ON p.ciudad_id = c.id;
```

### 2.3 ¿Qué se sacó de `personas` y a dónde fue?

| Columna en Etapa 2 (`personas`) | ¿A dónde fue en Etapa 3? |
|---|---|
| `ciudad VARCHAR(100)` | Eliminada. Reemplazada por `ciudad_id INT` → `ciudades.id` |
| `provincia VARCHAR(100)` | Movida a `ciudades.provincia` |
| `codigo_postal VARCHAR(10)` | Movida a `ciudades.codigo_postal` |
| *(nuevo)* `latitud`, `longitud`, `descripcion`, `fecha_fundacion` | Solo existen en `ciudades` (son datos del lugar, no de la persona) |

---

## 3. ¿Qué hay en el proyecto? Estructura de archivos

```
etapa3-dosTablas/
├── index.php                     # Portada / Dashboard con estadísticas
├── img/
│   └── mysql.png                 # Logo de MySQL (recurso estático)
├── lib/                         # Lado BACKEND compartido (librerías no navegables)
│   ├── bd/
│   │   ├── gestionBaseDatos.php  # DAO: conexión PDO + todas las funciones SQL (personas y ciudades)
│   │   ├── script.sql            # CREATE DATABASE + CREATE TABLE ciudades, personas (FK)
│   │   ├── script_inserts.sql    # 20 ciudades + 50 personas de ejemplo
│   │   ├── script_update.sql     # UPDATE de 1 persona y 1 ciudad (ejemplos comentados)
│   │   ├── script_consultas.sql  # 20+ SELECT desde simples hasta JOIN + GROUP BY + agregadas
│   │   └── script_delete.sql     # DELETE de 1 persona + intento comentado de borrar ciudad con FK
│   └── html/
│       └── funcionesHTML.php     # Funciones de vista: piePagina() y mostrarAlerta()
└── src/                         # Lado FRONTEND/BACKEND por entidad
    ├── persona/
    │   ├── editPersona.php       # Formulario alta/edición (navegable: GET ?id= para precargar)
    │   ├── viewPersona.php       # Listado + filtro LIKE + botones Modificar/Eliminar (navegable)
    │   └── gestionPersona.php    # Procesa INSERT/UPDATE/DELETE y redirige (NO navegable, sin HTML)
    └── ciudad/
        ├── editCiudad.php        # Formulario alta/edición de ciudades (navegable)
        ├── viewCiudad.php        # Listado + filtro + CRUD ciudades (navegable)
        └── gestionCiudad.php     # Procesa INSERT/UPDATE/DELETE ciudades (NO navegable)
```

### 3.1 Frontend vs Backend (navegable vs no navegable)

En este proyecto usamos la misma distinción que en la Etapa 2, pero ahora organizada en carpetas:

| Tipo | Archivos | ¿El usuario los ve? | ¿Tienen HTML? | Rol |
|---|---|---|---|---|
| **Frontend (navegables)** | `index.php`, `src/persona/editPersona.php`, `src/persona/viewPersona.php`, `src/ciudad/editCiudad.php`, `src/ciudad/viewCiudad.php` | **Sí** – son páginas con Bootstrap, tablas, formularios, buscadores | **Sí** – `<!DOCTYPE html>`, `<form>`, `<table>` | Muestran datos y capturan entrada del usuario. Llaman a las librerías de `lib/` con `require_once` |
| **Backend (no navegables)** | `src/persona/gestionPersona.php`, `src/ciudad/gestionCiudad.php`, `lib/bd/gestionBaseDatos.php`, `lib/html/funcionesHTML.php`, `lib/bd/*.sql` | **No** – si el usuario escribe su URL directamente, lo redirigen o muestran un error | **No** (excepto `funcionesHTML.php` que genera HTML pero no es una página completa) | Hacen el trabajo pesado: conectarse a MySQL, validar, hacer `INSERT/UPDATE/DELETE/SELECT` y redirigir con `header("Location: ...")` |

**Flujo típico (ej: modificar una persona):**

```
1. Usuario hace click en "Modificar" en viewPersona.php (GET a editPersona.php?id=5)
2. editPersona.php (frontend) hace:
      require 'lib/bd/gestionBaseDatos.php'
      $persona = obtenerPersonaPorId($conexion, 5)  → SELECT * WHERE id = 5
   y muestra el formulario precargado con value="..."
3. Usuario cambia el teléfono y aprieta "Guardar cambios" (POST a gestionPersona.php con accion=actualizar)
4. gestionPersona.php (backend, sin HTML) hace:
      actualizarPersona($conexion, 5, $datos)  → UPDATE ... WHERE id = 5
      header("Location: viewPersona.php")
5. viewPersona.php (frontend) vuelve a consultar y muestra la tabla actualizada
```

> **Concepto clave:** no todo archivo `.php` es una página web. Los de `lib/` y `gestion*.php` son "empleados" que trabajan detrás de escena.

### 3.2 El pie de página común

`lib/html/funcionesHTML.php` define:

```php
function piePagina() {
    echo '<footer class="bg-dark text-white text-center py-3 mt-5">...</footer>';
}
```

Cada página navegable la usa así (antes de `</body>`):

```php
require_once '../../lib/html/funcionesHTML.php';
// ... todo el HTML ...
<?php piePagina(); ?>
<script src="bootstrap..."></script>
</body>
```

Ventajas: si querés cambiar el pie (color, texto, año), lo cambiás en **un solo lugar** y se actualiza en 5 páginas. Es el mismo principio de reutilización que con `gestionBaseDatos.php` pero para HTML.

---

## 4. Scripts SQL incluidos (lib/bd/)

| Script | Qué hace | Cómo ejecutarlo |
|---|---|---|
| `script.sql` | Crea la BD `contactos3` y las dos tablas con su `FOREIGN KEY`. Ejecutar **una sola vez** al inicio. | `mysql -u root -p < lib/bd/script.sql` o pegar en phpMyAdmin → SQL |
| `script_inserts.sql` | Inserta **20 ciudades** (Río Cuarto, Córdoba, Rosario, Mendoza, etc.) y **50 personas** variadas, cada una con `ciudad_id` válido. | `mysql -u root -p contactos3 < lib/bd/script_inserts.sql` |
| `script_update.sql` | Dos ejemplos de `UPDATE`: corrige el email/teléfono de la persona `id=1` y la descripción/coordenadas de la ciudad `id=1`. Incluye bloque comentado de `UPDATE con JOIN`. | Ejecutar después de los inserts, bloque por bloque en phpMyAdmin |
| `script_consultas.sql` | **Guía progresiva de SELECT**: simples → `WHERE/LIKE` → `JOIN` → `GROUP BY/COUNT/HAVING/AVG/LIMIT`. Cada consulta tiene comentario explicando qué hace. | Copiar cada `SELECT` y ejecutarlo en phpMyAdmin para ver el resultado |
| `script_delete.sql` | Borra la persona `id=50` (`DELETE ... WHERE id = 50`). Incluye ejemplos comentados de borrado por DNI y explicación de por qué `DELETE FROM ciudades WHERE id=1` falla si hay personas con `ciudad_id=1` (protección de la FK). | `mysql -u root -p contactos3 < lib/bd/script_delete.sql` |

---

## 5. Cómo ejecutar el proyecto

```bash
# 1. Crear la base de datos
mysql -u root -p < lib/bd/script.sql

# 2. Poblar con datos de ejemplo
mysql -u root -p contactos3 < lib/bd/script_inserts.sql

# 3. Verificar credenciales en lib/bd/gestionBaseDatos.php
#    $host = "localhost"; $usuario = "root"; $contrasena = ""; $baseDatos = "contactos3";

# 4. Levantar el servidor PHP desde la carpeta del proyecto
cd etapa3-dosTablas
php -S localhost:8000

# 5. Abrir en el navegador
#    http://localhost:8000/index.php          → Portada con estadísticas
#    http://localhost:8000/src/persona/viewPersona.php  → Personas (con filtro y Modificar)
#    http://localhost:8000/src/ciudad/viewCiudad.php    → Ciudades
```

---

## 6. Comentarios en el código

Todos los archivos `.php` tienen **comentarios entre líneas** explicando:

*   Qué hace cada `require_once` y por qué el orden importa
*   Diferencia `GET` (consultas, filtros, precarga de formularios) vs `POST` (INSERT/UPDATE/DELETE)
*   Uso de `?` + `execute([])` para evitar inyección SQL
*   Significado de `htmlspecialchars()`, `trim()`, `(int)`, `fetch()` vs `fetchAll()`, `header("Location: ...") + exit`
*   Por qué `UPDATE` y `DELETE` necesitan `WHERE id = ?` y qué pasa si se omite

Los scripts `.sql` también están comentados bloque por bloque para que los estudiantes puedan ejecutarlos de a uno y entender cada consulta.

---

## 7. Próximos pasos (ideas para seguir)

*   **Paginación:** mostrar la tabla de a 10 filas con `LIMIT 10 OFFSET ?` y botones "Anterior/Siguiente".
*   **Ordenar por columna:** click en "Nombre" o "Ciudad" para hacer `ORDER BY nombre ASC/DESC` dinámico.
*   **Validaciones en PHP:** verificar que el DNI no se repita antes de `INSERT` (`SELECT id FROM personas WHERE dni = ?`).
*   **Resaltar recién modificado:** redirigir con `?modificado=5` y en `viewPersona.php` agregar `class="table-success"` a esa fila.
*   **Mapa con LeafletJS:** usar `latitud`/`longitud` de `ciudades` para dibujar un marcador por ciudad.
*   **Gráfico con ChartJS:** `SELECT provincia, COUNT(*) GROUP BY provincia` → gráfico de barras de personas por provincia.

