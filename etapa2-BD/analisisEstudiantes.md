# Guia del Proyecto `etapa2-BD` para Estudiantes

**Formulario de Contacto con PHP + MySQL** — Tu primer contacto con PHP y bases de datos.

---

### Contenido

1. [Que es este proyecto](#1-que-es-este-proyecto)
2. [Mapa del sitio: que archivos hay y donde estan](#2-mapa-del-sitio)
3. [El concepto mas importante: archivos navegables vs librerias](#3-navegables-vs-librerias)
4. [Como PHP se mezcla con HTML](#4-php-mezclado-con-html)
5. [Como mostrar resultados con `echo`](#5-mostrando-resultados-con-echo)
6. [Paso a paso: recorrido por cada archivo](#6-paso-a-paso-por-cada-archivo)
7. [Flujo completo de datos](#7-flujo-completo-de-datos)
8. [Resumen de conceptos clave](#8-resumen-de-conceptos-clave)

---

## 1. Que es este proyecto

Es una aplicacion web sencilla que hace **una sola cosa**: cargar personas en una base de datos MySQL y mostrarlas en una tabla, con la posibilidad de eliminarlas.

Lo que cambia respecto a las etapas anteriores (HTML puro o PHP sin BD) es que ahora los datos **quedan guardados permanentemente** en MySQL. No se pierden al cerrar el navegador.

---

## 2. Mapa del sitio

Asi se ve la estructura completa del proyecto. Observa que hay una **carpeta `bd/`** con archivos que no son paginas web:

```
etapa2-BD/
│
├── index.php                  ← PAGINA: el formulario de carga
├── resultado.php              ← PAGINA: la tabla con todas las personas
├── procesando.php             ← LIBRERIA: recibe datos, guarda en MySQL, redirige
│
├── img/
│   └── mysql.png              ← Imagen del logo MySQL
│
└── bd/
    ├── gestionBaseDatos.php   ← LIBRERIA: conexion a MySQL + funciones SQL
    └── script.sql             ← Script para crear la base de datos (se ejecuta 1 vez)
```

### Resumen rapido


| Archivo                   | Tipo                    | Que hace                                                                     |
| --------------------------- | ------------------------- | ------------------------------------------------------------------------------ |
| `index.php`               | Pagina navegable        | Muestra el formulario. El usuario escribe datos y presiona "Enviar"          |
| `resultado.php`           | Pagina navegable        | Consulta todas las personas en MySQL y las muestra en una tabla HTML         |
| `procesando.php`          | Libreria (no navegable) | Recibe los datos del formulario, los guarda (o elimina) en MySQL, y redirige |
| `bd/gestionBaseDatos.php` | Libreria (no navegable) | Contiene todas las funciones SQL: conexion, insertar, consultar, eliminar    |
| `bd/script.sql`           | Script SQL              | Crea la base de datos y la tabla personas. Se ejecuta UNA vez                |

---

## 3. Navegables vs Librerias

Este es el concepto **mas importante** para entender en tu primer proyecto con PHP. No todo archivo `.php` es una pagina web.

### Archivos navegables (paginas)

Son las paginas que el usuario **abre en el navegador**. Tienen HTML, estilos, botones, formularios. Son como las "caras" de la aplicacion.

- `index.php` → el formulario
- `resultado.php` → la tabla de personas

**Como los reconoces:** tienen `<html>`, `<head>`, `<body>`, estilos CSS, formularios, etc. Son HTML con un toque de PHP.

### Librerias (no navegables)

Son archivos que **trabajan detras de escena**. Un usuario nunca los abre directamente en el navegador. Son "empleados invisibles" que hacen el trabajo sucio.

- `procesando.php` → el cerebro que recibe y procesa datos
- `bd/gestionBaseDatos.php` → la biblioteca de funciones SQL

**Como los reconoces:** son PHP puro, sin `<html>`, sin `<body>`, sin estilos. Son solo logica.

### Analogia: un restaurante


| Concepto                                | Ejemplo                                | En el proyecto                           |
| ----------------------------------------- | ---------------------------------------- | ------------------------------------------ |
| **El local** (lo que ve el cliente)     | El comedor, la carta, la decoracion    | `index.php`, `resultado.php`             |
| **La cocina** (detras de escena)        | El chef, los estantes, las recetas     | `procesando.php`, `gestionBaseDatos.php` |
| **El plano del local** (se usa una vez) | El plano para construir el restaurante | `script.sql`                             |

El cliente (el usuario) solo entra al comedor. Nunca ve la cocina, pero sin ella no hay comida.

---

## 4. PHP mezclado con HTML

PHP se **pega** directamente dentro del HTML. No es un lenguaje separado que se compila: es codigo que se ejecuta en el servidor y su resultado se "incrusta" en el HTML que el navegador recibe.

### Las dos formas de mezclarlos

#### Forma 1: Bloques PHP dentro de HTML

Cuando necesitas **logica** (consultas, ciclos, condiciones) dentro del HTML:

```php
<?php
// Aca va codigo PHP puro: consultas, variables, funciones
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion);
?>
<!-- Ahora vuelve HTML normal -->
<html>
    <body>
        <h1>Personas: <?php echo count($personas); ?></h1>
    </body>
</html>
```

Los `<?php ?>` son como "ventanas" que abres al servidor PHP. El navegador **nunca ve** lo que hay adentro: solo ve el resultado que PHP imprime.

#### Forma 2: PHP inline (una sola linea)

Cuando solo necesitas **imprimir un valor** dentro de una linea de HTML:

```html
<td><?php echo $persona['nombre']; ?></td>
```

Esto es lo mas comun: abrir `<?php`, usar `echo`, y cerrar `?>` todo en la misma linea.

#### Sintaxis alternativa (para bloques dentro de HTML)

PHP ofrece una sintaxis especial para usar dentro de HTML. En vez de llaves `{ }`, usa dos puntos `:` y termina con `endif;`, `endforeach;`, etc.:

```php
<?php if (empty($personas)): ?>
    <div class="alert">No hay personas registradas aun.</div>
<?php else: ?>
    <table>
        <?php foreach ($personas as $persona): ?>
            <tr>
                <td><?php echo $persona['nombre']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
```

Esto es mas legible que mezclar llaves de PHP con llaves de HTML.

### Que ve el navegador vs que hace el servidor

```
CODIGO EN EL SERVIDOR (lo que vos escribís):
────────────────────────────────────────────
<h1>Cantidad: <?php echo count($personas); ?></h1>


LO QUE EL NAVEGADOR RECIBE (el resultado):
────────────────────────────────────────────
<h1>Cantidad: 5</h1>
```

El navegador **nunca ve** `<?php echo count($personas); ?>`. Solo ve el "5" que PHP calculo y/printo.

---

## 5. Mostrando resultados con `echo`

`echo` es la forma mas basica de **enviar algo al navegador**. Es como decirle a PHP: "esto que calculaste, ponlo aca en el HTML".

### Echo basico

```php
<?php echo "Hola mundo"; ?>
```

El navegador ve: `Hola mundo`

### Echo con variables

```php
<?php
$nombre = "Juan";
$edad = 25;
?>
<p>Mi nombre es <?php echo $nombre; ?> y tengo <?php echo $edad; ?> anios.</p>
```

El navegador ve: `<p>Mi nombre es Juan y tengo 25 anios.</p>`

### Echo con expresiones

```php
<p>Total: <?php echo count($personas); ?> registros</p>
```

`count()` cuenta los elementos del array, y `echo` imprime el numero.

### Echo con arrays asociativos

```php
<?php echo $persona['nombre']; ?>
<?php echo $persona['apellido']; ?>
<?php echo $persona['email']; ?>
```

Accedes a cada campo del array usando la clave entre corchetes.

### Echo con htmlspecialchars (seguridad)

```php
<?php echo htmlspecialchars($persona['nombre']); ?>
```

`htmlspecialchars()` convierte caracteres peligrosos (`<`, `>`, `"`, `&`) en texto seguro. Si alguien guardo `<script>alert('hack')</script>` como nombre, se muestra como **texto** y no se ejecuta como codigo. Esto se llama **proteccion XSS**.

**Regla:** Siempre usa `htmlspecialchars()` cuando muestres datos que vienen de un usuario o de la base de datos.

### Echo en contexto completo

Aca se ve todo junto en `resultado.php`, linea 80-81:

```php
<tr>
    <td><?php echo $persona['id']; ?></td>
    <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
    <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
</tr>
```

Cada `<?php echo ...; ?>` reemplaza su contenido por el valor real en el HTML final que recibe el navegador.

---

## 6. Paso a paso por cada archivo

### 6.1 `bd/script.sql` — El plano de la base de datos

Este archivo **no es PHP**. Es puro SQL. Se ejecuta **una sola vez** para crear la base de datos y la tabla.

```sql
CREATE DATABASE IF NOT EXISTS contactos2;

USE contactos2;

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

**Que aprender aca:**

- `CREATE DATABASE IF NOT EXISTS` — crea la base si no existe. Si ya existe, no da error.
- `CREATE TABLE IF NOT EXISTS` — crea la tabla si no existe.
- `INT AUTO_INCREMENT PRIMARY KEY` — el `id` es un numero que se genera solo (1, 2, 3...) y identifica unicamente cada fila.
- `VARCHAR(n)` — texto corto de hasta `n` caracteres.
- `DATE` — formato de fecha: `AAAA-MM-DD`.
- `TEXT` — texto largo, sin limite de caracteres.
- `NOT NULL` — el campo es obligatorio.

**Donde se ejecuta:** en phpMyAdmin, pestana "SQL", pegar el codigo y presionar "Continuar".

---

### 6.2 `bd/gestionBaseDatos.php` — La biblioteca de funciones SQL

Este es el archivo **mas importante** del proyecto. Es una libreria que contiene todas las funciones necesarias para hablar con MySQL. No tiene HTML, no se navega.

#### La conexion: `obtenerConexion()`

```php
function obtenerConexion() {
    $host = "localhost";
    $usuario = "root";
    $contrasena = "";
    $baseDatos = "contactos2";

    try {
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

**Que aprender aca:**

- **PDO** (PHP Data Objects) es la forma segura de conectar PHP con bases de datos.
- Los datos de conexion (`$host`, `$usuario`, `$contrasena`, `$baseDatos`) estan arriba, faciles de cambiar.
- `try { } catch (PDOException $e) { }` — si la conexion falla (MySQL apagado, datos incorrectos), se muestra un mensaje amigable en vez de una pantalla en blanco.
- `die()` — detiene el script inmediatamente si hay error.

#### Preparar y ejecutar consultas (prepared statements)

Cada funcion usa el mismo patron:

```php
// 1. Escribir la consulta con ? como marcadores
$sql = "INSERT INTO personas (nombre, ...) VALUES (?, ...)";

// 2. Preparar (PHP analiza la consulta pero no la ejecuta)
$stmt = $conexion->prepare($sql);

// 3. Ejecutar pasando los valores por separado
$stmt->execute([$datos['nombre'], ...]);
```

**Por que usar `?` en vez de concatenar字符串?**

Para proteger contra **inyeccion SQL**. Si un usuario enviara como DNI: `123' OR '1'='1`, y lo concatenaramos directo en la consulta, se convertiria en:

```sql
SELECT * FROM personas WHERE dni = '123' OR '1'='1'
```

Esto devuelve **todos** los registros porque `'1'='1'` siempre es verdadero. Con `?` y `execute()`, PHP maneja los datos de forma segura y esto no puede pasar.

#### Las 4 funciones


| Funcion                              | Que hace                 | Consulta SQL clave                         |
| -------------------------------------- | -------------------------- | -------------------------------------------- |
| `obtenerConexion()`                  | Crea la conexion a MySQL | `new PDO(...)`                             |
| `insertarPersona($conexion, $datos)` | Guarda una persona nueva | `INSERT INTO personas ... VALUES (?, ...)` |
| `obtenerTodasLasPersonas($conexion)` | Trae todas las personas  | `SELECT * FROM personas ORDER BY id DESC`  |
| `eliminarPersona($conexion, $id)`    | Borra una persona por ID | `DELETE FROM personas WHERE id = ?`        |

---

### 6.3 `index.php` — El formulario (pagina navegable)

Este es el punto de entrada de la aplicacion. Es basicamente HTML con Bootstrap, pero con un detalle critico en el `<form>`:

```html
<form action="procesando.php" method="POST">
```

**Que aprender aca:**

- **`action="procesando.php"`** — los datos se envian a `procesando.php`, NO directamente a `resultado.php`. Esto es lo que cambio de la etapa 1: primero hay que **guardar en MySQL** y despues mostrar.
- **`method="POST"`** — los datos viajan "escondidos" en el cuerpo del pedido HTTP, no en la URL. Ideal para formularios con datos sensibles.

#### Los campos del formulario

Cada campo tiene un atributo `name`:

```html
<input type="text" name="nombre" required>
<input type="text" name="apellido" required>
<input type="email" name="email" required>
```

El `name` es la **clave** con la que PHP lee el valor. Cuando el usuario escribe "Juan" en el campo nombre, en `procesando.php` se accede con `$_POST['nombre']` y su valor es `"Juan"`.

El atributo `required` es validacion del navegador: evita enviar campos vacios sin necesidad de PHP.

#### Diferencia con la etapa 1

En la etapa 1, el `action` apuntaba directamente a `resultado.php` (mostrar el dato en la misma pagina). Ahora apunta a `procesando.php` porque hay un paso intermedio: **guardar en MySQL**.

---

### 6.4 `procesando.php` — El cerebro (no navegable)

Este archivo **no tiene HTML**. Su unica mision: recibir datos, hacer algo con MySQL, y redirigir al usuario a otra pagina.

#### Paso 1: Importar la biblioteca

```php
require_once 'bd/gestionBaseDatos.php';
```

`require_once` trae todas las funciones de `gestionBaseDatos.php`. Si el archivo no existiera, el programa se detiene con un error. Es como "copiar y pegar" todo el contenido de ese archivo aca.

#### Paso 2: Verificar que llegaron datos por POST

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

`$_SERVER` es una superglobal (variable especial de PHP) con informacion del servidor. `REQUEST_METHOD` indica como llego el usuario. Si es `'POST'`, significa que alguien envio un formulario. Si alguien abre `procesando.php` directamente en el navegador, el metodo seria `'GET'` y no entraria aca.

#### Paso 3: Decidir que hacer

El archivo maneja **dos casos**:

**Caso 1 — Eliminar:**

```php
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int) $_POST['id'];
    eliminarPersona($conexion, $id);
    header("Location: resultado.php");
    exit;
}
```

- `isset()` verifica que la variable exista. Si no existe, devuelve `false`.
- `(int)` convierte el id a numero entero (por seguridad).
- `header("Location: resultado.php")` redirige el navegador a otra pagina.
- `exit` detiene el script. **Siempre** despues de un `header("Location:")`.

**Caso 2 — Insertar:**

```php
} else if (isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['dni'])) {
    $datos = [
        'nombre'    => trim($_POST['nombre']),
        'apellido'  => trim($_POST['apellido']),
        'dni'       => trim($_POST['dni']),
        // ...todos los campos
    ];
    insertarPersona($conexion, $datos);
    header("Location: resultado.php");
    exit;
}
```

- Se verifica que los campos obligatorios existan.
- `trim()` elimina espacios al inicio y al final.
- Se arma un **array asociativo** `$datos` donde cada clave es el nombre del campo.
- Se llama a `insertarPersona()` y se redirige.

**Caso 3 — Sin datos:**

```php
} else {
    header("Location: index.php");
    exit;
}
```

Si no llegaron los datos minimos, el usuario vuelve al formulario.

---

### 6.5 `resultado.php` — La tabla de personas (pagina navegable)

Este archivo es **PHP mezclado con HTML**. Primero ejecuta PHP para traer los datos de MySQL, y despues los muestra en una tabla HTML.

#### Paso 1: Traer datos (arriba del HTML)

```php
<?php
require_once 'bd/gestionBaseDatos.php';
$conexion = obtenerConexion();
$personas = obtenerTodasLasPersonas($conexion);
?>
```

Esto se ejecuta **antes** de que se genere el HTML. `$personas` queda como un array de arrays:

```php
$personas = [
    0 => ['id' => 5, 'nombre' => 'Lucia', 'apellido' => 'Perez', ...],
    1 => ['id' => 4, 'nombre' => 'Juan',  'apellido' => 'Gomez', ...],
];
```

#### Paso 2: Mostrar la cantidad

```html
<span class="badge"><?php echo count($personas); ?> registros</span>
```

`count()` cuenta los elementos del array. Si hay 5 personas, el navegador ve: `<span class="badge">5 registros</span>`.

#### Paso 3: Verificar si hay datos

```php
<?php if (empty($personas)): ?>
    <div class="alert alert-info">No hay personas registradas aun.</div>
<?php else: ?>
    <!-- tabla con los datos -->
<?php endif; ?>
```

`empty()` verifica si el array esta vacio. Si no hay personas, se muestra un mensaje. Si hay, se muestra la tabla.

#### Paso 4: Recorrer y mostrar cada persona

```php
<?php foreach ($personas as $persona): ?>
    <tr>
        <td><?php echo $persona['id']; ?></td>
        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
        <td><?php echo htmlspecialchars($persona['apellido']); ?></td>
        <!-- ...mas campos... -->
        <td>
            <form action="procesando.php" method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                <button type="submit">Eliminar</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
```

- `foreach` recorre el array. En cada vuelta, `$persona` es una persona.
- `echo $persona['nombre']` imprime el nombre.
- `htmlspecialchars()` protege contra XSS.

#### El boton eliminar

```html
<form action="procesando.php" method="POST">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
    <button type="submit">Eliminar</button>
</form>
```

Los campos `type="hidden"` no se ven, pero viajan en el POST. Cuando el usuario hace click en "Eliminar":

1. Se envia `accion=eliminar` e `id=123` (por ejemplo) a `procesando.php`
2. `procesando.php` detecta que es una eliminacion
3. Borra la persona de MySQL
4. Redirige a `resultado.php` para que se actualice la tabla

---

## 7. Flujo completo de datos

### Cuando se CARGA una persona

```
1. Usuario llena el formulario en index.php
         │
         ▼ (presiona "Enviar", metodo POST)
2. Los datos llegan a procesando.php
         │
         ▼ (se ejecuta insertarPersona())
3. Los datos se guardan en MySQL
         │
         ▼ (header("Location: resultado.php"))
4. El navegador abre resultado.php
         │
         ▼ (se ejecuta obtenerTodasLasPersonas())
5. Se consultan TODAS las personas y se muestra la tabla
```

### Cuando se ELIMINA una persona

```
1. Usuario hace click en "Eliminar" en resultado.php
         │
         ▼ (POST con accion=eliminar e id=X)
2. Los datos llegan a procesando.php
         │
         ▼ (se ejecuta eliminarPersona())
3. Se borra la persona de MySQL
         │
         ▼ (header("Location: resultado.php"))
4. Se muestra la tabla actualizada
```

---

## 8. Resumen de conceptos clave


| Concepto                                    | Donde se usa                             | Explicacion corta                                                          |
| --------------------------------------------- | ------------------------------------------ | ---------------------------------------------------------------------------- |
| **Navegable vs no navegable**               | Todo el proyecto                         | Hay paginas (HTML) y librerias (solo PHP)                                  |
| **`require_once`**                          | `procesando.php`, `resultado.php`        | Importa una libreria PHP (como copiar/pegar su contenido)                  |
| **`$_POST`**                                | `procesando.php`                         | Superdonde PHP guarda lo que envio el formulario con method POST           |
| **`$_SERVER['REQUEST_METHOD']`**            | `procesando.php`                         | Indica si el usuario envio un formulario (POST) o abrio directamente (GET) |
| **`echo`**                                  | `resultado.php`, `index.php`             | Imprime un valor en el HTML que ve el navegador                            |
| **`htmlspecialchars()`**                    | `resultado.php`                          | Protege contra XSS: convierte codigo malicioso en texto seguro             |
| **`isset()`**                               | `procesando.php`                         | Verifica si una variable existe                                            |
| **`trim()`**                                | `procesando.php`                         | Elimina espacios al inicio y final de un texto                             |
| **`count()`**                               | `resultado.php`                          | Cuenta los elementos de un array                                           |
| **`empty()`**                               | `resultado.php`                          | Verifica si un array esta vacio                                            |
| **`foreach`**                               | `resultado.php`                          | Recorre un array, elemento por elemento                                    |
| **Prepared statements (`?` + `execute()`)** | `gestionBaseDatos.php`                   | Proteccion contra inyeccion SQL                                            |
| **`header("Location:")` + `exit`**          | `procesando.php`                         | Redirige el navegador a otra pagina                                        |
| **Array asociativo**                        | `procesando.php`, `gestionBaseDatos.php` | Array con claves:`$datos['nombre']` en vez de `$datos[0]`                  |
| **`PDO`**                                   | `gestionBaseDatos.php`                   | Clase de PHP para conectarse a bases de datos de forma segura              |
| **`try/catch`**                             | `gestionBaseDatos.php`                   | Manejo de errores: si algo falla, se muestra un mensaje en vez de morir    |

---

## Tip: como ejecutar el proyecto

1. Asegurate de que **XAMPP** (o similar) este encendido con Apache y MySQL activos.
2. Copia la carpeta `etapa2-BD` dentro de `C:/xampp/htdocs/` (o la carpeta `htdocs` de tu servidor).
3. Abre phpMyAdmin: `http://localhost/phpmyadmin`
4. Actualiza `bd/script.sql` y `gestionBaseDatos.php`. En el primero se encuentra el código SQL, este inicia con lo siguiente:

   ```SQL
   CREATE DATABASE IF NOT EXISTS contactos2;
   USE contactos2; -- Seleccionamos la base de datos para usarla
   ```
   En `gestionBaseDatos.php`, la función `obtenerConexion()` contiene los datos de conexión. **Deben adecuar estos valores según su instalación** y verificar que el nombre de la base de datos coincida con el del script SQL:

   ```PHP
   function obtenerConexion() {    // Datos de conexion a MySQL - CAMBIAR segun tu instalacion
       $host = "localhost";       // Servidor (generalmente localhost)
       $usuario = "root";         // Usuario de MySQL
       $contrasena = "";          // Contrasena de MySQL (vacio por defecto en XAMPP)
       $baseDatos = "contactos2"; // Nombre de la base de datos (debe coincidir con script.sql)
       ...
   }
   ```
5. En la pestana **SQL**, pega el contenido de `bd/script.sql` y ejecutalo. Esto crea la base de datos.
6. Abre el navegador en: `http://localhost/etapa2-BD/index.php`
7. Llena el formulario y presiona "Enviar". Deberia redirigirte a `resultado.php` mostrando la persona cargada.
