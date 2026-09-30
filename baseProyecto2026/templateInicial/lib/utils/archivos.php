<?php
/*
    lib/utils/archivos.php - Subida de archivos (avatar y currículum)

    Cuando en un formulario usamos <input type="file">, el archivo elegido
    NO viaja como texto normal: viaja en el array superglobal $_FILES.

    Estructura de un elemento de $_FILES:
        $_FILES['avatar']['name']     = nombre original  (" foto.jpg ")
        $_FILES['avatar']['tmp_name'] = nombre temporal   (" /tmp/php1234 ")
        $_FILES['avatar']['size']     = tamaño en bytes
        $_FILES['avatar']['error']    = 0 si salió bien, otro número si falló
        $_FILES['avatar']['type']     = tipo MIME ("image/jpeg")

    Para guardar el archivo usamos la función move_uploaded_file(), que
    Mueve el archivo temporal del servidor a la carpeta que elijamos.

    En este proyecto los archivos se guardan en:
        files/avatars/  -> la foto de la persona
        files/cv/       -> el currículum

    En la base de datos NO se guarda el archivo, sino la RUTA
    (por ejemplo "files/avatars/avatar_1712.jpg").
*/

// Las funciones cargarEnv() y valorEntorno() están en varios.php
require_once __DIR__ . '/varios.php';

// Guarda el último error para poder mostrarlo en la página
$ultimoErrorArchivo = '';

/**
 * Devuelve el mensaje del último error de subida.
 *
 * @return string Texto con el error (vacío si no hubo error)
 */
function obtenerUltimoErrorArchivo() {
    global $ultimoErrorArchivo;
    return $ultimoErrorArchivo;
}

/**
 * Devuelve la ruta de la carpeta donde se guardan los archivos subidos.
 *
 * @return string Ruta absoluta de la carpeta (por ejemplo /var/www/proyecto/files)
 */
function carpetaArchivos() {
    // La carpeta sale del .env (CARPETA_ARCHIVOS=files)
    cargarEnv(__DIR__ . '/../../.env');
    $carpeta = valorEntorno('CARPETA_ARCHIVOS', 'files');

    // __DIR__ es la carpeta de este archivo (lib/utils), ../.. es la raíz del proyecto
    return dirname(__DIR__, 2) . '/' . $carpeta;
}

/**
 * Guarda un archivo subido por el usuario.
 *
 * @param array|null $archivo   Un elemento de $_FILES (ej: $_FILES['avatar'])
 * @param string     $subcarpeta Carpeta destino: "avatars" o "cv"
 * @param string     $prefijo   Prefijo del nombre (ej: "avatar" o "cv")
 * @param array      $extensiones Permitidas (ej: array('jpg','png'))
 * @return string|null La ruta guardada (ej: "files/avatars/avatar_123.jpg")
 *                    o null si no se envió ningún archivo
 */
function guardarArchivoSubido($archivo, $subcarpeta, $prefijo, $extensiones = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx')) {
    global $ultimoErrorArchivo;
    $ultimoErrorArchivo = '';

    // Si no llegó ningún archivo, no es un error: el campo es opcional
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    // Si hubo algún error en la subida
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $ultimoErrorArchivo = 'No se pudo subir el archivo (código de error: ' . $archivo['error'] . ').';
        return null;
    }

    // Controlamos el tamaño máximo (sale del .env: TAMANIO_MAXIMO_MB)
    cargarEnv(__DIR__ . '/../../.env');
    $maximoBytes = valorEntorno('TAMANIO_MAXIMO_MB', '3') * 1024 * 1024;
    if ($archivo['size'] > $maximoBytes) {
        $ultimoErrorArchivo = 'El archivo es muy grande. Máximo permitido: ' . valorEntorno('TAMANIO_MAXIMO_MB', '3') . ' MB.';
        return null;
    }

    // Revisamos la extensión del archivo (pathinfo + strtolower)
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensiones)) {
        $ultimoErrorArchivo = 'Tipo de archivo no permitido. Se permiten: ' . implode(', ', $extensiones) . '.';
        return null;
    }

    // Creamos la carpeta destino si todavía no existe
    $rutaCarpeta = carpetaArchivos() . '/' . $subcarpeta;
    if (!is_dir($rutaCarpeta)) {
        mkdir($rutaCarpeta, 0775, true);
    }

    // IMPORTANTE: el archivo lo guarda el SERVIDOR WEB, no el usuario.
    // Apache, por ejemplo, corre como el usuario www-data, así que la carpeta
    // tiene que darle permiso de escritura. Si no, move_uploaded_file()
    // falla con "Permission denied" y no se guarda ni la foto ni el CV.
    // Con is_writable() lo detectamos antes y podemos explicar qué hacer.
    if (!is_writable($rutaCarpeta)) {
        $ultimoErrorArchivo = 'El servidor no tiene permiso de escritura en la carpeta '
            . $rutaCarpeta
            . '. En Linux hay que darle permiso al usuario del servidor (www-data): sudo chown -R www-data:www-data '
            . dirname($rutaCarpeta);
        return null;
    }

    // Armamos un nombre nuevo para que dos archivos con el mismo nombre
    // no se pisen entre sí (ej: avatar_1712345678_1234.jpg)
    $nombreArchivo = $prefijo . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $rutaCompleta  = $rutaCarpeta . '/' . $nombreArchivo;

    // move_uploaded_file(): mueve el archivo temporal a nuestra carpeta
    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        $ultimoErrorArchivo = 'No se pudo guardar el archivo en el servidor. Revisá los permisos de la carpeta '
            . $rutaCarpeta . '.';
        return null;
    }

    // Devolvemos la ruta RELATIVA a la raíz del proyecto
    // (así la base de datos guarda algo corto como "files/avatars/avatar_171.jpg")
    $carpetaRelativa = valorEntorno('CARPETA_ARCHIVOS', 'files');
    return $carpetaRelativa . '/' . $subcarpeta . '/' . $nombreArchivo;
}

/**
 * Borra un archivo que estaba guardado en el servidor.
 * Se usa cuando se elimina una persona o se reemplaza su foto.
 *
 * @param string $rutaRelativa Ruta guardada en la base (ej: files/cv/cv_171.jpg)
 * @return void
 */
function borrarArchivo($rutaRelativa) {
    if (empty($rutaRelativa)) {
        return;
    }
    // Sumamos la raíz del proyecto a la ruta que estaba guardada
    $rutaCompleta = dirname(__DIR__, 2) . '/' . $rutaRelativa;
    if (file_exists($rutaCompleta)) {
        unlink($rutaCompleta); // unlink() borra un archivo
    }
}
