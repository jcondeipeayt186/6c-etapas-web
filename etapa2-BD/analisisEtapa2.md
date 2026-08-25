# Analisis del proyecto `etapa2-BD`

Formulario de contacto con **PHP + MySQL** (tu primer proyecto que usa una base de datos).

---

## 1. Idea general del proyecto

La aplicacion tiene una sola funcion: **cargar personas en una base de datos y luego mostrarlas en una tabla**, con la posibilidad de eliminarlas.

Lo nuevo respecto de las etapas anteriores es que los datos **ya no se pierden al cerrar el navegador**: quedan guardados en MySQL.

Para que esto funcione, el proyecto se separa en **dos mundos**:

| Mundo | Archivos | Como se ven |
|------|----------|-------------|
| Paginas **navegables** (el usuario las ve y usa) | `index.php`, `resultado.php` | Si, tienen HTML, colores, botones |
| Librerias intermedias (**no navegables**, trabajan detras de escena) | `procesando.php`, `bd/gestionBaseDatos.php` | No, son solo codigo PHP puro |

> Concepto importante: no todo archivo PHP es una pagina web. Algunos son "empleados" que hacen el trabajo y no se muestran al usuario.

---

## 2. Que hay en el proyecto

```
etapa2-BD/
├── index.php                  <- Pagina 1 (navegable): el formulario
├── resultado.php              <- Pagina 2 (navegable): la tabla de personas
├── procesando.php             <- Libreria 1 (no navegable): recibe y guarda los datos
├── img/
│   └── mysql.png              <- Icono que indica "este sitio usa MySQL"
└── bd/
    ├── gestionBaseDatos.php   <- Libreria 2 (no navegable): conexion + funciones SQL
    └── script.sql             <- Instrucciones para crear la base de datos
```

### Para que sirve cada archivo

| Archivo | Tipo | Que hace |
|---------|------|----------|
| `index.php` | Navegable | Muestra el formulario con todos los campos. El usuario escribe y presiona "Enviar" |
| `procesando.php` | No navegable | "El cerebro". Recibe lo que envio el formulario, lo guarda en MySQL (o elimina) y manda al usuario a `resultado.php` |
| `resultado.php` | Navegable | Consulta todos los registros de la tabla `personas` y los muestra en una tabla HTML |
| `bd/gestionBaseDatos.php` | No navegable | La "biblioteca" de funciones SQL: conectarse, insertar, consultar, eliminar |
| `bd/script.sql` | No navegable | El "plano" de la base de datos. Se ejecuta UNA vez para crear la base y la tabla |
| `img/mysql.png` | Recurso | Imagen decorativa (logo de MySQL) |

---

## 3. El recorrido completo (flujo de datos)

Cuando el usuario llena el formulario, los datos viajan de esta forma:

```
         1) Llena el formulario                2) Envia (POST)
 index.php  ─────────────────────►  procesando.php  ──────────────────────►  MySQL
                                                                               │
         4) El usuario ve la tabla       3) Redirige (header)                 │ guarda
 resultado.php  ◄─────────────────────  procesando.php  ◄──────────────────────┘
```

Y cuando se quiere eliminar una persona:

```
                        1) Boton "Eliminar" (POST, con id oculto)
resultado.php  ──────────────────────────────────────►  procesando.php  ──►  MySQL
        ▲                                                    │
        └────────────  2) Redirige de nuevo a resultado.php ◄─┘
```

**Puntos clave:**
- El usuario **solo ve** `index.php` y `resultado.php`.
- `procesando.php` es un intermediario: nunca muestra HTML, solo redirige.
- `gestionBaseDatos.php` no se ejecuta por si solo: es una **biblioteca de funciones** que otros archivos "importan".

---

## 4. Recorrido por archivo

### 4.1 `bd/script.sql` — El plano de la base de datos

Ejecutando este script (por ejemplo en phpMyAdmin) se crea la base `contactos` con la tabla `personas`.

```sql
CREATE DATABASE IF NOT EXISTS contactos;   -- crea la base (si no existe)
USE contactos;                              -- la selecciona

CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,      -- numero 1, 2, 3... identifica cada fila
    nombre VARCHAR(100) NOT NULL,           -- texto hasta 100 letras, no puede estar vacio
    dni VARCHAR(20) NOT NULL,
    ...
    observaciones TEXT                      -- texto largo, puede estar vacio
);
```

**Conceptos nuevos:**
- `CREATE DATABASE` / `CREATE TABLE`: crear la base y la tabla.
- `INT`, `VARCHAR(n)`, `DATE`, `TEXT`: tipos de dato (numero, texto corto, fecha, texto largo).
- `NOT NULL`: el campo es obligatorio.
- `AUTO_INCREMENT`: el `id` se genera solo (1, 2, 3...).
- `PRIMARY KEY`: el `id` identifica de forma unica cada registro.

> Este archivo se ejecuta **una sola vez**. No hace falta repetirlo cada vez que usamos la app.

---

### 4.2 `bd/gestionBaseDatos.php` — La biblioteca de funciones SQL

Este archivo define funciones que cualquier pagina puede usar. Por eso es la **capa de acceso a datos** (tambien llamada DAO).

#### La conexion: `obtenerConexion()`

```php
$conexion = new PDO(
    "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
    $usuario, $contrasena
);
```

- **PDO** (PHP Data Objects) es la clase de PHP para hablar con MySQL de forma segura.
- Los datos de conexion (servidor, usuario, contrasena, base) estan arriba, faciles de cambiar.
- El `try { } catch (PDOException $e) { }` captura errores de conexion: si MySQL no esta encendido o los datos son incorrectos, muestra un mensaje en vez de "pantalla en blanco".

#### Inyeccion SQL (la razon de los prepared statements)

Si concatenaramos los datos del usuario directamente en la consulta:

```php
$sql = "SELECT * FROM personas WHERE dni = '" . $dni . "'";
```

Un atacante podria enviar `123' OR '1'='1` y la consulta se convertiria en:

```sql
SELECT * FROM personas WHERE dni = '123' OR '1'='1'
```

que devuelve **todos** los registros, porque `'1'='1'` siempre es verdadero. Asi se puede robar o borrar informacion.

**Solucion:** *prepared statements*. Se escriben `?` (placeholders) y los valores se pasan por separado con `execute()`. Nunca se mezclan con la consulta.

#### Insertar: `insertarPersona()`

```php
$sql = "INSERT INTO personas (nombre, apellido, ...) VALUES (?, ?, ...)";
$stmt = $conexion->prepare($sql);
$stmt->execute([$datos['nombre'], $datos['apellido'], ...]);
```

- `INSERT INTO`: guarda una fila nueva.
- Los `?` son marcadores de posicion: el primero es para `nombre`, el segundo para `apellido`, etc.
- `execute([...])` une cada valor con su `?` **en orden**. El orden de la consulta y el del array deben coincidir exactamente.

#### Consultar: `obtenerTodasLasPersonas()`

```php
$sql = "SELECT * FROM personas ORDER BY id DESC";
$stmt->execute();
return $stmt->fetchAll(PDO::FETCH_ASSOC);
```

- `SELECT *`: trae todas las columnas de todas las filas.
- `ORDER BY id DESC`: ordena de mayor a menor (las personas mas nuevas primero).
- `fetchAll(PDO::FETCH_ASSOC)`: convierte el resultado en un **array de arrays asociativos**. Cada persona queda como `['id' => 1, 'nombre' => 'Juan', ...]`.

#### Eliminar: `eliminarPersona()`

```php
$sql = "DELETE FROM personas WHERE id = ?";
$stmt->execute([$id]);
```

- `DELETE ... WHERE id = ?`: borra **solo** la fila cuyo `id` coincida.
- Gracias al `?`, el `id` nunca puede "inyectarse" en la consulta.

---

### 4.3 `index.php` — El formulario (pagina navegable)

Es HTML normal con Bootstrap, pero con una parte clave:

```html
<form action="procesando.php" method="POST">
```

- **`action`**: a donde se envian los datos. Aqui NO es `resultado.php` (como en la etapa 1) sino `procesando.php`, porque primero hay que **guardar en la base de datos** y despues mostrar.
- **`method="POST"`**: los datos viajan "escondidos" en el cuerpo del pedido, no en la URL. Ideal para formularios.
- Cada campo tiene `name="nombre"`, `name="apellido"`, etc.: es la **clave** con la que PHP lee el valor en `$_POST['nombre']`.
- El atributo `required` del HTML evita enviar campos vacios (validacion del navegador).
- El `<img src="img/mysql.png">` en el titulo indica que la app usa MySQL.

---

### 4.4 `procesando.php` — El cerebro (no navegable)

No tiene ni una linea de HTML. Su unica mision: recibir datos, hacer algo con MySQL y redirigir.

#### Importar la biblioteca

```php
require_once 'bd/gestionBaseDatos.php';
```

Trae todas las funciones de conexion y SQL. Si el archivo no existiera, el programa se detiene.

#### Verificar que llegaron datos por POST

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

`$_SERVER` es una superglobal con datos del servidor. Aca confirmamos que la pagina fue visitada enviando un formulario y no abierta directamente.

#### Caso 1: eliminar

```php
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int) $_POST['id'];
    eliminarPersona($conexion, $id);
    header("Location: resultado.php");
    exit;
}
```

- El boton "Eliminar" de la tabla envia un campo oculto `accion=eliminar` con el `id` de la persona.
- `(int)` convierte el valor a numero entero (seguridad).
- `header("Location: ...")` **redirige** el navegador a otra pagina. Despues de redirigir siempre se usa `exit` para que el script se detenga.

#### Caso 2: insertar (con validacion)

```php
} else if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni'])) {
    $datos = [
        'nombre' => trim($_POST['nombre']),
        'apellido' => trim($_POST['apellido']),
        ...
    ];
    insertarPersona($conexion, $datos);
    header("Location: resultado.php");
    exit;
}
```

- `isset()` comprueba que el campo exista. Si alguien entrara a `procesando.php` directamente **sin pasar por el formulario**, estas variables no existirian y no se haria el insert.
- `trim()` elimina los espacios al inicio y al final de cada texto ("  Juan  " → "Juan").
- `$datos` es un **array asociativo**: cada valor tiene una clave (`$datos['nombre']`).
- Al final se llama a `insertarPersona()` y se redirige a `resultado.php`.

#### Caso 3: sin campos obligatorios

```php
} else {
    header("Location: index.php");
    exit;
}
```

Si no llegaron los datos minimos, el usuario vuelve al formulario.

---

### 4.5 `resultado.php` — La tabla de personas (pagina navegable)

Al abrirse, ejecuta PHP **antes** de dibujar el HTML:

```php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion);
```

`$personas` queda como un array asociativo. Ejemplo real:

```php
$personas = [
    0 => ['id' => 5, 'nombre' => 'Lucia', 'apellido' => 'Perez', ...],
    1 => ['id' => 4, 'nombre' => 'Juan',  'apellido' => 'Gomez', ...],
];
```

#### Contar registros

```php
<span class="badge"><?php echo count($personas); ?> registros</span>
```

`count()` devuelve cuantos elementos tiene el array (cuantas personas hay).

#### Mostrar una fila por persona (foreach)

```php
foreach ($personas as $persona): ?>
    <tr>
        <td><?php echo $persona['id']; ?></td>
        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
        ...
<?php endforeach; ?>
```

- `foreach`: recorre el array y en cada vuelta pone una persona en `$persona`. Por cada una se dibuja una `<tr>` (fila de la tabla).
- `$persona['nombre']`: accede a una columna de esa fila.

#### `htmlspecialchars()` (proteccion XSS)

```php
echo htmlspecialchars($persona['nombre']);
```

Convierte caracteres especiales (`<`, `>`, `"`, `&`) en texto seguro. Si alguien guardo `<script>...</script>` como nombre, se muestra como texto y **no se ejecuta**. Evita que se inyecte codigo malicioso en la pagina.

#### Boton eliminar

```php
<form action="procesando.php" method="POST" class="d-inline"
      onsubmit="return confirm('Seguro que deseas eliminar esta persona?');">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>
```

- Un `<input type="hidden">` no se ve pero viaja en el POST. Asi se envia la "orden" `accion=eliminar` y el `id` de la persona.
- `onsubmit="return confirm(...)"`: JavaScript pregunta "¿Seguro?" antes de enviar.

---

## 5. Conceptos clave (resumen)

| Concepto | Donde se ve | Idea principal |
|----------|-------------|----------------|
| **Navegable vs no navegable** | Todo el proyecto | Hay paginas (HTML) y librerias (solo PHP) |
| **POST vs GET** | `index.php` | POST escondido, GET en la URL |
| **`$_POST`** | `procesando.php` | Donde PHP guarda lo que envio el formulario |
| **`$_SERVER['REQUEST_METHOD']`** | `procesando.php` | Como llego el usuario a la pagina |
| **Prepared statements** | `gestionBaseDatos.php` | `?` + `execute()` = proteccion contra inyeccion SQL |
| **`fetchAll(FETCH_ASSOC)`** | `gestionBaseDatos.php` | Resultado SQL → array asociativo |
| **`header("Location:")` + `exit`** | `procesando.php` | Redirigir el navegador |
| **`foreach`** | `resultado.php` | Recorrer el array de personas |
| **`htmlspecialchars()`** | `resultado.php` | Evitar XSS |
| **`require_once`** | `procesando.php`, `resultado.php` | Importar la biblioteca de BD |
| **`isset()`, `trim()`, `count()`** | varios | Funciones utiles de PHP |

---

## 6. Ideas para mejorar el proyecto

Estas son buenas ideas para el trabajo practico o para continuar aprendiendo:

### 6.1. Agregar "actualizar" (editar personas)
Hoy la aplicacion solo crea, lee y elimina (faltaria la "U" de CRUD).
- Agregar un boton **Editar** en cada fila de `resultado.php`.
- Un formulario `formEditar.php` precargado con los datos actuales.
- En `procesando.php` detectar la accion `actualizar` y ejecutar `UPDATE personas SET ... WHERE id = ?`.
- Aprenderias: la consulta `UPDATE`, formularios que cargan datos desde la BD y `value="..."` para precargar inputs.

### 6.2. Agregar mas tablas y claves foraneas
Hoy `ciudad` y `provincia` se escriben a mano y pueden repetirse ("Cordoba" vs "cordoba").
- Crear una tabla `ciudades` con sus datos, y en `personas` guardar solo el `ciudad_id` (clave foranea).
- Elegir la ciudad con un `<select>` en vez de un texto libre.
- Aprenderias: `FOREIGN KEY`, `JOIN`, normalizacion, y por que es mejor guardar un ID que un texto.

### 6.3. Manejo de excepciones en toda la app
Hoy el `try/catch` solo protege la conexion.
- Envolver cada `execute()` en `try/catch` y mostrar mensajes amigables ("No se pudo guardar, la base esta ocupada") en vez de errores de MySQL.
- Tambien validar datos en PHP antes de enviarlos a la BD (rango de fechas, formato de email, etc.).
- Aprenderias: `PDOException`, `try/catch`, y la diferencia entre validar en el navegador (`required`) y en el servidor.

### 6.4. Adjuntar archivos e imagenes en el formulario
- Agregar un campo `<input type="file">` al formulario.
- El formulario deberia usar `enctype="multipart/form-data"`.
- Guardar el archivo en una carpeta (`uploads/`) con `move_uploaded_file()` y el nombre (o la ruta) en la base de datos.
- Aprenderias: la superglobal `$_FILES`, subida de archivos y seguridad de archivos (extensiones permitidas, tamanos maximos).

### 6.5. Librerias extras en la interfaz
- **LeafletJS (mapas):** si cada persona tiene una ciudad con latitud/longitud, se puede dibujar un mapa con un marcador por persona. Solo se agrega la libreria con un `<script>` y unas pocas lineas de JavaScript.
- **ChartJS (graficos estadisticos):** contar cuantas personas hay por ciudad y mostrar un grafico de barras o torta con los datos que ya trae PHP (se puede pasar a JavaScript con `json_encode`).
- Aprenderias: consumir librerias externas, pasar datos de PHP a JavaScript, y hacer visualizaciones de datos.

### 6.6. Otras ideas (opcionales)
- Paginacion: mostrar la tabla de a 10 registros con botones "Siguiente / Anterior".
- Ordenar por columna: clickear "Nombre" o "Apellido" para ordenar (`ORDER BY` dinamico).
- Buscador: filtrar personas por nombre con `WHERE nombre LIKE '%texto%'`.
- Sesiones y login: `$_SESSION` para que solo usuarios con contrasena puedan entrar.
