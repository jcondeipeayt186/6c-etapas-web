<?php
/*
    editPersona.php - Formulario de alta y edición de personas

    Este formulario sirve para DOS cosas (mismo archivo, dos modos):
    1. CREAR una persona nueva  -> se entra sin ?id=   (ej: editPersona.php)
    2. EDITAR una persona      -> se entra con ?id=  (ej: editPersona.php?id=5)

    En el modo edición se busca la persona en la base y se precargan sus
    datos (value="..."). El título y el botón cambian según el modo.

    DOS COSAS INTERESANTES DE ESTE FORMULARIO:

    1) LA CIUDAD DE NACIMIENTO (clave foránea)
       - Si hay hasta 50 ciudades: se muestra un <select> con todas.
       - Si hay más de 50: se muestra un input de texto + buscador.
         El usuario escribe 3 letras y el navegador busca las ciudades
         que empiezan con esas letras (usando buscarCiudad.php).
       - En los dos casos lo que se guarda es el ID de la ciudad,
         nunca el nombre escrito.

    2) LOS ARCHIVOS ADJUNTOS
       - Una foto (avatar) y un currículum (cv).
       - Van en <input type="file">, por eso el <form> necesita
         enctype="multipart/form-data".
       - Los archivos se guardan en files/avatars y files/cv
         (ver lib/utils/archivos.php) y en la base solo queda la ruta.
*/

// Incluimos la librería de la base de datos: conexión + funciones de personas y ciudades
require_once '../../lib/bd/gestionBaseDatos.php';
// Incluimos la librería de HTML compartido (piePagina, mostrarAlerta)
require_once '../../lib/html/funcionesHTML.php';

// Creamos la conexión PDO
$conexion = obtenerConexion();

// Traemos las ciudades para poder elegir el lugar de nacimiento
$ciudades = obtenerTodasLasCiudades($conexion);

// Si hay más de este número de ciudades, usamos el input con buscador
// en lugar del <select> (con muchas opciones la lista se vuelve incómoda)
$limiteParaLista = 50;
$usarBuscador = count($ciudades) > $limiteParaLista;

// Modo edición
$persona = null;    // la persona a editar (o null si es un alta)
$esEdicion = false;

// Si la URL trae ?id=, estamos editando
if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // (int) por seguridad: "hola" se convierte en 0
    $persona = obtenerPersonaPorId($conexion, $id);
    if ($persona) {
        $esEdicion = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Persona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <!-- Cabecera azul con título según el modo -->
                    <div class="card-header bg-primary text-white text-center">
                        <img src="../../img/mysql.png" alt="Logo MySQL" width="40" class="me-2">
                        <h2 class="d-inline align-middle"><?php echo $esEdicion ? 'Modificar Persona' : 'Nueva Persona'; ?></h2>
                    </div>
                    <div class="card-body">

                        <!-- Aviso: sin ciudades no se puede elegir lugar de nacimiento -->
                        <?php if (empty($ciudades)): ?>
                            <div class="alert alert-warning">
                                No hay ciudades cargadas. Primero debe
                                <a href="../ciudad/editCiudad.php" class="alert-link">cargar una ciudad</a>.
                            </div>
                        <?php endif; ?>

                        <!-- Errores que pueden venir desde gestionPersona.php -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <!-- Se pidió editar una persona que no existe -->
                        <?php if (isset($_GET['id']) && !$esEdicion): ?>
                            <div class="alert alert-danger">La persona no existe o el ID es inválido.</div>
                            <a href="viewPersona.php" class="btn btn-primary w-100">Volver al listado</a>
                        <?php else: ?>

                        <!--
                            El formulario siempre manda los datos a gestionPersona.php.
                            - Sin id (alta):  accion=crear     -> se hace INSERT
                            - Con id (edición): accion=actualizar -> se hace UPDATE
                            enctype="multipart/form-data" es obligatorio para poder subir archivos
                        -->
                        <form action="gestionPersona.php" method="POST" enctype="multipart/form-data"
                              onsubmit="return validarFormulario();">

                            <!-- Campos ocultos: le dicen a gestionPersona.php qué hacer -->
                            <?php if ($esEdicion): ?>
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $persona['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="accion" value="crear">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['nombre']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['apellido']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['dni']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cuit" class="form-label">CUIT</label>
                                    <input type="text" class="form-control" id="cuit" name="cuit" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['cuit']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <!-- type=date usa el formato YYYY-MM-DD, el mismo que guarda MySQL -->
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['fecha_nacimiento']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['telefono']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" required
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['direccion']) : ''; ?>">
                                </div>
                            </div>

                            <!-- ===================================================== -->
                            <!--  LUGAR DE NACIMIENTO (clave foránea ciudad_id)          -->
                            <!-- ===================================================== -->
                            <div class="mb-3">
                                <label for="ciudad_id" class="form-label">Lugar de Nacimiento</label>

                                <?php if (!$usarBuscador): ?>
                                    <!--
                                        CASO 1: pocas ciudades (hasta 50) -> un <select> normal.
                                        Cada <option> tiene como value el ID de la ciudad
                                        (eso es lo que se guarda en personas.ciudad_id).
                                    -->
                                    <select class="form-select" id="ciudad_id" name="ciudad_id" required>
                                        <option value="">-- Seleccionar ciudad --</option>
                                        <?php foreach ($ciudades as $ciudad): ?>
                                            <option value="<?php echo $ciudad['id']; ?>"
                                                <?php echo ($esEdicion && $persona['ciudad_id'] == $ciudad['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                                (<?php echo htmlspecialchars($ciudad['provincia']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                <?php else: ?>
                                    <!--
                                        CASO 2: muchas ciudades (más de 50) -> input de texto con buscador.
                                        - El input escribe el NOMBRE de la ciudad.
                                        - El campo oculto guarda el ID (lo completa JavaScript).
                                        - El <datalist> es la lista de sugerencias del navegador.
                                        - Cada vez que se escriben 3 letras, se consulta
                                          buscarCiudad.php y se llenan las sugerencias.
                                    -->
                                    <input type="text" class="form-control" id="nombre_ciudad"
                                           list="listaCiudades" autocomplete="off"
                                           placeholder="Escribí al menos 3 letras de la ciudad..."
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['ciudad_nombre']) : ''; ?>">

                                    <!-- Campo oculto: es el que se guarda en la base de datos -->
                                    <input type="hidden" name="ciudad_id" id="ciudad_id"
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['ciudad_id']) : ''; ?>">

                                    <!-- Sugerencias que muestra el navegador -->
                                    <datalist id="listaCiudades"></datalist>

                                    <div class="form-text" id="mensajeCiudad">
                                        <?php if ($esEdicion && $persona['ciudad_nombre']): ?>
                                            Ciudad elegida: <strong><?php echo htmlspecialchars($persona['ciudad_nombre']); ?></strong>
                                        <?php else: ?>
                                            Escribí 3 letras o más y elegí la ciudad de la lista.
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ===================================================== -->
                            <!--  ARCHIVOS ADJUNTOS (foto y currículum)                   -->
                            <!-- ===================================================== -->
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Archivos adjuntos</h6>

                                    <!-- Guardamos las rutas actuales en campos ocultos: si el usuario
                                         NO sube un archivo nuevo, se conserva el que ya estaba -->
                                    <input type="hidden" name="avatar_actual"
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['avatar_path']) : ''; ?>">
                                    <input type="hidden" name="cv_actual"
                                           value="<?php echo $esEdicion ? htmlspecialchars($persona['cv_path']) : ''; ?>">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="avatar" class="form-label">Foto (avatar)</label>
                                            <input type="file" class="form-control" id="avatar" name="avatar"
                                                   accept=".jpg,.jpeg,.png,.gif,.webp">
                                            <!-- Si ya tiene foto, la mostramos para saber cuál es -->
                                            <?php if ($esEdicion && $persona['avatar_path']): ?>
                                                <div class="mt-2">
                                                    <img src="../../<?php echo htmlspecialchars($persona['avatar_path']); ?>"
                                                         alt="Avatar actual" width="80" class="rounded border">
                                                    <div class="form-text">Foto actual</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="cv" class="form-label">Currículum (CV)</label>
                                            <input type="file" class="form-control" id="cv" name="cv"
                                                   accept=".pdf,.doc,.docx">
                                            <!-- Si ya tiene CV, mostramos el link para abrirlo -->
                                            <?php if ($esEdicion && $persona['cv_path']): ?>
                                                <div class="mt-2">
                                                    <a href="../../<?php echo htmlspecialchars($persona['cv_path']); ?>"
                                                       target="_blank" class="small">Ver CV actual</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="form-text">
                                        Tamaño máximo: 3 MB por archivo.
                                        Los archivos quedan en <code>files/avatars</code> y <code>files/cv</code>.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <!-- El <textarea> no usa value: el texto va entre las etiquetas -->
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo $esEdicion ? htmlspecialchars($persona['observaciones']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn <?php echo $esEdicion ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php echo $esEdicion ? 'Guardar cambios' : 'Crear persona'; ?>
                                </button>
                            </div>

                            <?php if ($esEdicion): ?>
                                <div class="d-grid mt-2">
                                    <a href="editPersona.php" class="btn btn-outline-secondary">Cancelar edición</a>
                                </div>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="viewPersona.php" class="btn btn-outline-success">Ver personas registradas</a>
                        <a href="../index.php" class="btn btn-outline-primary">Inicio</a>
                        <a href="../../index.php" class="btn btn-outline-secondary">Portada</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php piePagina(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($usarBuscador): ?>
    <script>
        /*
            BUSCADOR DE CIUDADES (se usa cuando hay más de 50 ciudades)

            1. El usuario escribe en el input.
            2. Con 3 letras o más, se pide a buscarCiudad.php las ciudades que empiezan así.
            3. Las sugerencias se cargan en el <datalist> (las muestra el navegador al hacer clic).
            4. Cuando el usuario elige una sugerencia, se guarda el ID de esa ciudad
               en el campo oculto "ciudad_id" (ese es el valor que va a la base de datos).
        */

        // Guardamos aquí las ciudades que devolvió la búsqueda, para poder
        // relacionar el texto escrito con el ID de la ciudad
        let ciudadesEncontradas = [];

        const inputCiudad = document.getElementById('nombre_ciudad');
        const inputId = document.getElementById('ciudad_id');
        const lista = document.getElementById('listaCiudades');
        const mensaje = document.getElementById('mensajeCiudad');

        // Cada vez que se escribe algo en el input, buscamos
        inputCiudad.addEventListener('input', function () {
            const texto = this.value.trim();

            // Con menos de 3 letras no se busca nada
            if (texto.length < 3) {
                lista.innerHTML = '';
                ciudadesEncontradas = [];
                return;
            }

            // fetch() llama al archivo PHP y espera la respuesta
            fetch('../ciudad/buscarCiudad.php?termino=' + encodeURIComponent(texto))
                .then(respuesta => respuesta.json())          // convertimos la respuesta a JSON
                .then(datos => mostrarSugerencias(datos, texto))
                .catch(error => console.log('Error al buscar ciudades: ' + error));
        });

        // Cuando el usuario elige una sugerencia del <datalist>, se completa el ID
        inputCiudad.addEventListener('change', fijarCiudadElegida);

        function mostrarSugerencias(datos, texto) {
            // Limpiamos la lista antes de agregar las sugerencias nuevas
            lista.innerHTML = '';
            ciudadesEncontradas = [];

            if (datos.length === 0) {
                mensaje.textContent = 'No se encontraron ciudades con ese nombre.';
                return;
            }

            datos.forEach(function (ciudad) {
                // Armamos el texto que se ve en la lista: "Río Cuarto (Córdoba)"
                const etiqueta = ciudad.nombre + ' (' + ciudad.provincia + ')';
                ciudadesEncontradas.push({ id: ciudad.id, nombre: ciudad.nombre, etiqueta: etiqueta });

                // Agregamos una sugerencia al <datalist>
                const opcion = document.createElement('option');
                opcion.value = etiqueta;
                lista.appendChild(opcion);
            });

            mensaje.textContent = 'Encontradas ' + datos.length + ' ciudades. Elegí una de la lista.';
            fijarCiudadElegida(texto);
        }

        function fijarCiudadElegida(texto) {
            const escrito = (texto !== undefined ? texto : inputCiudad.value).trim();

            // Buscamos la ciudad que coincide exactamente con lo escrito
            const elegida = ciudadesEncontradas.find(c => c.etiqueta === escrito);

            if (elegida) {
                // Guardamos el ID: este es el valor que va a personas.ciudad_id
                inputId.value = elegida.id;
                mensaje.textContent = 'Ciudad elegida: ' + elegida.nombre;
            } else {
                // Si el texto no coincide con ninguna ciudad, no hay ID que guardar
                inputId.value = '';
            }
        }

        // Controlamos que se haya elegido una ciudad antes de enviar el formulario
        function validarFormulario() {
            if (inputId.value === '') {
                alert('Elegí una ciudad de la lista de sugerencias.');
                return false;
            }
            return true;
        }
    </script>
    <?php endif; ?>
</body>
</html>
