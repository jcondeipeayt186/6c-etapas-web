/*
    SCRIPT DE CONSULTAS - Desde simples hasta complejas con JOIN y agregaciones
    =============================================================================
    Este script es 100% SELECT: no modifica datos, solo los consulta.
    Está pensado para que los estudiantes practiquen SQL leyendo los comentarios
    y ejecutando cada bloque en phpMyAdmin.

    Estructura progresiva:
    1. Consultas simples (una sola tabla)
    2. Consultas con filtro (WHERE + LIKE)
    3. Consultas con JOIN (dos tablas)
    4. Consultas sumarias / agregadas (COUNT, GROUP BY, ORDER BY, HAVING)

    Ejecutar con:
      mysql -u root -p contactos3 < script_consultas.sql
    o copiar/pegar bloque por bloque en phpMyAdmin.
*/

USE contactos3;

-- =======================================================
-- 1) CONSULTAS SIMPLES (una tabla, sin JOIN)
-- =======================================================

-- 1.1 Todas las ciudades ordenadas alfabéticamente
SELECT * FROM ciudades ORDER BY nombre ASC;

-- 1.2 Todas las personas ordenadas por id descendente (más nuevas primero)
SELECT * FROM personas ORDER BY id DESC;

-- 1.3 Solo nombre, apellido y email de las personas (proyección de columnas)
SELECT nombre, apellido, email FROM personas;

-- 1.4 Ciudades de la provincia de Córdoba
SELECT id, nombre, codigo_postal FROM ciudades WHERE provincia = 'Córdoba';

-- 1.5 Personas nacidas después del año 1990 (filtro por fecha)
SELECT nombre, apellido, fecha_nacimiento FROM personas WHERE fecha_nacimiento >= '1990-01-01' ORDER BY fecha_nacimiento ASC;

-- =======================================================
-- 2) CONSULTAS CON FILTRO LIKE (búsqueda por texto)
-- =======================================================
-- LIKE con % es lo que usa el buscador de viewPersona.php y viewCiudad.php

-- 2.1 Buscar personas cuyo nombre CONTENGA "Jul" (Julián, Julia, etc.)
SELECT id, nombre, apellido FROM personas WHERE nombre LIKE '%Jul%';

-- 2.2 Buscar ciudades que empiecen con "Villa"
SELECT id, nombre, provincia FROM ciudades WHERE nombre LIKE 'Villa%';

-- 2.3 Buscar personas cuyo apellido termine en "ez" (Fernández, Rodríguez...)
SELECT nombre, apellido FROM personas WHERE apellido LIKE '%ez';

-- 2.4 Filtro insensible a mayúsculas (MySQL con utf8mb4 ya lo es, pero se puede forzar con LOWER)
SELECT nombre, apellido FROM personas WHERE LOWER(nombre) LIKE '%maria%';

-- =======================================================
-- 3) CONSULTAS CON JOIN (dos tablas relacionadas)
-- =======================================================
-- JOIN combina personas con su ciudad de nacimiento vía ciudad_id

-- 3.1 Personas con su ciudad y provincia (JOIN básico)
SELECT p.nombre, p.apellido, c.nombre AS ciudad, c.provincia
FROM personas p
JOIN ciudades c ON p.ciudad_id = c.id
ORDER BY p.apellido ASC;

-- 3.2 Todas las personas, incluso las que no tienen ciudad asignada (LEFT JOIN)
-- Si ciudad_id es NULL, ciudad y provincia saldrán NULL pero la persona igual aparece
SELECT p.nombre, p.apellido, c.nombre AS ciudad
FROM personas p
LEFT JOIN ciudades c ON p.ciudad_id = c.id;

-- 3.3 Personas que nacieron en "Río Cuarto" (filtro sobre la tabla ciudades)
SELECT p.nombre, p.apellido, p.dni, c.nombre AS ciudad
FROM personas p
JOIN ciudades c ON p.ciudad_id = c.id
WHERE c.nombre = 'Río Cuarto';

-- 3.4 Personas de la provincia de Córdoba (JOIN + WHERE sobre provincia)
SELECT p.nombre, p.apellido, c.nombre AS ciudad, c.provincia
FROM personas p
JOIN ciudades c ON p.ciudad_id = c.id
WHERE c.provincia = 'Córdoba'
ORDER BY c.nombre ASC, p.apellido ASC;

-- 3.5 Buscar personas por nombre de ciudad (ej: todas las nacidas en ciudades que contienen "María")
SELECT p.nombre AS persona_nombre, p.apellido, c.nombre AS ciudad_nombre
FROM personas p
JOIN ciudades c ON p.ciudad_id = c.id
WHERE c.nombre LIKE '%María%';

-- 3.6 Detalle completo: persona + ciudad con todos los campos útiles
SELECT p.id AS persona_id, p.nombre, p.apellido, p.dni, p.email,
       c.nombre AS ciudad_nombre, c.provincia, c.codigo_postal, c.latitud, c.longitud
FROM personas p
LEFT JOIN ciudades c ON p.ciudad_id = c.id
ORDER BY p.id DESC
LIMIT 10;

-- =======================================================
-- 4) CONSULTAS SUMARIAS / AGREGADAS (COUNT, GROUP BY, HAVING, ORDER BY)
-- =======================================================
-- Estas son las consultas que usan funciones de agregación y agrupamiento.
-- Son las que permiten hacer "estadísticas" como las de index.php

-- 4.1 ¿Cuántas personas hay en total? (COUNT) -> lo que muestra index.php
SELECT COUNT(*) AS total_personas FROM personas;

-- 4.2 ¿Cuántas ciudades hay en total?
SELECT COUNT(*) AS total_ciudades FROM ciudades;

-- 4.3 ¿Cuántas personas hay por ciudad? (GROUP BY + COUNT + JOIN)
-- Ordenadas de mayor a menor cantidad
SELECT c.nombre AS ciudad, c.provincia, COUNT(p.id) AS cantidad_personas
FROM ciudades c
LEFT JOIN personas p ON p.ciudad_id = c.id
GROUP BY c.id, c.nombre, c.provincia
ORDER BY cantidad_personas DESC;

-- 4.4 Solo ciudades que tienen al menos 3 personas (GROUP BY + HAVING)
SELECT c.nombre AS ciudad, c.provincia, COUNT(p.id) AS cantidad
FROM ciudades c
JOIN personas p ON p.ciudad_id = c.id
GROUP BY c.id, c.nombre, c.provincia
HAVING COUNT(p.id) >= 3
ORDER BY cantidad DESC;

-- 4.5 ¿Cuántas personas hay por provincia de nacimiento? (agrupando por provincia)
SELECT c.provincia, COUNT(p.id) AS personas_por_provincia
FROM ciudades c
JOIN personas p ON p.ciudad_id = c.id
GROUP BY c.provincia
ORDER BY personas_por_provincia DESC;

-- 4.6 Ciudades sin personas (LEFT JOIN + WHERE IS NULL) -> útil para saber qué ciudades se pueden borrar
SELECT c.id, c.nombre, c.provincia
FROM ciudades c
LEFT JOIN personas p ON p.ciudad_id = c.id
WHERE p.id IS NULL;

-- 4.7 Promedio de personas por ciudad (subconsulta + AVG)
SELECT AVG(cantidad) AS promedio_personas_por_ciudad
FROM (
    SELECT COUNT(p.id) AS cantidad
    FROM ciudades c
    LEFT JOIN personas p ON p.ciudad_id = c.id
    GROUP BY c.id
) AS sub;

-- 4.8 Top 3 ciudades con más personas nacidas allí (LIMIT)
SELECT c.nombre AS ciudad, c.provincia, COUNT(p.id) AS cantidad
FROM ciudades c
JOIN personas p ON p.ciudad_id = c.id
GROUP BY c.id
ORDER BY cantidad DESC
LIMIT 3;

-- 4.9 Personas por año de nacimiento (extrae el año de la fecha)
SELECT YEAR(fecha_nacimiento) AS anio_nacimiento, COUNT(*) AS cantidad
FROM personas
GROUP BY YEAR(fecha_nacimiento)
ORDER BY anio_nacimiento ASC;

-- 4.10 Consulta compleja completa: para cada provincia, ciudad más poblada (personas nacidas)
-- (Requiere subconsulta). Avanzado: para entender cómo anidar consultas.
SELECT provincia, ciudad, cantidad
FROM (
    SELECT c.provincia, c.nombre AS ciudad, COUNT(p.id) AS cantidad,
           RANK() OVER (PARTITION BY c.provincia ORDER BY COUNT(p.id) DESC) AS ranking
    FROM ciudades c
    JOIN personas p ON p.ciudad_id = c.id
    GROUP BY c.provincia, c.nombre
) AS ranked
WHERE ranking = 1
ORDER BY cantidad DESC;

-- =======================================================
-- 5) CONSULTAS DE VERIFICACIÓN (útiles para debug)
-- =======================================================

-- 5.1 Verificar que no haya personas con ciudad_id inexistente (debería dar 0 filas si la FK funciona)
SELECT p.id, p.nombre, p.apellido, p.ciudad_id
FROM personas p
LEFT JOIN ciudades c ON p.ciudad_id = c.id
WHERE p.ciudad_id IS NOT NULL AND c.id IS NULL;

-- 5.2 Listar personas sin ciudad asignada (ciudad_id = NULL)
SELECT id, nombre, apellido, ciudad_id FROM personas WHERE ciudad_id IS NULL;
