<?php
/*
    lib/utils/varios.php - Funciones sueltas de apoyo

    Acá van las pequeñas funciones que usa toda la aplicación y que NO
    tienen que ver con la base de datos: leer el .env, formatear fechas,
    escapar textos, etc.

    Si el proyecto crece, este es el lugar para agregar nuevas utilidades.
*/

/**
 * Lee el archivo .env y guarda sus variables en $_ENV.
 *
 * El archivo .env tiene líneas con el formato  CLAVE=valor
 * Las líneas que empiezan con # son comentarios y se ignoran.
 *
 * Ejemplo de .env:
 *      DB_HOST=localhost
 *      MAIL_DRY_RUN=true
 *
 * @param string $ruta Ruta completa del archivo .env
 * @return void
 */
function cargarEnv($ruta) {
    // Si el archivo no existe, no pasa nada (se usan los valores por defecto)
    if (!file_exists($ruta)) {
        return;
    }

    // file() lee el archivo y devuelve un array con cada línea
    // FILE_IGNORE_NEW_LINES: saca el salto de línea de cada línea
    // FILE_SKIP_EMPTY_LINES: no trae las líneas vacías
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Ignoramos comentarios y líneas vacías
        if ($linea === '' || $linea[0] === '#') {
            continue;
        }

        // Solo nos interesan las líneas que tienen un "="
        if (strpos($linea, '=') === false) {
            continue;
        }

        // Separamos en dos: la clave y el valor
        // El 2 del explode es para no romper si el valor tiene "=" adentro
        list($clave, $valor) = explode('=', $linea, 2);

        // Guardamos la variable en $_ENV (si no estaba guardada antes)
        if (!array_key_exists($clave, $_ENV)) {
            $_ENV[$clave] = trim(trim($valor), "\"'"); // trim quita comillas
        }
    }
}

/**
 * Devuelve el valor de una variable del .env, o un valor por defecto
 * si la variable no existe.
 *
 * Se usa así:
 *     $host = valorEntorno('DB_HOST', 'localhost');
 *
 * @param string $nombre Nombre de la variable (por ejemplo DB_HOST)
 * @param string $porDefecto Valor a devolver si no está en el .env
 * @return string El valor de la variable
 */
function valorEntorno($nombre, $porDefecto = '') {
    // Si la variable existe en el .env, se devuelve su valor
    // (aunque esté vacía: por ejemplo DB_PASS= quiere decir "sin contraseña")
    if (array_key_exists($nombre, $_ENV)) {
        return $_ENV[$nombre];
    }
    return $porDefecto;
}

/**
 * Convierte una fecha de MySQL (2026-05-14) a formato argentino (14/05/2026).
 *
 * @param string $fecha Fecha en formato YYYY-MM-DD
 * @return string La fecha formateada, o el texto original si no se pudo convertir
 */
function fechaFormato($fecha) {
    if (empty($fecha)) {
        return '';
    }
    $objetoFecha = new DateTime($fecha);
    return $objetoFecha->format('d/m/Y');
}

/**
 * Escapa un texto antes de mostrarlo en el HTML.
 *
 * Sirve para que un nombre con "<b>" no rompa la página ni se ejecute
 * como código. Conviene usarlo SIEMPRE que mostremos datos de la base.
 *
 * @param string $texto Texto a escapar
 * @return string Texto seguro para mostrar en el HTML
 */
function textoSeguro($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
