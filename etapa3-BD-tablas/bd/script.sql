/*
    SCRIPT SQL - ETAPA 3: BASE DE DATOS CON DOS TABLAS RELACIONADAS

    En esta etapa la base de datos tiene DOS tablas:
      1. ciudades : guarda las ciudades (con su provincia, ubicacion, etc.)
      2. personas : guarda las personas (y de donde nacieron)

    LA RELACION:
    Cada persona nacio en una ciudad. Eso se representa con una CLAVE FORANEA (FOREIGN KEY)
    en la tabla personas. La columna ciudad_id guarda el id de la ciudad donde nacio la persona.

    EJEMPLO:
    ciudades:  | id | nombre     | provincia  |
               | 1  | Cordoba    | Cordoba    |
               | 2  | Rosario    | Santa Fe   |

    personas:  | id | nombre | ciudad_id |
               | 1  | Juan   | 2         |  <-- Juan nacio en Rosario (id 2)

    Para ejecutar este script:
    1. Abrir phpMyAdmin (http://localhost/phpmyadmin)
    2. Ir a la pestana "SQL"
    3. Pegar todo este codigo y presionar "Continuar"
*/

-- Creamos una base de datos nueva (separada de la etapa 2) para no mezclar tablas
CREATE DATABASE IF NOT EXISTS contactos3;
USE contactos3;

-- ===========================================
-- TABLA CIUDADES
-- ===========================================
CREATE TABLE IF NOT EXISTS ciudades (
    -- id: numero entero que se autoincrementa y es clave primaria (identifica cada ciudad)
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- nombre: texto de hasta 100 caracteres, no puede estar vacio
    nombre VARCHAR(100) NOT NULL,

    -- provincia: texto de hasta 100 caracteres
    provincia VARCHAR(100) NOT NULL,

    -- latitud: DECIMAL(9,6) = 3 digitos enteros + 6 decimales.
    -- Rango real: -90 (Sur) a +90 (Norte). Maximo permitido: 999.999999.
    latitud DECIMAL(9,6),

    -- longitud: DECIMAL(10,6) = 4 digitos enteros + 6 decimales.
    -- Rango real: -180 (Oeste) a +180 (Este). Maximo permitido: 9999.999999.
    longitud DECIMAL(10,6),

    -- codigo_postal: el codigo postal le pertenece a la CIUDAD, no a la persona.
    -- Por eso esta en esta tabla (es un dato del lugar, no de quien nacio ahi).
    codigo_postal VARCHAR(10),

    -- descripcion: texto libre para describir la ciudad (opcional)
    descripcion TEXT,

    -- fecha_fundacion: fecha en que se fundo la ciudad (opcional)
    fecha_fundacion DATE
);

-- ===========================================
-- TABLA PERSONAS (con clave foranea a ciudades)
-- ===========================================
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    direccion VARCHAR(200) NOT NULL,

    -- CLAVE FORANEA:
    -- Reemplaza al campo "ciudad" de texto libre de la etapa 2.
    -- Aca solo se guarda el ID de la ciudad donde nacio la persona.
    ciudad_id INT,

    -- NOTA: ya NO existe codigo_postal en personas, se movio a la tabla ciudades.

    observaciones TEXT,

    -- La restriccion FOREIGN KEY obliga a que ciudad_id sea un id que EXISTA en ciudades.
    -- Asi la base de datos se encarga de mantener la relacion siempre valida.
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
