# src/template — Plantillas para crear un módulo nuevo

Esta carpeta **no es parte de la aplicación**: son tres archivos modelo para
copiar cuando haya que agregar un módulo nuevo (por ejemplo "paises",
"cursos", "notas"...).

## Los tres archivos

| Archivo | ¿Es navegable? | ¿Tiene HTML? | ¿Qué hace? |
|---|---|---|---|
| `viewModulo.php` | Sí | Sí | Muestra la tabla de todos los registros, con buscador y botones de Modificar / Eliminar |
| `editModulo.php` | Sí | Sí | Formulario de alta y de edición (mismo archivo, dos modos según haya `?id=` o no) |
| `gestionModulo.php` | No | No | Recibe los datos del formulario, hace el INSERT / UPDATE / DELETE y redirige |

Los nombres son neutros a propósito. En la aplicación real se llaman
`viewPersona.php`, `editPersona.php`, `gestionPersona.php`, etc.

## Cómo usar las plantillas

1. Copiá la carpeta: `src/template/` → `src/paises/`
2. Renombrá los archivos:
   - `viewModulo.php` → `viewPais.php`
   - `editModulo.php` → `editPais.php`
   - `gestionModulo.php` → `gestionPais.php`
3. Reemplazá las palabras `MODULO`, `Modulo`, `modulo` por el nombre de tu
   módulo (país, paises, etc.)
4. Cambiá los nombres de las funciones por los tuyos
   (`obtenerTodosLosModulos()` → `obtenerTodosLosPaises()`), que deben estar
   en tu archivo de la base de datos.
5. Agregá un botón en `src/index.php` que apunte a tu `viewPais.php`.

> Ojo con las rutas: los `require_once` usan `../../lib/...` porque el módulo
> va a estar a la misma altura que esta carpeta (`src/...`).

## ¿Se parece al patrón MVP?

Sí, **se parece**, aunque con los nombres que se usan en este proyecto
y no con las letras de MVP.

**MVP = Modelo - Vista - Presentador.** La idea es separar el programa en
tres responsabilidades para que no esté todo mezclado en un mismo archivo:

| Pieza del patrón | Qué hace | En este proyecto |
|---|---|---|
| **Modelo** | Guarda los datos y habla con la base de datos (SQL) | `lib/bd/*-bd.php` (`personas-bd.php`, `ciudades-bd.php`) |
| **Vista** | Muestra los datos y recibe lo que escribe el usuario (HTML) | `viewModulo.php` y `editModulo.php` |
| **Presentador / Controlador** | Recibe lo que viene del formulario, valida y llama al modelo; después devuelve el control a la vista | `gestionModulo.php` |

### El recorrido de un dato (ejemplo: guardar una persona)

```
1. El usuario completa editPersona.php          -> VISTA (formulario)
2. Aprieta "Crear persona" (POST)               -> se envían los datos
3. gestionPersona.php recibe los datos          -> PRESENTADOR
4. llama a insertarPersona(...)                 -> MODELO (INSERT en MySQL)
5. header("Location: viewPersona.php")          -> vuelve a la VISTA
6. viewPersona.php vuelve a consultar y muestra la tabla
```

### Qué se gana con esta separación

- Cada archivo hace **una sola cosa** y es más corto y más fácil de leer.
- Para **reutilizar**: `piePagina()` se usa en todas las páginas y se
  cambia en un solo lugar.
- Para **mantener**: si algún día la tabla `personas` cambia, solo se toca
  `lib/bd/personas-bd.php`; los formularios no se enteran.
- Para **probar**: se puede probar el modelo (las consultas) sin abrir
  el navegador.

### Lo que NO es MVP puro

Para ser totalmente fiel al patrón, el "presentador" debería ser una clase
(programada con `class` y `public function`). Acá, para que sea más simple
de entender en los primeros pasos, el presentador es un archivo con
funciones sueltas y variables. La idea (la separación de responsabilidades)
es la misma; solamente se usaron menos cosas de PHP.
