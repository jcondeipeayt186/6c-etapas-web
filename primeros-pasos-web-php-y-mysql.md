# Primeros pasos con páginas web en PHP y MySQL

> Guía paso a paso para estudiantes de secundario que están haciendo sus primeras experiencias con **HTML, CSS, Bootstrap, PHP y MySQL**.

Este documento recorre el proyecto **"Formulario de Contactos"** etapa por etapa. En cada etapa vas a ver **qué aprendés**, **qué hace cada archivo** y **explicaciones de los bloques de código más importantes**, con un detalle mayor en las etapas 2 y 3, donde aparece la conexión a la base de datos.

---

## Índice

1. [¿Cómo funciona una página web &#34;de verdad&#34;?](#1-cómo-funciona-una-página-web-de-verdad)
2. [Etapa 0 - Formulario HTML puro (sin PHP)](#2-etapa-0---formulario-html-puro-sin-php)
3. [Etapa 1 - PHP sin base de datos](#3-etapa-1---php-sin-base-de-datos)
4. [Etapa 2 - Formulario con base de datos MySQL (detallado)](#4-etapa-2---formulario-con-base-de-datos-mysql-detallado)
5. [Etapa 3 - Dos tablas relacionadas con clave foránea (detallado)](#5-etapa-3---dos-tablas-relacionadas-con-clave-foránea-detallado)
6. [Glosario de conceptos](#6-glosario-de-conceptos)
7. [Errores comunes y cómo solucionarlos](#7-errores-comunes-y-cómo-solucionarlos)
8. [Actividades para practicar](#8-actividades-para-practicar)

---

## 1. ¿Cómo funciona una página web "de verdad"?

Antes de ver código, necesitás entender **quiénes participan** cuando abrís una página web.

### Los 3 actores

```
+----------------+          +-------------------+          +---------------+
|   NAVEGADOR    |  pide    |      SERVIDOR     |  pide    |     MYSQL     |
|   (tu PC)      | -------> |  (Apache / PHP)   | -------> |  (base datos) |
|  Chrome, etc.  |  HTML    |  "el restaurante" |  datos   |  "el depósito"|
+----------------+          +-------------------+          +---------------+
        ▲                          |                              |
        |__________________________|______________________________|
                 respuesta con HTML (y los datos de la BD)
```

- **Navegador (cliente):** es el que "pide" páginas. Solo entiende HTML, CSS y JavaScript.
- **Servidor (Apache + PHP):** es como un restaurante. Recibe el pedido del navegador, **ejecuta código PHP**, consulta los datos que necesita y le "sirve" al navegador un HTML terminado. El navegador **nunca ve el código PHP**, solo ve el resultado.
- **Base de datos (MySQL):** es como un depósito o archivero gigante. Guarda los datos de forma permanente (aunque apagues la PC, los datos siguen).

### HTML vs PHP

|                                      | HTML            | PHP                            |
| ------------------------------------ | --------------- | ------------------------------ |
| ¿Quién lo procesa?                 | El navegador    | El servidor                    |
| ¿Puede guardar datos?               | No              | Sí (si se conecta a MySQL)    |
| ¿Puede leer datos de un formulario? | Solo mostrarlos | Sí, con`$_POST` o `$_GET` |
| Archivo                              | `pagina.html` | `pagina.php`                 |

> **Idea clave:** el navegador **pide** una página, el servidor la **prepara** (ejecutando PHP y consultando MySQL) y la **envía lista para mostrar**. Por eso PHP se dice que es un lenguaje **"del lado del servidor"**.

### ¿Qué necesitás para correr este proyecto?

Un "paquete" que incluya Apache, PHP y MySQL todo junto. El más usado en las escuelas es **XAMPP** (o Laragon). Una vez instalado:

1. Copiás la carpeta de la etapa que quieras a `htdocs` (dentro de XAMPP).
2. Encendés **Apache** y **MySQL** desde el panel de XAMPP.
3. Abrís en el navegador `http://localhost/etapa2-BD`.
4. Para la base de datos, entrás a `http://localhost/phpmyadmin`, vas a la pestaña **SQL**, pegás el contenido de `script.sql` y pulsás "Continuar".

También podés usar PHP sin XAMPP con el servidor de desarrollo:

```bash
cd etapa1
php -S localhost:8000
```

Y abrir `http://localhost:8000` en el navegador.

---

## 2. Etapa 0 - Formulario HTML puro (sin PHP)

> Carpeta: `etapa0-html/`

### Qué es

Un formulario sobre **películas y series** hecho **solo con HTML, CSS y JavaScript**. No hay servidor ni base de datos: todo pasa dentro del navegador.

### Archivos

| Archivo            | Para qué sirve                                           |
| ------------------ | --------------------------------------------------------- |
| `index.html`     | El formulario con 7 tipos de input distintos              |
| `resultado.html` | Muestra los datos elegidos, leyendo la URL con JavaScript |

### Conceptos que se aprenden

- Qué es un formulario HTML y para qué sirve cada **tipo de input**.
- Diferencia entre el **atributo `name`** y el **atributo `id`**.
- Cómo viajan los datos con el método **GET** (en la URL).
- Leer la URL con **JavaScript** (`URLSearchParams`).

### Bloques de código significativos

#### 1) El formulario y sus tipos de input

```html
<form id="formulario" action="resultado.html" method="get">
```

- `action="resultado.html"` → cuando presionás "Enviar", el navegador va a esa página.
- `method="get"` → los datos viajan pegados en la URL (`resultado.html?nombre=...&email=...`).

Adentro del formulario hay **7 tipos de input**:

```html
<input type="text"   name="nombre">       <!-- texto corto -->
<input type="email"  name="email">        <!-- texto + validación de email -->
<input type="number" name="temporadas">   <!-- solo números (con min y max) -->
<input type="date"   name="fechaEstreno"> <!-- selector de fecha del navegador -->

<!-- radio: elegís UNA opción (todas comparten el mismo name) -->
<input type="radio" name="tipo" value="Pelicula"> Pelicula
<input type="radio" name="tipo" value="Serie">    Serie

<!-- checkbox: podés marcar VARIAS opciones -->
<input type="checkbox" name="genero" value="Accion">
<input type="checkbox" name="genero" value="Comedia">

<textarea name="opinion">...</textarea>   <!-- texto largo, varias líneas -->

<select name="plataforma">                <!-- lista desplegable -->
    <option value="Netflix">Netflix</option>
    <option value="Disney+">Disney+</option>
</select>
```

> **Importante:** el atributo **`name`** es lo que importa para enviar datos: es la "etiqueta" con la que después vas a recuperar el valor (`nombre`, `email`, `temporadas`, ...). El `id` solo sirve para relacionar la etiqueta (`<label for="...">`) con el campo.

#### 2) Leer los datos en resultado.html con JavaScript

```html
<script>
    const params = new URLSearchParams(window.location.search);
    const nombre = params.get('nombre') || 'No completado';
    const generos = params.getAll('genero');
    const generosTexto = generos.length > 0 ? generos.join(', ') : 'Ninguno seleccionado';

    document.getElementById('resultado').innerHTML =
        '<p><strong>Nombre:</strong> ' + nombre + '</p>'
        + '<p><strong>Generos:</strong> ' + generosTexto + '</p>';
</script>
```

- `window.location.search` → es la parte de la URL después del `?` (los parámetros).
- `URLSearchParams` → convierte esa parte en algo fácil de leer.
- `params.get('nombre')` → trae el valor del parámetro `nombre`.
- `|| 'No completado'` → si el valor no existe, usa el texto de reemplazo (operador "o").
- `params.getAll('genero')` → los checkbox con el mismo `name` se repiten, así que se traen **todos** en un array.
- `generos.join(', ')` → une los elementos del array con comas para mostrarlos.

### Limitación de esta etapa (importante)

Los datos **no se guardan en ningún lado**: se pierden apenas cerrás la página. Además, cualquiera puede verlos en la URL. En las próximas etapas eso va a cambiar.

---

## 3. Etapa 1 - PHP sin base de datos

> Carpeta: `etapa1/`

### Qué es

El mismo estilo de formulario, pero ahora de **personas (contacto)** y **procesado por PHP**. El formulario envía los datos a un archivo PHP que los muestra en una tabla. Todavía **no se guardan** en base de datos.

### Archivos

| Archivo           | Para qué sirve                                                                                                                               |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `index.php`     | Formulario con 12 campos (nombre, apellido, DNI, CUIT, fecha, email, teléfono, dirección, ciudad, provincia, código postal, observaciones) |
| `resultado.php` | Recibe los datos por POST y los muestra en una tabla                                                                                          |

### Conceptos que se aprenden

- Qué significa que una página sea `.php` (se ejecuta en el servidor).
- Método **POST** vs **GET**.
- **Superglobales** `$_POST` y `$_SERVER`.
- `htmlspecialchars()` para prevenir **inyección de código (XSS)**.
- **Bootstrap 5** para darle estilo sin escribir CSS a mano.

### Bloques de código significativos

#### 1) El formulario con Bootstrap (index.php)

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
```

Con esa sola línea ya tenés **todo Bootstrap** (botones, tarjetas, grillas, tablas). Bootstrap se descarga desde un CDN (un servidor que reparte las librerías). También podrías guardar el archivo `.css` en tu proyecto y enlazarlo igual.

```html
<div class="container py-5">            <!-- centra y agrega márgenes -->
  <div class="row justify-content-center">
    <div class="col-md-8">              <!-- ancho de 8 de 12 columnas -->
      <div class="card shadow">         <!-- tarjeta con sombra -->
        <div class="card-header bg-primary text-white text-center">
          <h2>Formulario de Contacto</h2>
        </div>
        <div class="card-body">
          <form action="resultado.php" method="POST">
```

- La **grilla de Bootstrap** tiene 12 columnas. `col-md-6` significa "en pantallas medianas o más grandes, ocupá la mitad del ancho". Así se ponen dos campos en la misma fila.
- `card`, `card-header`, `card-body` → componentes de tarjeta de Bootstrap.
- `bg-primary`, `text-white`, `text-center` → clases de color y alineación ya hechas.

```html
<div class="col-md-6 mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" class="form-control" id="nombre" name="nombre" required>
</div>
```

- `class="form-control"` → es la clase de Bootstrap para que los inputs se vean prolijos.
- `required` → el navegador no deja enviar si el campo está vacío.
- **`name="nombre"`** → esta es la clave con la que PHP va a buscar el valor: `$_POST['nombre']`.

> `action="resultado.php"` y `method="POST"` son la frase clave: *"cuando el usuario envíe, llevá los datos a `resultado.php` usando el método POST"*.

#### 2) Recibir los datos en PHP (resultado.php)

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST'):
?>
```

- `$_SERVER` es una **superglobal** con datos del servidor y de la petición.
- `$_SERVER['REQUEST_METHOD']` dice **cómo llegaron los datos**: `POST` (formulario enviado) o `GET` (se accedió por la URL).
- Con el `if` controlamos que solo muestre datos si el formulario fue realmente enviado.

```php
<td><?php echo htmlspecialchars($_POST['nombre']); ?></td>
```

- `$_POST` es la **superglobal** que contiene todos los datos enviados por POST. Es un **array asociativo**: la clave es el `name` del campo y el valor es lo que escribió el usuario. Ejemplo: `$_POST['nombre']` → `"Juan"`.
- `htmlspecialchars()` convierte caracteres especiales como `<` en `&lt;`. ¿Por qué? Si el usuario escribe `<script>alert('hack')</script>`, sin esta función el navegador **lo ejecutaría**. Con `htmlspecialchars()` el navegador lo muestra como texto plano. Esto evita el ataque **XSS** (inyección de código).

```php
<td><?php echo nl2br(htmlspecialchars($_POST['observaciones'])); ?></td>
```

- `nl2br()` convierte los saltos de línea (`\n`) en `<br>`. Sin esto, el texto del `textarea` se vería todo junto en una sola línea.
- Notá cómo las funciones se pueden **combinar**: primero se limpia el texto (XSS) y después se arreglan los saltos de línea.

```php
<?php else: ?>
    <div class="alert alert-warning">No se recibieron datos. <a href="index.php">Volver al formulario</a></div>
<?php endif; ?>
```

- Si alguien entra a `resultado.php` directo (sin enviar el formulario), se muestra un aviso. `alert alert-warning` es el estilo de "advertencia" de Bootstrap.

### La diferencia clave con la Etapa 0

- En la Etapa 0 el **navegador** leía los datos con JavaScript (`URLSearchParams`).
- En la Etapa 1 los datos viajan "escondidos" en el **cuerpo de la petición** (POST) y los lee el **servidor** con `$_POST`. La URL queda limpia.

---

## 4. Etapa 2 - Formulario con base de datos MySQL (detallado)

> Carpeta: `etapa2-BD/`

### Qué es

El salto grande: los datos que el usuario carga **se guardan para siempre** en una base de datos MySQL, y después se muestran **todos los registros** en una tabla con botón de eliminar.

### Archivos

| Archivo                     | Para qué sirve                                                   |
| --------------------------- | ----------------------------------------------------------------- |
| `index.php`               | El formulario (igual al de la Etapa 1)                            |
| `procesando.php`          | Archivo**sin HTML**: guarda o elimina en la BD y redirige   |
| `resultado.php`           | Muestra**TODAS** las personas guardadas con botón eliminar |
| `bd/gestionBaseDatos.php` | Conexión PDO + funciones CRUD                                    |
| `bd/script.sql`           | Script que crea la base de datos y la tabla                       |

### Flujo de la aplicación

```
index.php (formulario)
    |
    ▼ POST
procesando.php (guarda en MySQL)
    |
    ▼ header("Location: resultado.php")
resultado.php (muestra todos los registros)
```

> **¿Por qué un `procesando.php` intermedio?** Porque el formulario primero debe **guardar** los datos y después **mostrarlos**. Si el formulario apuntara directo a `resultado.php`, los datos nunca se guardarían. Separar las tareas hace el código más ordenado.

### Conceptos que se aprenden

- Qué es una **base de datos** y una **tabla** (filas y columnas).
- **SQL**: `CREATE TABLE`, `INSERT`, `SELECT`, `DELETE`.
- Conexión a MySQL con **PDO**.
- **Prepared statements** (protección contra inyección SQL).
- Redirección con `header("Location: ...")`.
- `foreach`, `count()`, arrays asociativos.
- Formularios con **campo oculto** (`hidden`) para mandar acciones.

---

### 4.1 El script SQL (bd/script.sql)

Antes de poder guardar datos, hay que crear la base de datos. Eso lo hace este archivo. Se ejecuta una sola vez (desde phpMyAdmin, pestaña SQL).

```sql
CREATE DATABASE IF NOT EXISTS contactos;
USE contactos;

CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    observaciones TEXT
);
```

**Explicación de lo importante:**

| Fragmento SQL                               | Qué significa                                                                                                                                            |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `CREATE DATABASE IF NOT EXISTS contactos` | Crea la base`contactos` si no existe. `IF NOT EXISTS` evita un error si ya está creada                                                               |
| `USE contactos`                           | "Entrá" a esa base para trabajar en ella                                                                                                                 |
| `CREATE TABLE IF NOT EXISTS personas`     | Crea la tabla`personas`                                                                                                                                 |
| `id INT AUTO_INCREMENT PRIMARY KEY`       | Columna id: número entero (`INT`) que **se autoincrementa** (1, 2, 3...) y es la **clave primaria** (identifica cada fila de forma única) |
| `nombre VARCHAR(100)`                     | Texto de hasta**100 caracteres**. `VARCHAR(n)` es un texto "con medida"                                                                           |
| `NOT NULL`                                | La columna**no puede quedar vacía** (es obligatoria)                                                                                               |
| `fecha_nacimiento DATE`                   | Guarda fechas con formato`AAAA-MM-DD` (año-mes-día)                                                                                                   |
| `observaciones TEXT`                      | Texto largo sin límite fijo. No tiene`NOT NULL`: es opcional                                                                                           |

**Datos útiles para elegir el tipo de columna:**

- **`INT`** → números enteros (id, cantidades).
- **`VARCHAR(n)`** → textos cortos con límite (`DNI`, `CUIT`, `email`, teléfono...). El DNI se guarda como texto porque puede llevar puntos o guiones.
- **`DATE`** → fechas.
- **`TEXT`** → textos largos (observaciones, descripciones).
- **`DECIMAL(a,b)`** → números con decimales (latitud, longitud, precios).

> La tabla es como una **planilla de Excel**: las columnas son los campos fijos y cada fila es una persona cargada.

---

### 4.2 La conexión a MySQL con PDO (bd/gestionBaseDatos.php)

Este archivo es la "capa de datos". Todas las funciones que tocan MySQL viven acá. Cualquier página puede usarlas con `require_once 'bd/gestionBaseDatos.php';`.

#### La conexión: función `obtenerConexion()`

```php
function obtenerConexion() {
    // Datos de conexion a MySQL - CAMBIAR segun tu instalacion
    $host = "localhost";       // Servidor (generalmente localhost)
    $usuario = "root";         // Usuario de MySQL
    $contrasena = "";          // Contrasena de MySQL (vacio por defecto en XAMPP)
    $baseDatos = "contactos";  // Nombre de la base de datos

    try {
        // Creamos la conexion PDO con manejo de errores mediante try/catch
        $conexion = new PDO(
            "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
            $usuario,
            $contrasena
        );
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexion: " . $e->getMessage());
    }
}
```

**Qué hace cada parte:**

- `new PDO("mysql:host=...;dbname=...;charset=utf8mb4", usuario, contraseña)` → crea un **objeto de conexión** PDO. PDO (**PHP Data Objects**) es la clase de PHP para conectarse a bases de datos de forma segura.
  - El primer parámetro se llama **DSN**: es la "dirección" de la base (qué tipo de BD, dónde está, cuál usar). `charset=utf8mb4` le dice que los textos vienen en UTF-8 (para que las tildes y la ñ funcionen bien).
- `setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)` → configura PDO para que, ante un error, **lance una excepción** (un aviso de error capturable). Sin esto, los errores de MySQL pasarían "silenciosos".
- `try { ... } catch (PDOException $e) { ... }` → estructura para **manejar errores**: PHP intenta (`try`) conectar; si falla, va al `catch` y muestra el mensaje (`die()` detiene el script). Así el usuario ve un mensaje claro en vez de un pantallazo de error.
- `return $conexion;` → devuelve el objeto de conexión listo para usar en las consultas.

> **Muy importante:** cuando uses XAMPP en tu PC, las credenciales suelen ser `root` con contraseña vacía. En otros entornos (un hosting, un servidor escolar) vas a tener que **cambiarlas**.

#### Insertar con prepared statements: función `insertarPersona()`

```php
function insertarPersona($conexion, $datos) {
    // La consulta SQL usa ? como placeholders (marcadores de posicion)
    $sql = "INSERT INTO personas (nombre, apellido, dni, ...)
            VALUES (?, ?, ?, ...)";

    // Preparamos la consulta (PDO la analiza pero no la ejecuta aun)
    $stmt = $conexion->prepare($sql);

    // execute() con un array une los valores a los placeholders
    $resultado = $stmt->execute([
        $datos['nombre'],
        $datos['apellido'],
        // ... y así con todos los campos
    ]);

    return $resultado;
}
```

**El patrón de los "prepared statements" (consultas preparadas) en 3 pasos:**

1. **Escribís la consulta SQL con `?` en vez de los valores reales.** Los `?` son "marcadores de posición": el lugar donde después irá cada dato.
2. **`$conexion->prepare($sql)`** → PDO analiza la consulta y la prepara (pero **no la ejecuta** todavía).
3. **`$stmt->execute([valor1, valor2, ...])`** → se pasan los valores **en el mismo orden** que los `?`. PDO los "escapa" (los protege) automáticamente.

**¿Por qué es tan importante este patrón?**

Es la defensa contra la **inyección SQL**. Si se armaran las consultas pegando texto (ej. `"SELECT * FROM personas WHERE nombre = '" . $_POST['nombre'] . "'"`), un usuario malintencionado podría escribir algo como:

```sql
' OR '1'='1
```

y **modificar la consulta original** para que devuelva o borre lo que él quiera. Con los `?`, el texto del usuario se trata como **dato**, nunca como parte de la instrucción SQL.

> **Regla de oro:** en este proyecto, **todas** las consultas que llevan datos del usuario usan `?` y `execute([...])`. Nunca se pegan valores con `.` dentro del SQL.

#### Leer todos los registros: función `obtenerTodasLasPersonas()`

```php
function obtenerTodasLasPersonas($conexion) {
    $sql = "SELECT * FROM personas ORDER BY id DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

- `SELECT * FROM personas` → "traeme **todas** las columnas de todas las filas" de la tabla.
- `ORDER BY id DESC` → ordena de mayor a menor por id: **las más nuevas primero**.
- `fetchAll(...)` → trae **todos** los registros de una vez, como un **array**.
- `PDO::FETCH_ASSOC` → cada fila se convierte en un **array asociativo**: las claves son los nombres de las columnas. Ejemplo:

```php
[
    0 => ['id' => 5, 'nombre' => 'Juan', 'apellido' => 'Perez', ...],
    1 => ['id' => 4, 'nombre' => 'Ana',  'apellido' => 'Lopez', ...],
]
```

Por eso después en el código se accede con `$persona['nombre']`, `$persona['apellido']`, etc.

#### Eliminar: función `eliminarPersona()`

```php
function eliminarPersona($conexion, $id) {
    $sql = "DELETE FROM personas WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}
```

- `DELETE FROM personas` → borra filas de la tabla.
- `WHERE id = ?` → **solo** la fila cuyo `id` coincida. Sin `WHERE`, ¡se borrarían **todas** las personas! Siempre hay que filtrar por el id.

---

### 4.3 El archivo intermedio: procesando.php

Este archivo **no tiene ni una línea de HTML**. Su trabajo es puramente de lógica: recibir datos, guardar/eliminar en la BD y redirigir.

```php
<?php
// 1) Traemos las funciones de la base de datos
require_once 'bd/gestionBaseDatos.php';

// 2) Abrimos la conexion a MySQL
$conexion = obtenerConexion();

// 3) Verificamos que los datos llegaron por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CASO 1: ELIMINAR una persona
    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        $id = (int) $_POST['id'];            // convertimos a numero entero
        eliminarPersona($conexion, $id);
        header("Location: resultado.php");   // redirigimos
        exit;
    }

    // CASO 2: INSERTAR una nueva persona
    $datos = [
        'nombre'           => trim($_POST['nombre']),
        'apellido'         => trim($_POST['apellido']),
        // ... todos los campos
        'observaciones'    => trim($_POST['observaciones']),
    ];

    insertarPersona($conexion, $datos);
    header("Location: resultado.php");
    exit;
}
```

**Conceptos clave de este archivo:**

- `require_once` → incluye el archivo una sola vez. Si no existe, el script **se detiene** con error (a diferencia de `include` que avisa pero sigue).
- `isset($_POST['accion'])` → pregunta **¿existe** ese campo? Es la forma segura de comprobar que algo vino del formulario antes de usarlo.
- `(int) $_POST['id']` → el "cast" **convierte** el texto a número entero. Si el usuario mandara `"100; DROP TABLE..."`, el `(int)` lo deja en `100` (corta todo lo que no sea número). Es otra capa de seguridad.
- `trim($_POST['nombre'])` → **quita espacios** al inicio y al final. Así `" Juan "` queda `"Juan"`.
- `header("Location: resultado.php")` → envía un **encabezado HTTP** que le dice al navegador "andá a otra página". Es la forma correcta de redirigir.
- `exit` → **detiene** el script. Es obligatorio después de `header("Location: ...")` para que no se ejecute más código.

> **Regla de oro del `header`:** `header("Location: ...")` **debe ir antes de cualquier salida HTML** (antes de un `echo` o de cerrar el `<?php`). Si no, PHP tira el error *"headers already sent"*.

---

### 4.4 La página de resultados: resultado.php

A diferencia de la Etapa 1 (que mostraba una sola persona), acá se consultan **todas** y se muestran en una tabla.

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion);
?>
```

El `<?php ... ?>` de arriba se ejecuta **antes** de escribir el HTML. Por eso, cuando el HTML necesita los datos, `$personas` ya está cargada. Este es el patrón clásico de PHP: **primero lógica, después vista**.

#### Contar los registros

```html
<span class="badge bg-light text-dark"><?php echo count($personas); ?> registros</span>
```

- `count($personas)` → devuelve **cuántos elementos** tiene el array (cuántas personas hay). Se muestra en una "insignia" (`badge`) de Bootstrap.

#### El foreach que dibuja las filas

```php
<?php if (empty($personas)): ?>
    <div class="alert alert-info">No hay personas registradas aun.</div>
<?php else: ?>
    <?php foreach ($personas as $persona): ?>
    <tr>
        <td><?php echo $persona['id']; ?></td>
        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
        <!-- ... -->
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
```

- `empty($personas)` → `true` si el array está vacío. Si no hay registros, mostramos un aviso en vez de una tabla vacía.
- `foreach ($personas as $persona)` → **recorre** el array. En cada vuelta, `$persona` es una persona (array asociativo). La sintaxis `foreach (...): ... endforeach;` es la forma alternativa de escribir el bloque (más prolija cuando se mezcla con HTML).
- Cada `$persona['...']` imprime el dato de una columna. Notá que los textos se pasan por `htmlspecialchars()` (prevención de XSS), pero el `id` no hace falta (es un número controlado).

#### El formulario de eliminar (oculto en cada fila)

```html
<form action="procesando.php" method="POST" class="d-inline"
      onsubmit="return confirm('Seguro que deseas eliminar esta persona?');">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>
```

Este es un truco muy usado: **un formulario invisible con campos ocultos** (`hidden`) que el usuario no ve pero que se envían igual.

- `name="accion" value="eliminar"` → le dice a `procesando.php` qué operación hacer (mismo archivo sirve para insertar y eliminar).
- `name="id"` → envía el id de la fila a borrar (cada fila tiene su propio formulario con su propio id).
- `onsubmit="return confirm('...')"` → **JavaScript**: antes de enviar, pregunta "¿Seguro?". Si el usuario cancela, el formulario **no** se envía (`return false`).

> Como el formulario no tiene botón visible más que "Eliminar", al presionarlo el navegador envía POST con `accion=eliminar` e `id=X`. `procesando.php` detecta eso con `isset($_POST['accion'])`.

---

## 5. Etapa 3 - Dos tablas relacionadas con clave foránea (detallado)

> Carpeta: `etapa3-BD-tablas/`

### Qué es

Ahora la base de datos tiene **DOS tablas** relacionadas entre sí. Esto se llama **normalización**: ordenar los datos para que no se repitan.

```
ciudades  (una ciudad tiene: nombre, provincia, latitud, longitud,
                             codigo_postal, descripcion, fecha de fundacion)
    ▲
    │ 1 ── n
    │
personas   (una persona nace en UNA ciudad)
```

La relación se representa con una **CLAVE FORÁNEA** (`ciudad_id`) en la tabla `personas`.

### ¿Por qué se hace esto? (la idea de la normalización)

En la Etapa 2, cada persona escribía su ciudad **como texto libre**:

```
personas:
| nombre | ciudad          |
| Juan   | cordoba         |
| Ana    | Cordoba         |
| Luis   | Cba             |
```

Problemas: datos repetidos (`cordoba`, `Cordoba`, `Cba` son la misma ciudad), incoherentes, y si la ciudad cambia de nombre hay que corregir todas las personas. Además el `codigo_postal` era un dato de la persona, pero en realidad **pertenece al lugar**.

En la Etapa 3, la ciudad se carga **una sola vez** en su tabla y las personas solo guardan el **id** de esa ciudad:

```
ciudades:
| id | nombre   | provincia  | codigo_postal |
| 1  | Cordoba  | Cordoba    | 5000          |
| 2  | Rosario  | Santa Fe   | 2000          |

personas:
| id | nombre | ciudad_id |
| 1  | Juan   | 2         |   <-- Juan nació en la ciudad con id 2 (Rosario)
| 2  | Ana    | 1         |   <-- Ana nació en la ciudad con id 1 (Cordoba)
```

Ventajas:

- El nombre de la ciudad se escribe **una sola vez** (no se repite en cada persona).
- No hay errores de escritura ("cordoba" vs "Cordoba").
- Si la ciudad cambia (de nombre, de código postal), se corrige en **un solo lugar** y todas las personas se actualizan automáticamente.

### Archivos

| Archivo                     | Para qué sirve                                                                      |
| --------------------------- | ------------------------------------------------------------------------------------ |
| `index.php`               | Portada con estadísticas (cantidad de personas y ciudades) y accesos                |
| `formPersona.php`         | Formulario de persona con**select** de ciudad (elige entre las existentes)     |
| `procesando.php`          | Sin HTML: inserta o elimina personas                                                 |
| `resultado.php`           | Tabla de personas con su ciudad (vía JOIN) y buscador por nombre                    |
| `ciudades.php`            | Lista de ciudades con editar/eliminar y buscador por nombre                          |
| `formCiudad.php`          | Formulario de alta/edición de ciudades (sirve para las dos cosas)                   |
| `gestionCiudades.php`     | Sin HTML: procesa crear, actualizar o eliminar ciudades                              |
| `bd/gestionBaseDatos.php` | Conexión PDO + funciones CRUD, búsqueda (LIKE) y conteo para ambas tablas          |
| `bd/script.sql`           | Crea la BD`contactos3` con las tablas `ciudades` y `personas` + clave foránea |

### Flujo de la aplicación

```
index.php (portada: estadisticas)
    |
    ├──> formPersona.php (formulario persona, elige ciudad del select)
    |        |
    |        ▼ POST
    |     procesando.php (guarda persona con ciudad_id en MySQL)
    |        |
    |        ▼ header("Location: resultado.php")
    |     resultado.php (muestra personas con su ciudad via JOIN + buscar)
    |
    └──> ciudades.php (gestion de ciudades + buscar)
            |
            ├── formCiudad.php (alta/edicion ciudad)
            |     |
            |     ▼ POST
            |  gestionCiudades.php (INSERT / UPDATE / DELETE)
            |     |
            |     ▼ header("Location: ciudades.php")
            |  ciudades.php (lista todas las ciudades)
```

### Conceptos que se aprenden

- Clave primaria (PRIMARY KEY) y **clave foránea** (FOREIGN KEY).
- Normalización: eliminar datos repetidos, usar IDs en vez de texto.
- **JOIN** para combinar datos de dos tablas en una consulta.
- CRUD completo (Create, Read, **Update**, Delete) para ciudades.
- Formulario que sirve para **alta y edición** a la vez (según venga `?id=` o no).
- Verificación de la clave foránea: no se puede borrar una ciudad que tenga personas.
- Búsquedas con **LIKE** y comodín `%` (GET, porque es una consulta, no un guardado).
- Conteo con **COUNT(*)** para estadísticas.
- Validación de datos en PHP para evitar errores de MySQL.

---

### 5.1 El script SQL con la clave foránea (bd/script.sql)

```sql
CREATE DATABASE IF NOT EXISTS contactos3;
USE contactos3;

CREATE TABLE IF NOT EXISTS ciudades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,
    latitud DECIMAL(9,6),        -- 3 enteros + 6 decimales
    longitud DECIMAL(10,6),      -- 4 enteros + 6 decimales
    codigo_postal VARCHAR(10),   -- pertenece a la CIUDAD, no a la persona
    descripcion TEXT,
    fecha_fundacion DATE
);

CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    ciudad_id INT,               -- CLAVE FORANEA: solo guarda el id
    observaciones TEXT,
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
```

**Lo nuevo de esta etapa:**

- **`DECIMAL(9,6)`** → un número decimal que puede tener hasta 9 dígitos en total y 6 decimales. Es perfecto para latitud (-90 a 90) y longitud (-180 a 180).
- **`ciudad_id INT`** → reemplaza al campo de texto `ciudad` de la Etapa 2. Ahora solo se guarda un **número**: el id de la ciudad donde nació la persona.
- **`FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)`** → la **clave foránea**. Le dice a MySQL: *"la columna `ciudad_id` solo puede contener valores que existan en la columna `id` de la tabla `ciudades`"*.
  - Si intentás guardar una persona con un `ciudad_id` que no existe → **error**.
  - Si intentás **borrar una ciudad** que tiene personas nacidas en ella → **error**. Por eso el programa verifica antes con la función `hayPersonasEnCiudad()`.
  - La base de datos misma **garantiza** que la relación siempre sea válida.

> **Analogía:** la clave primaria es como el DNI de cada fila (único e irrepetible). La clave foránea es como el campo "padre/madre" de un formulario: no puede ser un número inventado, tiene que corresponder a una fila real de la otra tabla.

---

### 5.2 La capa de datos con JOIN y LIKE (bd/gestionBaseDatos.php)

#### El JOIN que une personas con su ciudad

```php
function obtenerTodasLasPersonas($conexion, $busqueda = '') {
    $sql = "SELECT p.*, c.nombre AS ciudad_nombre, c.provincia AS ciudad_provincia
            FROM personas p
            JOIN ciudades c ON p.ciudad_id = c.id";

    // Si hay termino de busqueda, agregamos un WHERE con LIKE
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

**Explicación del JOIN:**

- `personas p` → le ponemos un **alias** (`p`) a la tabla personas para escribir menos. Lo mismo `ciudades c`.
- `JOIN ciudades c ON p.ciudad_id = c.id` → es el corazón de todo: **une** cada persona con la ciudad cuyo `id` sea igual a su `ciudad_id`. Traducción: *"para cada persona, buscá la ciudad donde nació"*.
- `c.nombre AS ciudad_nombre` → trae el nombre de la ciudad y le pone el **alias** `ciudad_nombre`. Por eso después en el HTML se usa `$persona['ciudad_nombre']`. Si no hubiera alias, se llamaría `nombre` y se confundiría con el nombre de la persona.

**¿Qué pasa con los datos que la persona ya no guarda?** En la Etapa 2, `personas` tenía `ciudad`, `provincia` y `codigo_postal`. Ahora esos datos salen de la tabla `ciudades` vía JOIN, así que no están repetidos.

**Explicación de la búsqueda con LIKE:**

- `LIKE ?` con valor `'%' . $busqueda . '%'` → busca textos que **contengan** el término en cualquier posición.
- `%` es un **comodín** (comodín = "cualquier cosa"). `'%jul%'` matchea "Julian", "Julia", "Julián", etc.
- La búsqueda es **opcional**: si `$busqueda` está vacía, el `if` no agrega el `WHERE` y se traen todas las personas. El array `$parametros` se pasa siempre a `execute()` (vacío o no), y PDO lo maneja bien.

#### COUNT(*) para las estadísticas de la portada

```php
function contarPersonas($conexion) {
    $sql = "SELECT COUNT(*) FROM personas";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}
```

- `COUNT(*)` → cuenta **cuántas filas** hay en la tabla. Devuelve un solo número.
- `fetchColumn()` → a diferencia de `fetchAll()` (que trae todas las filas), acá se usa `fetchColumn()` porque la consulta devuelve **un solo valor** (el conteo).

#### El CRUD de ciudades (crear, leer, actualizar, eliminar)

```php
function insertarCiudad($conexion, $datos) {
    $sql = "INSERT INTO ciudades (nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([...]);
}

function obtenerCiudadPorId($conexion, $id) {
    $sql = "SELECT * FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);   // fetch = UNA fila
}

function actualizarCiudad($conexion, $id, $datos) {
    $sql = "UPDATE ciudades
            SET nombre = ?, provincia = ?, latitud = ?, longitud = ?,
                codigo_postal = ?, descripcion = ?, fecha_fundacion = ?
            WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([...datos..., $id]);
}

function eliminarCiudad($conexion, $id) {
    $sql = "DELETE FROM ciudades WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}
```

**Lo importante de cada una:**

- `insertarCiudad()` → mismo patrón `INSERT ... VALUES (?, ...)` de la Etapa 2.
- `obtenerCiudadPorId()` → usa **`fetch()`** (no `fetchAll()`): trae **una sola fila**, la de la ciudad con ese id. Devuelve `null` si no existe. Se usa para precargar los datos en el formulario de edición.
- `actualizarCiudad()` → es el **UPDATE** (la "U" de CRUD que faltaba): cambia los valores de la fila `WHERE id = ?`. Notá que el `$id` va **al final** del array de execute, porque es el último `?` de la consulta.
- `eliminarCiudad()` → `DELETE WHERE id = ?`. Pero ojo: si la ciudad tiene personas, MySQL daría error por la clave foránea. Por eso antes se verifica con `hayPersonasEnCiudad()`.

#### La verificación de la clave foránea

```php
function hayPersonasEnCiudad($conexion, $id) {
    $sql = "SELECT COUNT(*) FROM personas WHERE ciudad_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);

    $cantidad = $stmt->fetchColumn();
    return $cantidad > 0;
}
```

- Cuenta cuántas personas nacieron en la ciudad `$id`.
- Si el resultado es **mayor que 0** (`return $cantidad > 0`), la ciudad **no se puede borrar**. El programa muestra un mensaje de error en lugar de intentar el DELETE (que fallaría igual, pero con un mensaje feo).

---

### 5.3 El select de ciudad que se llena desde la BD (formPersona.php)

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$ciudades = obtenerTodasLasCiudades($conexion);
?>
```

Antes de escribir el HTML, se consultan **todas las ciudades** y se guardan en `$ciudades`.

```html
<label for="ciudad_id" class="form-label">Lugar de Nacimiento</label>
<select class="form-select" id="ciudad_id" name="ciudad_id" required>
    <option value="">-- Seleccionar ciudad --</option>
    <?php foreach ($ciudades as $ciudad): ?>
        <option value="<?php echo $ciudad['id']; ?>">
            <?php echo htmlspecialchars($ciudad['nombre']); ?>
            (<?php echo htmlspecialchars($ciudad['provincia']); ?>)
        </option>
    <?php endforeach; ?>
</select>
```

- El **`foreach` dibuja un `<option>` por cada ciudad** de la base de datos.
- `value="<?php echo $ciudad['id']; ?>"` → lo que se **envía** es el **id** de la ciudad (clave foránea). El usuario no ve el id, ve el nombre y la provincia.
- `name="ciudad_id"` → en `procesando.php` se recibe como `$_POST['ciudad_id']`.
- Si no hay ciudades, arriba se muestra un aviso: "Primero debe cargar ciudades". Así el select nunca queda vacío sin explicación.

> **Beneficio:** el usuario **no puede** escribir "cordoba", "Cordoba" o "Cba". Solo puede elegir entre las ciudades ya cargadas. Se acabaron los datos repetidos e incoherentes.

---

### 5.4 Procesar con validación (gestionCiudades.php)

Este archivo maneja **tres acciones** para ciudades: crear, actualizar y eliminar. Usa la técnica del campo oculto `accion` (igual que la Etapa 2, pero con más casos).

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];   // "crear", "actualizar" o "eliminar"

    // CASO 1: ELIMINAR
    if ($accion === 'eliminar') {
        $id = (int) $_POST['id'];

        // Si la ciudad tiene personas, NO se elimina y se muestra un mensaje
        if (hayPersonasEnCiudad($conexion, $id)) {
            header("Location: ciudades.php?error=1");
            exit;
        }
        eliminarCiudad($conexion, $id);
        header("Location: ciudades.php");
        exit;
    }

    // Los campos opcionales vacios se guardan como NULL
    $latitud  = ($_POST['latitud']  !== '') ? $_POST['latitud']  : null;
    $longitud = ($_POST['longitud'] !== '') ? $_POST['longitud'] : null;

    // VALIDACION: la latitud debe estar entre -90 y 90, la longitud entre -180 y 180
    $error = null;
    if ($latitud !== null && (!is_numeric($latitud) || $latitud < -90 || $latitud > 90)) {
        $error = 'La latitud debe ser un numero entre -90 y 90 (ej: -31.416667)';
    } elseif ($longitud !== null && (!is_numeric($longitud) || $longitud < -180 || $longitud > 180)) {
        $error = 'La longitud debe ser un numero entre -180 y 180 (ej: -64.183333)';
    }

    // Si hay error, volvemos al formulario con el mensaje en la URL
    if ($error) {
        header("Location: formCiudad.php?error=" . urlencode($error));
        exit;
    }

    $datos = [
        'nombre'          => trim($_POST['nombre']),
        'provincia'       => trim($_POST['provincia']),
        'latitud'         => $latitud,
        'longitud'        => $longitud,
        'codigo_postal'   => trim($_POST['codigo_postal']),
        'descripcion'     => trim($_POST['descripcion']),
        'fecha_fundacion' => ($_POST['fecha_fundacion'] !== '') ? $_POST['fecha_fundacion'] : null,
    ];

    if ($accion === 'actualizar') {
        $id = (int) $_POST['id'];
        actualizarCiudad($conexion, $id, $datos);
    } else {
        insertarCiudad($conexion, $datos);
    }

    header("Location: ciudades.php");
    exit;
}
```

**Conceptos nuevos:**

- **Una variable, tres caminos.** El mismo archivo decide qué hacer según el valor de `$accion` (que llega en un campo oculto del formulario).
- **`$_POST['latitud'] !== '' ? ... : null`** → es un **operador ternario** (una forma corta de escribir `if/else` en una línea): si el campo vino vacío, se guarda `null` (sin valor) en la base; si vino con algo, se guarda ese valor.
- **Validación de datos en PHP.** Antes de mandar a MySQL, se comprueba con `is_numeric()` y con los límites (-90/90 y -180/180). ¿Por qué? Si se mandara un valor gigante, MySQL tira el error *"Out of range value"* (error 500). Validar en PHP evita ese error y le da al usuario un **mensaje claro**.
- **`urlencode($error)`** → el mensaje de error viaja en la URL (`?error=...`), pero hay que **codificarlo** porque tiene espacios y caracteres especiales que la URL no acepta tal cual.
- **Mismo archivo para crear y editar:** el `if ($accion === 'actualizar')` elige entre `UPDATE` (con id) e `INSERT` (sin id).

---

### 5.5 El formulario que sirve para alta y edición (formCiudad.php)

Este es uno de los trucos más útiles: **un solo formulario para crear y editar**.

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();

$ciudad = null;
$esEdicion = false;

// Si la URL trae ?id=, estamos editando una ciudad existente
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $ciudad = obtenerCiudadPorId($conexion, $id);
    $esEdicion = true;
}
?>
```

- `isset($_GET['id'])` → pregunta si la URL trae un parámetro `?id=`. Si lo trae (ej. `formCiudad.php?id=3`), es **edición**: se carga la ciudad desde la BD. Si no lo trae, es **alta** (ciudad nueva).
- `$esEdicion` → una bandera (true/false) que el resto del HTML usa para cambiar textos y precargar valores.

```html
<form action="gestionCiudades.php" method="POST">
    <?php if ($esEdicion): ?>
        <!-- Campo oculto con el id de la ciudad y la accion "actualizar" -->
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
    <?php else: ?>
        <input type="hidden" name="accion" value="crear">
    <?php endif; ?>

    <input type="text" class="form-control" name="nombre" required
           value="<?php echo $esEdicion ? htmlspecialchars($ciudad['nombre']) : ''; ?>">
```

- En modo edición se mandan dos campos ocultos: `accion=actualizar` e `id=<id de la ciudad>`. En modo alta, solo `accion=crear`.
- **`value="..."`** en los inputs precarga los datos de la ciudad cuando es edición. El `htmlspecialchars()` también se aplica acá para que los textos con `"` o `<` no rompan el atributo.

---

### 5.6 La tabla de ciudades con editar y eliminar (ciudades.php)

```html
<!-- EDITAR: enlace con ?id=X -->
<a href="formCiudad.php?id=<?php echo $ciudad['id']; ?>"
   class="btn btn-warning btn-sm">Editar</a>

<!-- ELIMINAR: envia accion=eliminar a gestionCiudades.php -->
<form action="gestionCiudades.php" method="POST" class="d-inline"
      onsubmit="return confirm('Seguro que deseas eliminar esta ciudad?');">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" value="<?php echo $ciudad['id']; ?>">
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>
```

- **Editar** se hace con un simple **enlace** `?id=X` (GET, porque es "pedir el formulario"). `formCiudad.php` detecta el id y carga los datos.
- **Eliminar** usa POST con campos ocultos (porque modifica la base). `gestionCiudades.php` verificará antes si la ciudad tiene personas.

```php
<?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
    <div class="alert alert-danger">
        No se puede eliminar la ciudad porque hay personas registradas que nacieron en ella.
    </div>
<?php endif; ?>
```

- Cuando `gestionCiudades.php` redirige con `ciudades.php?error=1`, esta página muestra el mensaje. Es la forma de "pasarse un mensaje" entre páginas a través de la URL.

---

### 5.7 El buscador en resultado.php

```html
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

- `method="GET"` → la búsqueda viaja en la URL: `resultado.php?busqueda=juan`. Usamos **GET** (no POST) porque buscar es una **consulta**, no un guardado: no modifica nada, y además queda el filtro en la URL para poder compartirlo o recargarlo.
- `value="<?php echo htmlspecialchars($busqueda); ?>"` → deja **escrito el término** después de buscar (para que el usuario vea qué buscó).
- En el PHP de arriba:

```php
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$personas = obtenerTodasLasPersonas($conexion, $busqueda);
```

- Si no viene `busqueda` en la URL, se usa `''` (vacío) y se traen todas las personas.
- La función `obtenerTodasLasPersonas()` ya sabe filtrar con LIKE cuando `$busqueda` no está vacía.

---

### 5.8 La portada con estadísticas (index.php)

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$cantidadPersonas = contarPersonas($conexion);
$cantidadCiudades = contarCiudades($conexion);
?>
```

```html
<h1 class="display-3 text-primary mb-0"><?php echo $cantidadPersonas; ?></h1>
<p class="text-muted mb-0">Personas registradas</p>
<a href="resultado.php" class="btn btn-primary w-100">Gestionar personas</a>
```

- Se consultan los **dos** conteos antes del HTML.
- `display-3` es un estilo de letra **muy grande** de Bootstrap, ideal para números de estadística.
- Cada tarjeta muestra el número, un texto y un botón que lleva a la gestión correspondiente.

---

## 6. Glosario de conceptos

| Concepto                          | Qué significa (en simple)                                                                                  |
| --------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| **HTML**                    | Lenguaje que arma la estructura de la página (títulos, inputs, tablas). Lo entiende el navegador          |
| **CSS**                     | Lenguaje de estilos: colores, tamaños, márgenes. Bootstrap es un CSS ya hecho                             |
| **Bootstrap**               | Biblioteca de CSS (y algo de JS) que da diseño listo con clases como`card`, `btn`, `table`           |
| **PHP**                     | Lenguaje del servidor. Crea el HTML "al vuelo" y se conecta a la base de datos                              |
| **MySQL**                   | Motor de base de datos. Guarda los datos de forma permanente                                                |
| **Servidor / Cliente**      | Cliente = navegador (pide). Servidor = Apache + PHP (prepara y responde)                                    |
| **Superglobal**             | Variable de PHP disponible en todo el script:`$_POST`, `$_GET`, `$_SERVER`                            |
| **GET**                     | Método de envío: los datos van en la URL (visible). Sirve para consultas y búsquedas                     |
| **POST**                    | Método de envío: los datos van en el cuerpo del pedido (no se ven). Sirve para guardar/modificar/eliminar |
| **Array asociativo**        | Colección`clave => valor`. Ej: `['nombre' => 'Juan']`. Se accede con `$arr['nombre']`                |
| **foreach**                 | Bucle que recorre un array:`foreach ($personas as $persona) { ... }`                                      |
| **Base de datos**           | Conjunto de tablas. En este proyecto:`contactos` y `contactos3`                                         |
| **Tabla**                   | Conjunto de filas (registros) con las mismas columnas (campos)                                              |
| **Clave primaria (PK)**     | Columna que identifica cada fila de forma única (el`id`)                                                 |
| **Clave foránea (FK)**     | Columna que guarda el id de otra tabla (ej:`ciudad_id` → `ciudades.id`)                                |
| **SQL**                     | Lenguaje para hablarle a la base:`SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE TABLE`           |
| **PDO**                     | Biblioteca de PHP para conectarse a bases. Permite prepared statements                                      |
| **Prepared statement**      | Consulta preparada con`?` que separa el SQL de los datos (anti inyección SQL)                            |
| **Inyección SQL**          | Ataque que mete SQL malicioso dentro de un campo del formulario                                             |
| **XSS**                     | Ataque que mete código`<script>` en un campo. Se evita con `htmlspecialchars()`                        |
| **CRUD**                    | Las 4 operaciones básicas: Create (crear), Read (leer), Update (actualizar), Delete (eliminar)             |
| **JOIN**                    | Combina dos tablas en una consulta según una relación (la clave foránea)                                 |
| **LIKE**                    | Búsqueda de texto parcial, con comodín`%`: `'%jul%'`                                                  |
| **header("Location: ...")** | Redirección HTTP a otra página. Siempre seguida de`exit`                                                |
| **Normalización**          | Ordenar la base: cada dato en su tabla, sin repetir información                                            |

---

## 7. Errores comunes y cómo solucionarlos

| Error / síntoma                                                    | Causa probable                                                     | Solución                                                                                        |
| ------------------------------------------------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| **"Error de conexion: SQLSTATE[HY000] [1045] Access denied"** | Credenciales incorrectas en`obtenerConexion()`                   | Verificá usuario/contraseña en`gestionBaseDatos.php` (XAMPP: `root` y vacío)              |
| **"Unknown database 'contactos'"**                            | No se ejecutó el`script.sql`                                    | Ejecutá`bd/script.sql` en phpMyAdmin (pestaña SQL)                                           |
| **"Table 'contactos.personas' doesn't exist"**                | La tabla no se creó, o se creó en otra base                      | Corré el script completo de nuevo                                                               |
| **"headers already sent"**                                    | Hay un`echo` o espacio/HTML antes de `header("Location: ...")` | Asegurate de que`header()` esté en la primera línea del archivo y no haya nada antes         |
| **Pantalla en blanco**                                        | Error de sintaxis PHP o de conexión silenciado                    | Activá`display_errors` o revisá que todas las `?` del SQL tengan su valor en `execute()` |
| **Página descarga el `.php` en vez de ejecutarlo**         | Apache no está procesando PHP                                     | Encendé Apache en XAMPP y accedé por`http://localhost` (no doble click al archivo)           |
| **"Out of range value for column 'latitud'"**                 | Se mandó un número que no entra en el`DECIMAL`                 | Validá en PHP (como en`gestionCiudades.php`) antes de guardar                                 |
| **No se puede eliminar una ciudad**                           | La ciudad tiene personas nacidas en ella (clave foránea)          | Es**comportamiento correcto**: eliminá primero las personas o cambiá su ciudad           |
| **Datos repetidos o mal escritos en la ciudad**               | Eso pasaba en la Etapa 2 (texto libre)                             | Usá la Etapa 3: la ciudad se elige de un select cargado desde la BD                             |
| **El select de ciudad está vacío**                          | No hay ciudades cargadas todavía                                  | Cargá ciudades primero (botón "Gestionar ciudades" en la portada)                              |
| **El buscador no encuentra nada**                             | Se busca en la columna equivocada                                  | Verificá que el`LIKE` use `p.nombre` (o el campo correcto)                                  |
| **La fecha no se guarda / se guarda mal**                     | Formato incorrecto                                                 | MySQL espera`AAAA-MM-DD`; el input `type="date"` ya lo manda así                            |

---

## 8. Actividades para practicar

### Etapa 0 y 1 (básico)

1. Agregá un nuevo tipo de input a la Etapa 0 (por ejemplo, `type="color"` o `type="range"`) y mostralo en `resultado.html`.
2. Cambiá el `method` del formulario de la Etapa 1 de `POST` a `GET` y probá qué pasa con la URL. ¿Por qué es mejor POST para guardar?
3. Ingresá al formulario de la Etapa 1: `<textarea>` con `<b>hola</b>` en observaciones. Fijate la diferencia entre con y sin `htmlspecialchars()`.

### Etapa 2 (con base de datos)

4. En `bd/gestionBaseDatos.php`, conectate con tus credenciales reales y guardá una persona. Después verificá en phpMyAdmin que la fila exista.
5. Agregá a la tabla de `resultado.php` las columnas que faltan (Provincia, Código Postal, Fecha, Teléfono, Observaciones).
6. Probalo con `php -S localhost:8000` y después con XAMPP/phpMyAdmin. ¿Qué pasa si borrás la base de datos y no la creás de nuevo?

### Etapa 3 (dos tablas)

7. Cargá 3 ciudades y luego 3 personas. Intentá eliminar una ciudad con personas. ¿Qué pasa? Ahora eliminá primero las personas y después la ciudad.
8. Agregá un buscador por **apellido** en `resultado.php` (además del de nombre).
9. Modificá la portada para que muestre, además, cuántas personas hay por cada ciudad (pista: `COUNT(*) ... GROUP BY ciudad_id`).
10. Agregá un botón "Editar" para personas (hoy solo se pueden editar ciudades). Usá como modelo el `formCiudad.php`.

### Todas las etapas (repaso general)

11. Armá una tabla comparando qué archivos hay en cada etapa y qué hace cada uno.
12. Escribí con tus palabras el camino que hace un dato desde que lo escribís en el formulario hasta que queda guardado en MySQL (dibujalo como un diagrama de flujo).
13. Investigá: ¿qué diferencia hay entre `include` y `require_once`? ¿Y entre `mysqli` y `PDO`?

---

## Resumen de las etapas

| Característica               | Etapa 0              | Etapa 1          | Etapa 2                   | Etapa 3                        |
| ----------------------------- | -------------------- | ---------------- | ------------------------- | ------------------------------ |
| Tecnología                   | HTML + JS puro       | PHP + Bootstrap  | PHP + Bootstrap + MySQL   | PHP + Bootstrap + MySQL        |
| Base de datos                 | No                   | No               | Sí                       | Sí                            |
| Tablas                        | -                    | -                | 1 (`personas`)          | 2 (`ciudades`, `personas`) |
| Clave foránea                | No                   | No               | No                        | Sí (`ciudad_id`)            |
| ¿Los datos quedan guardados? | No                   | No               | Sí                       | Sí                            |
| Eliminar                      | No                   | No               | Sí                       | Sí                            |
| Editar (Update)               | No                   | No               | No                        | Sí (ciudades)                 |
| Buscar                        | No                   | No               | No                        | Sí (LIKE)                     |
| Concepto central              | Tipos de input y GET | POST y`$_POST` | PDO + prepared statements | Claves foráneas + JOIN        |

---

*Guía elaborada para el proyecto "Formulario de Contactos" de Aplicaciones Web 6° C. Cada carpeta del proyecto (`etapa0-html`, `etapa1`, `etapa2-BD`, `etapa3-BD-tablas`) contiene el código completo de cada etapa.*
