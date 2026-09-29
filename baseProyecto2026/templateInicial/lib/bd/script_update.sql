/*
    SCRIPT DE UPDATE - Modificar una persona y una ciudad
    ======================================================
    Este script demuestra la operación UPDATE (la "U" de CRUD).

    Importante en UPDATE:
    - SET columna = valor : qué campos se cambian
    - WHERE id = ?        : A QUÉ fila se aplica el cambio. Sin WHERE se cambiarían TODAS.

    Ejecutar con:
      mysql -u root -p contactos3 < script_update.sql
    o en phpMyAdmin (pestaña SQL) con la base contactos3 seleccionada.
*/

USE contactos3;

-- -------------------------------------------------------
-- 1) UPDATE DE UNA PERSONA
-- -------------------------------------------------------
-- Ejemplo: Julián Conde (id=1) cambia su email y su ciudad de nacimiento
-- Antes: jconde@ac.unrc.edu.ar, ciudad_id=1 (Río Cuarto)
-- Después: julian.conde.2026@gmail.com, ciudad_id=2 (Córdoba capital)

-- Ver el estado ANTES (descomentar para probar)
-- SELECT id, nombre, apellido, email, ciudad_id FROM personas WHERE id = 1;

UPDATE personas
SET email = 'julian.conde.2026@gmail.com',
    telefono = '3584000001',
    direccion = 'Av. Reforma 999 - Depto 2B',
    observaciones = 'Actualizado vía script_update.sql - cambio de contacto'
WHERE id = 1;

-- Verificar el cambio
-- SELECT id, nombre, apellido, email, telefono, direccion, observaciones, ciudad_id FROM personas WHERE id = 1;

-- Otro ejemplo: cambiar el lugar de nacimiento de María Pérez (id=2) de Córdoba (2) a Rosario (7)
UPDATE personas
SET ciudad_id = 7
WHERE id = 2;

-- -------------------------------------------------------
-- 2) UPDATE DE UNA CIUDAD
-- -------------------------------------------------------
-- Ejemplo: corregir la descripción y código postal de Río Cuarto (id=1)
-- y ajustar sus coordenadas con más precisión

-- Ver el estado ANTES
-- SELECT id, nombre, provincia, latitud, longitud, codigo_postal, descripcion FROM ciudades WHERE id = 1;

UPDATE ciudades
SET descripcion = 'Capital alterna de Córdoba, polo universitario y agrobusiness del sur cordobés',
    codigo_postal = 'X5800',
    latitud = -33.130000,
    longitud = -64.350000,
    fecha_fundacion = '1786-11-11'
WHERE id = 1;

-- Otro ejemplo: actualizar la descripción de Bariloche (id=20) para agregar detalle turístico
UPDATE ciudades
SET descripcion = 'Ciudad andina a orillas del Nahuel Huapi, capital nacional del turismo aventura y chocolate artesanal',
    latitud = -41.133472,
    longitud = -71.310360
WHERE id = 20;

-- Verificar los cambios
-- SELECT id, nombre, provincia, codigo_postal, latitud, longitud, descripcion FROM ciudades WHERE id IN (1, 20);

-- -------------------------------------------------------
-- NOTA DIDÁCTICA: UPDATE con JOIN (opcional, avanzado)
-- -------------------------------------------------------
-- Si quisieras actualizar todas las personas que nacieron en una ciudad con un dato desactualizado,
-- podrías usar JOIN en el UPDATE (no común en este proyecto, pero útil para mantenimiento masivo).
-- Ejemplo comentado: agregar una nota a todas las personas de Río Cuarto
-- UPDATE personas p JOIN ciudades c ON p.ciudad_id = c.id
-- SET p.observaciones = CONCAT(COALESCE(p.observaciones,''), ' | Revisado 2026')
-- WHERE c.nombre = 'Río Cuarto';
