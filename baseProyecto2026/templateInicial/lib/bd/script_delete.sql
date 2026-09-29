/*
    SCRIPT DE DELETE - Eliminar una persona
    ========================================
    Este script demuestra la operación DELETE (la "D" de CRUD).

    Importante en DELETE:
    - DELETE FROM tabla WHERE id = ? : borra SOLO la fila que cumple la condición
    - Sin WHERE borraría TODA la tabla (¡peligroso!)

    En este proyecto con dos tablas:
    - Se puede borrar CUALQUIER persona sin restricciones (no tiene dependencias)
    - NO se puede borrar una ciudad que tenga personas asignadas (lo impide la FOREIGN KEY)

    Ejecutar con:
      mysql -u root -p contactos3 < script_delete.sql
*/

USE contactos3;

-- -------------------------------------------------------
-- Ver el listado ANTES de borrar (para elegir un id)
-- -------------------------------------------------------
-- SELECT id, nombre, apellido, dni, ciudad_id FROM personas ORDER BY id DESC LIMIT 10;

-- -------------------------------------------------------
-- Eliminar UNA persona por su ID (ejemplo: id = 50)
-- -------------------------------------------------------
-- Cambiá el número por el id que quieras eliminar. Usamos 50 que es el último insertado.
-- Si el id no existe, el DELETE no hace nada pero tampoco da error (afecta 0 filas).

DELETE FROM personas
WHERE id = 50;

-- Verificar que se borró (debe devolver 0 filas)
-- SELECT * FROM personas WHERE id = 50;

-- Ver que el resto de la tabla sigue intacta
-- SELECT COUNT(*) AS total_personas_restantes FROM personas;

-- -------------------------------------------------------
-- OTROS EJEMPLOS COMENTADOS (descomentar para probar)
-- -------------------------------------------------------

-- Borrar por otro criterio (ej: por DNI específico)
-- DELETE FROM personas WHERE dni = '30090561';

-- Borrar la última persona insertada de una ciudad específica (ej: última de Río Cuarto)
-- DELETE FROM personas WHERE ciudad_id = 1 ORDER BY id DESC LIMIT 1;

-- -------------------------------------------------------
-- INTENTO DE BORRAR UNA CIUDAD CON PERSONAS (DEBE FALLAR)
-- -------------------------------------------------------
-- Si descomentás la siguiente línea, MySQL dará error 1451: "Cannot delete or update a parent row"
-- porque hay personas que referencian esa ciudad vía ciudad_id.
-- Es la clave foránea protegiendo la integridad.

-- DELETE FROM ciudades WHERE id = 1;

-- Para borrar una ciudad primero hay que reasignar o borrar sus personas:
-- 1) Reasignar: UPDATE personas SET ciudad_id = NULL WHERE ciudad_id = 1;
-- 2) O borrar personas: DELETE FROM personas WHERE ciudad_id = 1;
-- 3) Recién entonces: DELETE FROM ciudades WHERE id = 1;
