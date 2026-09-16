<?php
/*
    funcionesHTML.php - Componentes HTML reutilizables (ETAPA 5)
*/

function piePagina() {
    $anio = date('Y');
    echo '
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-1">
                <strong>Etapa 5 - Gestión Usuarios</strong> &mdash; PHP + MySQL + Bootstrap 5
            </p>
            <small class="text-white-50">
                Proyecto educativo IPEAyT 186 &middot; ' . $anio . ' &middot; Hecho con fines didácticos
            </small>
        </div>
    </footer>
    ';
}

function mostrarAlerta($texto, $tipo = 'info') {
    $textoSeguro = htmlspecialchars($texto);
    echo '<div class="alert alert-' . $tipo . ' text-center" role="alert">' . $textoSeguro . '</div>';
}

function navbar($usuario = null, $tipos = []) {
    $esAdmin = in_array(1, $tipos);
    $esDirectivoAdmin = count(array_intersect([1,2], $tipos)) > 0;
    $username = $usuario ? htmlspecialchars($usuario) : 'Invitado';
    echo '
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
      <div class="container">
        <a class="navbar-brand" href="../home/index.php">Gestión 5</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="../home/index.php">Inicio</a></li>';
    if ($esDirectivoAdmin) {
        echo '
            <li class="nav-item"><a class="nav-link" href="../persona/viewPersona.php">Personas</a></li>
            <li class="nav-item"><a class="nav-link" href="../ciudad/viewCiudad.php">Ciudades</a></li>';
    }
    if ($esAdmin) {
        echo '
            <li class="nav-item"><a class="nav-link" href="../usuarios/viewUsuario.php">Usuarios</a></li>
            <li class="nav-item"><a class="nav-link" href="../roles/viewRol.php">Roles</a></li>';
    }
    echo '
          </ul>
          <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">' . $username . '</span>
            <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </nav>
    ';
}
