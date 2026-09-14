/*
    SCRIPT SQL - ETAPA 3 dosTablas: BASE DE DATOS CON DOS TABLAS RELACIONADAS
    ==========================================================================

    En esta etapa la base de datos tiene DOS tablas relacionadas:
      1. ciudades  : guarda las ciudades (con provincia, ubicación, código postal, etc.)
      2. personas  : guarda las personas (y dónde nacieron)

    LA MEJORA (NORMALIZACIÓN):
    --------------------------
    En la Etapa 2 la tabla personas tenía columnas:
      ciudad VARCHAR(100), provincia VARCHAR(100), codigo_postal VARCHAR(10)

    Problemas de ese diseño:
    - Datos repetidos: si 20 personas son de "Río Cuarto", el texto "Río Cuarto" se repite 20 veces
    - Inconsistencia: "Rio Cuarto" vs "Río Cuarto" vs "RIO CUARTO" vs "Río 4to" son textos distintos para la misma ciudad
    - Desperdicio: cambiar el código postal de una ciudad implicaba actualizar 20 filas
    - Sin origen genuino: ciudad, provincia y código postal son DATOS DE LA CIUDAD, no de la persona

    Solución (Etapa 3): normalizar
    - Sacar esos tres campos de personas
    - Crear una tabla ciudades con: nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion
    - En personas dejar solo ciudad_id INT que es una CLAVE FORÁNEA (FOREIGN KEY) a ciudades(id)
    - Así cada ciudad se escribe UNA sola vez y las personas la referencian por su ID (1, 2, 3...)

    LA RELACIÓN:
    ------------
    ciudades:  | id | nombre     | provincia  | codigo_postal |
               | 1  | Córdoba    | Córdoba    | 5000          |
               | 2  | Rosario    | Santa Fe   | 2000          |

    personas:  | id | nombre | ciudad_id |
               | 1  | Juan   | 2         |  <-- Juan nació en Rosario (id 2)
               | 2  | Ana    | 1         |  <-- Ana nació en Córdoba (id 1)

    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
    obliga a que ciudad_id sea un id que EXISTA en ciudades. La BD mantiene la coherencia.

    Para ejecutar este script:
    1. Abrir phpMyAdmin (http://localhost/phpmyadmin)
    2. Ir a la pestaña "SQL"
    3. Pegar todo este código y presionar "Continuar"

    O desde la línea de comandos:
      mysql -u root -p < script.sql
*/

-- Creamos una base de datos nueva (separada de la etapa 2) para no mezclar tablas
CREATE DATABASE IF NOT EXISTS contactos3;
USE contactos3;

-- ===========================================
-- TABLA CIUDADES
-- ===========================================
CREATE TABLE IF NOT EXISTS ciudades (
    -- id: clave primaria, se autoincrementa (1, 2, 3...) e identifica cada ciudad
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- nombre y provincia: texto hasta 100 caracteres, no pueden estar vacíos (son obligatorios)
    nombre VARCHAR(100) NOT NULL,
    provincia VARCHAR(100) NOT NULL,

    -- latitud: DECIMAL(9,6) = 3 dígitos enteros + 6 decimales
    -- Rango real del planeta: -90 (Sur) a +90 (Norte). Ej: Río Cuarto -33.123456
    latitud DECIMAL(9,6),

    -- longitud: DECIMAL(10,6) = 4 dígitos enteros + 6 decimales
    -- Rango real: -180 (Oeste) a +180 (Este). Ej: Río Cuarto -64.349924
    longitud DECIMAL(10,6),

    -- codigo_postal: le pertenece a la CIUDAD, no a la persona (normalización)
    codigo_postal VARCHAR(10),

    -- descripcion: texto libre para describir la ciudad (opcional, por eso no tiene NOT NULL)
    descripcion TEXT,

    -- fecha_fundacion: fecha en que se fundó la ciudad (opcional)
    fecha_fundacion DATE
);

-- ===========================================
-- TABLA PERSONAS (con clave foránea a ciudades)
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

    -- CLAVE FORÁNEA:
    -- Reemplaza al campo "ciudad" de texto libre de la etapa 2.
    -- Acá solo se guarda el ID de la ciudad donde nació la persona (INT).
    -- Si la persona no tiene ciudad asignada, puede ser NULL (por eso no tiene NOT NULL)
    ciudad_id INT,

    -- NOTA: ya NO existen las columnas ciudad (texto), provincia ni codigo_postal en esta tabla.
    -- Esos datos ahora se obtienen con JOIN a la tabla ciudades.

    observaciones TEXT,

    -- Restricción de clave foránea: obliga a que ciudad_id exista en ciudades(id)
    -- Así la BD garantiza que no haya personas apuntando a ciudades inexistentes
    -- Si se intenta borrar una ciudad que tiene personas, MySQL lo impide (se debe verificar antes con hayPersonasEnCiudad)
    FOREIGN KEY (ciudad_id) REFERENCES ciudades(id)
);
