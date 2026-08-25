# Formulario de Contactos - PHP + Bootstrap

Proyecto educativo de **Aplicaciones Web 6° C** que demuestra el desarrollo de un formulario web con PHP, desde una version simple hasta una conectada a base de datos.

---

## Etapa 0 - Formulario HTML puro (sin PHP)

> Carpeta: `etapa0-html/`

Formulario simple con HTML, CSS y JavaScript puro, sin uso de PHP ni frameworks. Sirve como introduccion a formularios web y tipos de input antes de pasar a PHP.

### Archivos

| Archivo            | Descripcion                                                        |
| ------------------ | ------------------------------------------------------------------ |
| `index.html`     | Formulario con 7 tipos de input distintos sobre peliculas y series |
| `resultado.html` | Muestra los datos usando parametros GET y`URLSearchParams`       |

### Tipos de input utilizados

| Tipo         | Campo                                                       | Elemento                    |
| ------------ | ----------------------------------------------------------- | --------------------------- |
| `text`     | Nombre de la pelicula/serie                                 | `<input type="text">`     |
| `number`   | Cantidad de temporadas                                      | `<input type="number">`   |
| `date`     | Fecha de estreno                                            | `<input type="date">`     |
| `radio`    | Tipo (Pelicula o Serie)                                     | `<input type="radio">`    |
| `checkbox` | Generos (Accion, Comedia, Drama, Ciencia Ficcion)           | `<input type="checkbox">` |
| `textarea` | Opinion personal                                            | `<textarea>`              |
| `select`   | Plataforma (Netflix, Amazon Prime, Disney+, HBO Max, Otros) | `<select>`                |

### Metodos de transferencia de datos

- **GET con URLSearchParams** (`resultado.html`): los datos se envian como parametros en la URL y se leen con `new URLSearchParams(window.location.search)`

### CSS

Solo se aplica estilos puros a los titulos (`h1` y `h2`), sin frameworks.

--

## Etapa 1 - Formulario sin base de datos

> Carpeta: `etapa1/`

Version basica que recibe datos de un formulario y los muestra en pantalla, sin persistirlos.

### Archivos

| Archivo           | Descripcion                                                                                                                                                   |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `index.php`     | Formulario HTML con 12 campos (nombre, apellido, DNI, CUIT, fecha de nacimiento, email, telefono, direccion, ciudad, provincia, codigo postal, observaciones) |
| `resultado.php` | Muestra los datos enviados por POST en una tabla                                                                                                              |

### Conceptos que se enseñan

- Formularios HTML (`<form>`, `<input>`, `<textarea>`)
- Metodo POST vs GET
- SuperGLOBALS `$_POST` y `$_SERVER`
- `htmlspecialchars()` para prevenir inyeccion de codigo (XSS)
- Bootstrap 5 (cards, grillas, formularios)

### Como ejecutar

```bash
cd etapa1
php -S localhost:8000
```

Abrir `http://localhost:8000` en el navegador.

---

## Etapa 2 - Formulario con base de datos MySQL

> Carpeta: `etapa2-BD/`

Evolucion del proyecto anterior. Los datos se guardan en MySQL y se muestran en una tabla con la posibilidad de eliminar registros.

### Archivos

| Archivo                     | Descripcion                                                                                      |
| --------------------------- | ------------------------------------------------------------------------------------------------ |
| `index.php`               | Mismo formulario de la Etapa 1, pero envia a`procesando.php`                                   |
| `procesando.php`          | Archivo intermedio sin HTML. Recibe los datos, los guarda en la BD y redirige a`resultado.php` |
| `resultado.php`           | Consulta y muestra TODAS las personas de la BD en una tabla con boton de eliminar                |
| `bd/gestionBaseDatos.php` | Funciones de conexion y CRUD (conexion, insertar, consultar, eliminar)                           |
| `bd/script.sql`           | Script SQL para crear la base de datos`contactos` y la tabla `personas`                      |

### Flujo de la aplicacion

```
index.php (formulario)
    │
    ▼ POST
procesando.php (guarda en MySQL)
    │
    ▼ header("Location: resultado.php")
resultado.php (muestra todos los registros)
```

### Conceptos que se enseñan

- Conexión a MySQL con PDO
- Prepared statements (proteccion contra inyeccion SQL)
- Funciones CRUD (Create, Read, Delete)
- Redireccion con `header("Location: ...")`
- `foreach` para recorrer arrays de la BD
- `count()` para contar registros
- Arreglos asociativos

### Requisitos

- PHP 7.4 o superior
- MySQL / MariaDB
- phpMyAdmin (opcional, para gestionar la BD desde el navegador)

### Configuracion

1. Crear la base de datos ejecutando `bd/script.sql` en MySQL
2. Verificar credenciales en `bd/gestionBaseDatos.php`:

```php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$baseDatos = "contactos";
```

### Como ejecutar

```bash
cd etapa2-BD
php -S localhost:8000
```

Abrir `http://localhost:8000` en el navegador.

---

## Etapa 3 - Base de datos con dos tablas relacionadas (clave foranea)

> Carpeta: `etapa3-BD-tablas/`

Evolucion de la Etapa 2 que incorpora **normalizacion**: la base de datos ahora tiene DOS tablas relacionadas.

- `ciudades`: guarda las ciudades (nombre, provincia, latitud, longitud, **codigo postal**, descripcion, fecha de fundacion)
- `personas`: guarda las personas y la ciudad donde nacieron

La relacion "donde nacio cada persona" se representa con una **CLAVE FORANEA** (`ciudad_id`) en la tabla personas. El campo de texto libre "ciudad" de la Etapa 2 se reemplaza por esta clave foranea. Tambien el `codigo_postal` se movio de `personas` a `ciudades` porque es un dato del lugar.

Incluye **portada con estadisticas** (index.php), **buscadores por nombre** en personas y ciudades.

### Archivos

| Archivo                       | Descripcion                                                                                           |
| ----------------------------- | ----------------------------------------------------------------------------------------------------- |
| `index.php`                 | Pagina de bienvenida con estadisticas (cantidad de personas y ciudades) y accesos rapidos              |
| `formPersona.php`           | Formulario de persona con **select** de ciudad (elige entre las ciudades existentes)                   |
| `procesando.php`            | Archivo sin HTML: inserta o elimina personas                                                          |
| `resultado.php`             | Tabla de personas con su ciudad (via JOIN) y **buscador por nombre**                                  |
| `ciudades.php`              | Lista de ciudades con editar/eliminar y **buscador por nombre**                                       |
| `formCiudad.php`            | Formulario de alta/edicion de ciudades (sirve para crear y modificar)                                  |
| `gestionCiudades.php`       | Archivo sin HTML: procesa crear, actualizar o eliminar ciudades (con validacion de lat/long)          |
| `bd/gestionBaseDatos.php`   | Conexion PDO + funciones CRUD, busqueda (LIKE) y conteo para ambas tablas                              |
| `bd/script.sql`             | Script SQL: crea la BD `contactos3` con las tablas `ciudades` y `personas` + clave foranea          |
| `img/mysql.png`             | Icono de MySQL                                                                                         |

### Flujo de la aplicacion

```
index.php (portada: estadisticas)
    │
    ├──> formPersona.php (formulario persona, elige ciudad del select)
    │        │
    │        ▼ POST
    │     procesando.php (guarda persona con ciudad_id en MySQL)
    │        │
    │        ▼ header("Location: resultado.php")
    │     resultado.php (muestra personas con su ciudad via JOIN + buscar)
    │
    └──> ciudades.php (gestion de ciudades + buscar)
            │
            ├── formCiudad.php (alta/edicion ciudad)
            │     │
            │     ▼ POST
            │  gestionCiudades.php (INSERT / UPDATE / DELETE)
            │     │
            │     ▼ header("Location: ciudades.php")
            │  ciudades.php (lista todas las ciudades)
```

### Conceptos que se enseñan

- Dos tablas relacionadas: `ciudades` y `personas`
- Clave primaria (PRIMARY KEY) y clave foranea (FOREIGN KEY)
- Normalizacion: eliminar datos repetidos, usar IDs en vez de texto, cada dato en su tabla
- JOIN para combinar datos de dos tablas en una consulta
- CRUD completo (Create, Read, Update, Delete) para ciudades
- Formulario que sirve para alta y edicion a la vez (segun venga `?id=` o no)
- Verificacion de la clave foranea: no se puede borrar una ciudad que tenga personas
- Busquedas con `LIKE` y comodin `%` (GET, porque es una consulta, no un guardado)
- Conteo con `COUNT(*)` para estadisticas
- Validacion de datos en PHP para evitar errores de MySQL

### Configuracion

1. Ejecutar `bd/script.sql` (crea la base de datos `contactos3`)
2. Verificar credenciales en `bd/gestionBaseDatos.php`
3. Cargar primero las ciudades (boton "Gestionar ciudades") y despues las personas

### Como ejecutar

```bash
cd etapa3-BD-tablas
php -S localhost:8000
```

---

## Comparacion de las etapas

| Caracteristica              | Etapa 0        | Etapa 1                  | Etapa 2                 | Etapa 3                     |
| --------------------------- | -------------- | ------------------------ | ----------------------- | --------------------------- |
| Tecnologia                  | HTML + JS puro | PHP + Bootstrap          | PHP + Bootstrap + MySQL | PHP + Bootstrap + MySQL     |
| Base de datos               | No             | No                       | Si (MySQL)              | Si (MySQL)                  |
| Tablas                      | -              | -                        | 1 (`personas`)          | 2 (`ciudades` y `personas`) |
| Clave foranea               | No             | No                       | No                      | Si (`ciudad_id`)            |
| Persistencia                | No (se pierde al cerrar)    | No (se pierde al cerrar) | Si (queda guardada)     | Si (queda guardada)         |
| Eliminar registros          | No             | No                       | Si                      | Si                          |
| Actualizar (editar)         | No             | No                       | No                      | Si (ciudades)               |
| Muestra todos los registros | No (solo 1)    | No (solo 1)              | Si                      | Si                          |
| Archivos HTML/PHP           | 2              | 2                        | 4                       | 7 + carpetas                |
