<?php
/*
    src/template/avisoPlantilla.php - La ayuda para crear el módulo PAÍSES

    Las plantillas de esta carpeta (viewModulo.php, editModulo.php,
    gestionModulo.php) llaman a funciones que todavía NO existen:

        obtenerTodosLosModulos()   obtenerModuloPorId()
        crearModulo()              actualizarModulo()   eliminarModulo()

    Esas funciones las tiene que escribir cada alumno en lib/bd/<tabla>-bd.php
    cuando copia la carpeta src/template/ para crear su propio módulo.

    Si abrieras la plantilla sin eso, PHP mostraría un error 500 con un texto
    poco claro ("Call to undefined function obtenerTodosLosModulos()").
    Con este archivo mostramos, en cambio, el paso a paso completo.

    ----------------------------------------------------------------------------
    EL EJEMPLO DE ESTA PÁGINA: agregar el módulo "países"
    ----------------------------------------------------------------------------
    El módulo countries viene con una complicación que los demás no tienen:
    una RELACIÓN con otro módulo. La ciudad pasa a depender del país:

        pais (id, nombre, codigo_iso, capital, poblacion, descripcion)
          |
          | 1 pais -> N ciudades   (ciudades.pais_id es clave foránea)
          v
        ciudades (id, nombre, provincia, pais_id, ...)
          |
          | 1 ciudad -> N personas  (personas.ciudad_id)
          v
        personas (id, nombre, ..., ciudad_id)

    Por eso no alcanza con copiar los archivos: hay que tocar el módulo
    ciudades (formulario, guardado, listado y buscador) y el inicio.
    Todos los pasos están abajo y también en el readme.md de la raíz
    (sección 8, "Agregar el módulo países").
*/

/**
 * Muestra la página de ayuda con el paso a paso para agregar el módulo países.
 *
 * @param string $funciones Nombre(s) de la función que falta, separados por coma
 * @param bool   $accesoDirecto true si se abrió este archivo como página propia
 *                              (desde el link "¿Cómo agrego un nuevo módulo?")
 * @return void
 */
function mostrarAvisoPlantilla($funciones, $accesoDirecto = false) {
    // Sacamos los espacios que sobran si viene más de una función
    $lista = implode(', ', array_map('trim', explode(',', $funciones)));
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cómo agregar un módulo nuevo - Plantillas de países</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">

                    <div class="card-header bg-warning text-dark">
                        <h2 class="mb-0">
                            <?php echo $accesoDirecto
                                ? 'Cómo agregar un módulo nuevo'
                                : 'Todavía no puedo mostrar esta página'; ?>
                        </h2>
                    </div>

                    <div class="card-body">

                        <p class="lead">
                            Estas plantillas de <code>src/template/</code> están preparadas para
                            agregar el módulo <strong>PAÍSES</strong>. Son un ejemplo: la idea es
                            copiarlas, renombrarlas y completarlas con el módulo que te pida
                            el profesor.
                        </p>

                        <?php if (!$accesoDirecto): ?>
                        <div class="alert alert-light border">
                            Me falta esta función en el proyecto:
                            <code class="text-danger"><?php echo htmlspecialchars($lista); ?></code>
                            Por eso PHP no puede seguir y mostraría un error 500.
                        </div>
                        <?php endif; ?>

                        <h5 class="mt-4">Lo que vas a construir</h5>
                        <p>
                            Un módulo nuevo <strong>paises</strong> <em>y</em> una relación nueva:
                            la ciudad pasa a saber a qué país pertenece. Quedan
                            <strong>tres tablas</strong> encadenadas:
                        </p>
                        <pre class="bg-light p-3 rounded border">pais (id, nombre, codigo_iso, capital, poblacion, descripcion)
  |
  |  1 pais -&gt; N ciudades      ciudades.pais_id  (clave foránea)
  v
ciudades (id, nombre, provincia, pais_id, ...)
  |
  |  1 ciudad -&gt; N personas     personas.ciudad_id (clave foránea)
  v
personas (id, nombre, ..., ciudad_id)</pre>

                        <h5 class="mt-4 mb-3">Paso a paso</h5>

                        <div class="accordion" id="pasos">
                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#paso1">
                                        <strong class="me-2">Paso 1.</strong> La tabla <code>pais</code> en MySQL
                                    </button>
                                </h6>
                                <div id="paso1" class="accordion-collapse collapse show" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>
                                            Creá la tabla y agregale la columna
                                            <code>pais_id</code> a ciudades, con su clave foránea.
                                        </p>
<pre class="bg-light p-2 rounded border mb-2">CREATE TABLE pais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo_iso CHAR(3) NOT NULL,   -- ISO 3166-1: ARG, BRA, ESP...
    capital VARCHAR(100),
    poblacion BIGINT,
    descripcion TEXT,
    UNIQUE (codigo_iso)            -- no puede repetirse el código
);

ALTER TABLE ciudades ADD COLUMN pais_id INT NULL AFTER provincia;
ALTER TABLE ciudades
    ADD CONSTRAINT fk_ciudades_pais
    FOREIGN KEY (pais_id) REFERENCES pais(id);</pre>
                                        <p class="mb-0">
                                            La columna va <code>NULL</code> a propósito: si ya tenías
                                            ciudades cargadas, primero las creás y después les
                                            asignás el país con un <code>UPDATE</code>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso2">
                                        <strong class="me-2">Paso 2.</strong> El modelo: <code>lib/bd/paises-bd.php</code>
                                    </button>
                                </h6>
                                <div id="paso2" class="accordion-collapse collapse" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>
                                            Es el archivo donde vive TODO el SQL de países. Copiá
                                            <code>lib/bd/ciudades-bd.php</code> como base y renombrá
                                            las funciones:
                                        </p>
<pre class="bg-light p-2 rounded border mb-0">insertarPais($conexion, $datos)          // INSERT
obtenerTodosLosPaises($conexion, $busqueda)  // SELECT con filtro
obtenerPaisPorId($conexion, $id)        // SELECT por id
contarPaises($conexion)                 // COUNT(*) para el inicio
contarCiudadesPorPais($conexion)        // GROUP BY: ciudades por país
hayCiudadesEnPais($conexion, $id)       // para no borrar un país con ciudades
actualizarPais($conexion, $id, $datos)   // UPDATE
eliminarPais($conexion, $id)            // DELETE</pre>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso3">
                                        <strong class="me-2">Paso 3.</strong> Que el modelo esté cargado
                                    </button>
                                </h6>
                                <div id="paso3" class="accordion-collapse collapse" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>
                                            Una sola línea en <code>lib/bd/gestionBaseDatos.php</code>,
                                            al lado de las otras librerías. Así todas las páginas
                                            tienen las funciones de países disponibles sin
                                            incluirlas una por una.
                                        </p>
<pre class="bg-light p-2 rounded border mb-0">require_once __DIR__ . '/personas-bd.php';
require_once __DIR__ . '/ciudades-bd.php';
require_once __DIR__ . '/paises-bd.php';   // &lt;- la nueva</pre>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso4">
                                        <strong class="me-2">Paso 4.</strong> Las páginas del módulo
                                    </button>
                                </h6>
                                <div id="paso4" class="accordion-collapse collapse" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>Copiá la carpeta y renombrá los archivos:</p>
<pre class="bg-light p-2 rounded border mb-2">src/template/  -&gt;  src/pais/

viewModulo.php     -&gt;  viewPais.php
editModulo.php     -&gt;  editPais.php
gestionModulo.php  -&gt;  gestionPais.php
avisoPlantilla.php  -&gt;  avisoPlantilla.php  (o borrarlo: ya no falta nada)</pre>
                                        <p>
                                            Y cambiá los nombres de las funciones en las tres páginas:
                                        </p>
                                        <ul>
                                            <li><code>obtenerTodosLosModulos()</code> → <code>obtenerTodosLosPaises()</code></li>
                                            <li><code>obtenerModuloPorId()</code> → <code>obtenerPaisPorId()</code></li>
                                            <li><code>crearModulo()</code> → <code>insertarPais()</code></li>
                                            <li><code>actualizarModulo()</code> → <code>actualizarPais()</code></li>
                                            <li><code>eliminarModulo()</code> → <code>eliminarPais()</code></li>
                                        </ul>
                                        <p class="mb-0">
                                            En <code>viewPais.php</code> agregá una columna con
                                            <code>contarCiudadesPorPais()</code>, y en
                                            <code>gestionPais.php</code> el borrado tiene que
                                            consultar <code>hayCiudadesEnPais()</code> antes de
                                            eliminar (igual que se hace con las personas y las ciudades).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso5">
                                        <strong class="me-2">Paso 5.</strong> La relación: adaptar ciudades
                                    </button>
                                </h6>
                                <div id="paso5" class="accordion-collapse collapse" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>
                                            Esta es la parte que se olvida: si creaste la columna
                                            <code>pais_id</code> pero no tocás el módulo ciudades,
                                            nadie puede elegir un país al cargar una ciudad.
                                        </p>
                                        <ol>
                                            <li><code>insertarCiudad()</code> y <code>actualizarCiudad()</code>:
                                                sumar <code>pais_id</code> a la lista de columnas y de valores.</li>
                                            <li><code>editCiudad.php</code>: un <code>&lt;select name="pais_id"&gt;</code>
                                                armado con <code>obtenerTodosLosPaises()</code>.</li>
                                            <li><code>gestionCiudad.php</code>: agregar
                                                <code>'pais_id' =&gt; (int) $_POST['pais_id']</code> al array
                                                <code>$datos</code> y validarlo.</li>
                                            <li><code>obtenerTodasLasCiudades()</code>: hacer
                                                <code>LEFT JOIN pais</code> para traer
                                                <code>pais_nombre</code> y mostrarlo en la columna
                                                "País" de <code>viewCiudad.php</code>.</li>
                                            <li><code>obtenerCiudadesPorNombre()</code>: mismo JOIN, para
                                                que el buscador de <code>editPersona.php</code> muestre
                                                "Río Cuarto (Córdoba, Argentina)".</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h6 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso6">
                                        <strong class="me-2">Paso 6.</strong> La entrada desde el inicio
                                    </button>
                                </h6>
                                <div id="paso6" class="accordion-collapse collapse" data-bs-parent="#pasos">
                                    <div class="accordion-body">
                                        <p>
                                            En <code>src/index.php</code>, sumá
                                            <code>contarPaises($conexion)</code> y una tercera tarjeta
                                            con el botón a <code>pais/viewPais.php</code>, igual que
                                            las de personas y ciudades. Si no, el módulo existe pero
                                            nadie lo encuentra.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <strong>No hay que tocar <code>editPersona.php</code>:</strong> la persona
                            sigue guardando solo <code>ciudad_id</code>. El país se hereda a través de
                            la ciudad (persona → ciudad → país), que es justamente el beneficio de
                            normalizar: el dato del país está escrito una sola vez.
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="../../index.php" class="btn btn-primary">Volver al inicio</a>
                        <a href="readme.md" class="btn btn-outline-secondary">Ver el readme del módulo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    <?php
    exit;
}

/*
    ACCESO DIRECTO A ESTE ARCHIVO
    -------------------------------------------------------------------------
    Normalmente este archivo se incluye con require_once desde las plantillas
    (viewModulo.php, editModulo.php, gestionModulo.php), y lo que se muestra
    es la página que arma mostrarAvisoPlantilla().

    Pero también se puede abrir como página propia: es lo que hace el link
    "¿Cómo agrego un nuevo módulo?" del inicio. En ese caso no hay ninguna
    función faltante que señalar, así que mostramos la misma guía como
    documentación de referencia.

    La comparación es contra SCRIPT_FILENAME (el archivo que está ejecutando
    el navegador), no contra __FILE__: si el chequeo fuera con __FILE__,
    también se dispararía al ser incluido desde otra plantilla y la página
    aparecería dos veces.
*/
if (isset($_SERVER['SCRIPT_FILENAME'])
    && basename($_SERVER['SCRIPT_FILENAME']) === 'avisoPlantilla.php') {
    mostrarAvisoPlantilla('las funciones de tu módulo nuevo (ejemplo: países)', true);
}
