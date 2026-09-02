# Agregando un Filtro a la Etapa 2-BD

> **Objetivo:** agregarle a `resultado.php` un buscador que permita filtrar las personas por nombre.  
> **Punto de partida:** tenés la aplicación **Etapa 2-BD** funcionando — formulario que guarda en MySQL y una página `resultado.php` que muestra **todas** las personas con `obtenerTodasLasPersonas()`.

Al terminar, vas a poder escribir "Jul" en una cajita, apretar **Buscar** y ver solo las personas cuyo nombre contiene "Jul" (Julián, Julia, etc.). Si dejás la cajita vacía y apretás Buscar, ves a todos de nuevo.

---

## 0. Idea general: ¿qué vamos a tocar?

Un filtro tiene **dos partes** que trabajan juntas, como un equipo:

1.  **Backend (la base de datos):** una función nueva que sabe hacer `SELECT ... WHERE nombre LIKE '%algo%'`.
2.  **Frontend + conexión (la página visible):** leer lo que el usuario escribió, llamar a esa función y mostrar el resultado.

Solo vamos a modificar **2 archivos**:

| Archivo | Qué cambiamos |
|---|---|
| `bd/gestionBaseDatos.php` | Agregamos una función nueva: `obtenerPersonasConFiltro()` |
| `resultado.php` | 3 cambios chiquitos: (A) leer el texto buscado, (B) agregar la cajita de búsqueda, (C) mejorar el cartel de "no hay resultados" |

> `index.php` y `procesando.php` **no se tocan**. El filtro es solo para *mostrar* datos, no para guardarlos.

---

## 1. Paso 1 — El backend: crear `obtenerPersonasConFiltro()` en `bd/gestionBaseDatos.php`

### 1.1 ¿Qué hace esta función?

En la Etapa 2 tenías:

```php
function obtenerTodasLasPersonas($conexion) {
    $sql = "SELECT * FROM personas ORDER BY id DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

Siempre trae **todas** las filas. Ahora queremos algo más inteligente:

> Si el usuario no buscó nada → trae a todos.  
> Si buscó "Jul" → trae solo los que tienen "Jul" en el nombre.

En SQL eso se hace con `LIKE` y el comodín `%`:

```sql
-- Trae todos los que tengan "jul" en cualquier parte del nombre
SELECT * FROM personas WHERE nombre LIKE '%jul%'
-- '%jul%' matchea "Julián", "Julia", "Julieta"
```

`%` significa "cualquier texto, de cualquier largo". `'Jul%'` sería "empieza con Jul", `'%Jul'` sería "termina con Jul".

### 1.2 La función nueva

Abrí `bd/gestionBaseDatos.php` y **al final del archivo, antes de `eliminarPersona()`**, agregá esta función (captura `4-obtenerPersonaConFiltro.png`):

![Función con filtro](./agregandoFiltro%20a%20Etapa2/4-obtenerPersonaConFiltro.png)

```php
/**
 * Obtiene las personas que cumplan con el filtro de búsqueda.
 * @param PDO $conexion Conexión a la base de datos
 * @param string $busqueda Término de búsqueda (filtra por nombre)
 * @return array Array de personas
 */
function obtenerPersonasConFiltro($conexion, $busqueda = '') {
    // Consulta base
    $sql = "SELECT * FROM personas p";

    // Si hay término de búsqueda, agregamos un WHERE con LIKE
    $parametros = [];
    if ($busqueda !== '') {
        $sql .= " WHERE p.nombre LIKE ?";
        $parametros[] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Puntos clave para entender el código:**

| Línea | Qué significa |
|---|---|
| `$busqueda = ''` | El parámetro es **opcional**. Si nadie lo pasa, vale `''` (vacío) y trae a todos. |
| `$sql = "SELECT * FROM personas p"` | `p` es un **alias** (apodo) para la tabla `personas`. Después podemos escribir `p.nombre` en vez de `personas.nombre`. |
| `if ($busqueda !== '')` | Solo agregamos el `WHERE` si realmente hay algo que buscar. |
| `$sql .= " WHERE p.nombre LIKE ?"` | El `.=` **concatena**: le agrega texto a la consulta que ya teníamos. |
| `$parametros[] = '%' . $busqueda . '%'` | Armamos `'%Jul%'`. Va entre `%` para que busque "que contenga". |
| `?` + `execute($parametros)` | **Prepared statement**: el `?` se reemplaza de forma segura. Nunca concatenes `$_GET` directo en el SQL (evitás inyección SQL). Si `$busqueda` está vacío, `$parametros` queda como `[]` y `execute([])` simplemente trae todos. |

> **No borres** `obtenerTodasLasPersonas()`. Dejala como está; la nueva función la complementa. Te sirve para comparar o por si otro archivo la usa.

---

## 2. Paso 2 — Conectar el filtro en `resultado.php` (parte PHP, arriba de todo)

En `resultado.php`, bien arriba donde dice `<?php ... ?>`, hacés el cambio que se ve en la captura `1-obtenerPersonas.png`:

![Cambiar la consulta en resultado.php](./agregandoFiltro%20a%20Etapa2/1-obtenerPersonas.png)

### Antes (Etapa 2 sin filtro):

```php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion);
```

### Después (con filtro):

```php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();

// Esta línea vieja la comentamos o la borramos:
// $personas = obtenerTodasLasPersonas($conexion);

// Recibimos el término de búsqueda desde la URL (si existe)
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// La función recibe el término: si está vacío, trae todas las personas;
// si no, filtra por nombre usando LIKE
$personas = obtenerPersonasConFiltro($conexion, $busqueda);
```

**¿Qué estás haciendo línea por línea?**

1.  `$_GET['busqueda']` — Cuando el usuario apreta "Buscar", el navegador va a `resultado.php?busqueda=Jul`. Todo lo que va después de `?` viaja por **GET** y PHP lo guarda en el array `$_GET`.
2.  `isset($_GET['busqueda'])` — Pregunta: "¿vino algo llamado `busqueda` en la URL?". La primera vez que abrís la página, no vino nada, entonces da `false`.
3.  `? trim(...) : ''` — Es un **operador ternario** (un `if` en una línea). Si vino algo, lo limpia con `trim()` (saca espacios extra: `"  Jul "` → `"Jul"`). Si no vino nada, usa `''` (vacío).
4.  `$personas = obtenerPersonasConFiltro(...)` — Llamás a la función nueva. Si `$busqueda` es `''`, la función hace un `SELECT` sin `WHERE` y trae a todos. Si es `"Jul"`, hace `WHERE nombre LIKE '%Jul%'`.

> **¿Por qué GET y no POST?** Porque un filtro es una **consulta**, no un guardado. Con GET el término queda en la URL (`?busqueda=Jul`) y podés compartir el link, recargar la página o volver atrás y el filtro sigue ahí. POST es para cuando *cambiás* datos (guardar/eliminar), y por eso `index.php` usa POST.

---

## 3. Paso 3 — El buscador visible (formulario HTML en `resultado.php`)

Ahora que la parte de arriba ya sabe filtrar, falta la **cajita** donde el usuario escribe.

Dentro de `<div class="card-body">`, justo antes del `<?php if (empty($personas)): ?>`, agregá el formulario que se ve en la captura `2-formularioFiltro.png`:

![Formulario del buscador](./agregandoFiltro%20a%20Etapa2/2-formularioFiltro.png)

```html
<!--
    BUSCADOR:
    - method="GET": el término viaja en la URL (?busqueda=...)
    - action="resultado.php": recarga esta misma página con el filtro
    - value="...": deja el término escrito después de buscar
-->
<form method="GET" action="resultado.php" class="row g-2 mb-3">
    <div class="col-md-9">
        <input type="text" name="busqueda" class="form-control"
               placeholder="Buscar por nombre de persona..."
               value="<?php echo htmlspecialchars($busqueda); ?>">
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-success w-100">Buscar</button>
    </div>
</form>
```

**Desglose para no perderte:**

| Atributo | Para qué es |
|---|---|
| `method="GET"` | Le dice al navegador: "poné los datos en la URL". Al enviar, la URL queda `resultado.php?busqueda=lo-que-escribi`. |
| `action="resultado.php"` | "Cuando apretes Buscar, recargá esta misma página". Es la misma página la que se filtra a sí misma. |
| `name="busqueda"` | El nombre de la clave que llegará a `$_GET`. Tiene que coincidir con `$_GET['busqueda']` del Paso 2. |
| `placeholder="Buscar por..."` | Texto gris que se ve cuando la cajita está vacía. Es solo ayuda visual. |
| `value="<?php echo htmlspecialchars($busqueda); ?>"` | ¡Muy importante! Deja escrito lo que buscaste después de apretar Buscar. Sin esto, la cajita quedaría vacía y no sabrías qué filtraste. `htmlspecialchars()` evita que un texto con `<` o `"` rompa el HTML. |
| `class="row g-2 mb-3"` / `col-md-9` / `col-md-3` | Clases de **Bootstrap** para que la cajita ocupe 9 columnas y el botón 3, uno al lado del otro. |

---

## 4. Paso 4 — Mejorar el cartel de "no hay resultados"

En la Etapa 2 el cartel era siempre:

```html
<div class="alert alert-info">No hay personas registradas aún.</div>
```

Ahora hay **dos situaciones distintas** y queremos mensajes distintos:

1.  La tabla está vacía de verdad → "No hay personas registradas aún."
2.  La tabla tiene gente, pero nadie matchea tu búsqueda → "No se encontraron personas con ese nombre."

Reemplazá ese bloque por lo que se ve en la captura `3-ifEmpty.png`:

![Mensaje diferenciado según el filtro](./agregandoFiltro%20a%20Etapa2/3-ifEmpty.png)

```php
<?php if (empty($personas)): ?>
    <!-- Si no hay personas en la BD, mostramos un mensaje informativo -->
    <!-- <div class="alert alert-info">No hay personas registradas aun.</div> -->
    <div class="alert alert-info">
        <?php echo ($busqueda !== '') ? 'No se encontraron personas con ese nombre.' : 'No hay personas registradas aun.'; ?>
    </div>
<?php else: ?>
```

**¿Cómo funciona?**

*   `empty($personas)` — ¿el array vino vacío? Puede ser por las dos razones de arriba.
*   `($busqueda !== '') ? ... : ...` — Otro ternario: si `$busqueda` **no** está vacío, es porque el usuario buscó algo y no hubo coincidencias → mostrá el primer mensaje. Si está vacío, es porque la tabla realmente no tiene filas → mostrá el segundo.

El resto del archivo (el `foreach` que dibuja la tabla, el botón Eliminar, el `Volver al formulario`) **queda exactamente igual**.

---

## 5. Paso 5 — Probar que todo funciona

1.  Levantá el servidor desde la carpeta correcta:

    ```bash
    cd etapa2-BD-Filtro
    php -S localhost:8000
    ```

2.  Abrí `http://localhost:8000/resultado.php`. Deberías ver el buscador arriba de la tabla, como en la captura `5-frontendFiltro.png`:

    ![Resultado final con buscador](./agregandoFiltro%20a%20Etapa2/5-frontendFiltro.png)

3.  Probá estos casos:

    | Qué hacés | Qué esperás ver en la URL | Qué ves en la tabla |
    |---|---|---|
    | Entrás sin buscar | `resultado.php` | Todas las personas |
    | Escribís `Jul` y apretás Buscar | `resultado.php?busqueda=Jul` | Solo Julián, Julia, etc. |
    | Escribís `ZZZ` (que no existe) | `resultado.php?busqueda=ZZZ` | Cartel: "No se encontraron personas con ese nombre." |
    | Borrar el texto y apretar Buscar de nuevo | `resultado.php?busqueda=` | Vuelven todos |

    Si cambiás la URL a mano (`?busqueda=ma`) y apretás Enter, también filtra. Eso es la magia de GET: el filtro es parte del link.

4.  Revisá que **Eliminar** siga funcionando (usa POST y va a `procesando.php`, no toca el filtro).

---

## 6. Resumen: cómo viaja un filtro de punta a punta

```
1. Usuario escribe "Jul" y aprieta Buscar
                │
                ▼  GET  (el navegador arma la URL)
  resultado.php?busqueda=Jul
                │
                ▼  PHP lee $_GET
      $busqueda = "Jul"
                │
                ▼  Llama a la función con el término
  obtenerPersonasConFiltro($conexion, "Jul")
                │
                ▼  SQL armado dinámicamente
  SELECT * FROM personas p WHERE p.nombre LIKE '%Jul%' ORDER BY p.id DESC
                │
                ▼  MySQL devuelve solo los que matchean
           $personas = [ Julián, Julia ]
                │
                ▼  foreach dibuja las filas
           <table> ... solo 2 filas ...
                │
                ▼  value="Jul" deja la palabra escrita
         [ Buscar por nombre... Jul ] [Buscar]
```

Todo el trabajo extra fueron **~20 líneas**: una función con `LIKE` y un formulario con `GET`.

---

## 7. Para seguir jugando (desafíos)

Una vez que este filtro anda, podés animarte a:

*   **Filtrar por apellido también:** cambiá el `WHERE` a `WHERE p.nombre LIKE ? OR p.apellido LIKE ?` y pasá dos parámetros iguales (`['%'.$busqueda.'%', '%'.$busqueda.'%']`).
*   **Filtrar por ciudad:** `WHERE p.ciudad LIKE ?`.
*   **Búsqueda insensible a mayúsculas:** MySQL con `utf8mb4` ya lo hace, pero probá buscar `julian` en minúsculas y ver si encuentra `Julián`.
*   **Botón "Limpiar filtro":** un link a `resultado.php` sin parámetros (`<a href="resultado.php" class="btn btn-secondary">Limpiar</a>`) que vuelve a mostrar todo.

¡Con esto ya tenés tu primera funcionalidad que interactúa con la base de datos para *consultar* de forma inteligente!
