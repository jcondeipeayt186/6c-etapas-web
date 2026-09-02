/*
    SCRIPT SQL - Base de datos contactos

    Este archivo crea la base de datos y la tabla personas.
    Para ejecutarlo:
    1. Abrir phpMyAdmin (http://localhost/phpmyadmin)
    2. Ir a la pestana "SQL"
    3. Pegar todo este codigo y presionar "Continuar"

    O desde la linea de comandos:
    mysql -u root -p < script.sql
*/

-- CREATE DATABASE: crea la base de datos si no existe
-- "IF NOT EXISTS" evita error si ya existe
CREATE DATABASE IF NOT EXISTS contactos;

-- Seleccionamos la base de datos para usarla
USE contactos;

-- CREATE TABLE: crea la tabla personas con sus columnas
CREATE TABLE IF NOT EXISTS personas (
    -- id: numero entero que se autoincrementa (1, 2, 3...) y es clave primaria
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- VARCHAR(100): texto de hasta 100 caracteres
    -- NOT NULL: no puede quedar vacio
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,

    -- VARCHAR(20): DNI y CUIT son texto (porque pueden tener puntos o guiones)
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,

    -- DATE: formato de fecha (YYYY-MM-DD)
    fecha_nacimiento DATE NOT NULL,

    -- VARCHAR(150): email con mas caracteres por el @ y dominio
    email VARCHAR(150) NOT NULL,

    -- VARCHAR(30): telefono puede tener guiones, parentesis, espacios
    telefono VARCHAR(30) NOT NULL,

    -- VARCHAR(200): direccion permite mas caracteres
    direccion VARCHAR(200) NOT NULL,

    -- VARCHAR(100): ciudad y provincia
    ciudad VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,

    -- VARCHAR(10): codigo postal corto
    codigo_postal VARCHAR(10) NOT NULL,

    -- TEXT: texto largo sin limite de caracteres (para observaciones)
    -- Sin NOT NULL porque es opcional
    observaciones TEXT
);
