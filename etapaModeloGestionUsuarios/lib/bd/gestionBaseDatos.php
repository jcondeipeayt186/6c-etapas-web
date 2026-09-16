<?php
/*
    gestionBaseDatos.php - Compatibilidad hacia atrás

    En ETAPA 5 la conexión está en conexion.php y cada tabla tiene su propio archivo.
    Este archivo mantiene compatibilidad incluyendo todos los módulos,
    para que código antiguo que haga require 'gestionBaseDatos.php' siga funcionando.
*/
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/usuarios.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/usuario_roles.php';
require_once __DIR__ . '/personas.php';
require_once __DIR__ . '/ciudades.php';

/*
En src/ciudad/gestionCiudad por ejemplo, como en muchos otros módulos, se hace require_once __DIR__ . '/../../lib/bd/gestionBaseDatos.php';
Esto es para mantener compatibilidad con código antiguo que hacía require 'gestionBaseDatos.php' y
para que el código antiguo siga funcionando sin cambios, aunque ahora la conexión y las tablas están en archivos separados.

En este archivo ahora no hay nada implementado, pero al tener los require de los archivos de conexión y tablas, se asegura que al hacer require 'gestionBaseDatos.php' se carguen todos los módulos necesarios.

*/