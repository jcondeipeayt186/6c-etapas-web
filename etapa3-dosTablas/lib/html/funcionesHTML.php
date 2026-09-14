<?php
/*
    funcionesHTML.php - Funciones reutilizables de HTML

    Este archivo guarda funciones que generan pedacitos de HTML
    que se repiten en varias páginas navegables.

    ¿Por qué en lib/html y no en src?
    - lib/ es para librerías reutilizables (no navegables) que cualquier página puede incluir
    - html/ indica que estas funciones generan HTML (vista), a diferencia de bd/ que genera SQL
    - Así separamos claramente: lib/bd (modelo) vs lib/html (vista compartida)

    Todas las páginas navegables (index.php, viewPersona, viewCiudad, editPersona, editCiudad)
    hacen require_once de este archivo y llaman a piePagina() antes de </body>.
*/

/**
 * Muestra el pie de página común de toda la aplicación.
 *
 * Usa clases de Bootstrap para que se vea prolijo sin escribir CSS.
 * - bg-dark: fondo oscuro
 * - text-white: texto blanco
 * - py-3: padding vertical
 * - mt-5: margen superior grande para separarlo del contenido
 *
 * Se llama simplemente con: <?php piePagina(); ?>
 */
function piePagina() {
    // Obtenemos el año actual dinámicamente (no hay que actualizarlo a mano cada año)
    $anio = date('Y');
    echo '
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <!-- Fila superior: nombre del proyecto y tecnologías -->
            <p class="mb-1">
                <strong>Etapa 3 - Dos Tablas</strong> &mdash; PHP + MySQL + Bootstrap 5
            </p>
            <!-- Fila inferior: copyright y disclaimer educativo -->
            <small class="text-white-50">
                Proyecto educativo IPEAyT 186 &middot; ' . $anio . ' &middot; Hecho con fines didácticos
            </small>
        </div>
    </footer>
    ';
}

/**
 * (Opcional) Función para mostrar una alerta Bootstrap.
 * Ejemplo: mostrarAlerta("Guardado con éxito", "success");
 * No se usa en todas las páginas, pero queda disponible para el futuro.
 *
 * @param string $texto Texto de la alerta
 * @param string $tipo Tipo de alerta Bootstrap (success, danger, warning, info)
 */
function mostrarAlerta($texto, $tipo = 'info') {
    // htmlspecialchars evita que texto con <script> se ejecute
    $textoSeguro = htmlspecialchars($texto);
    echo '<div class="alert alert-' . $tipo . ' text-center" role="alert">' . $textoSeguro . '</div>';
}
