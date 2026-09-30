-- =====================================================================
-- actualizar_base.sql - Actualizaciones para bases que YA existen
-- =====================================================================
-- ¿Cuándo corro este archivo?
--   Cuando ya tenés la base de datos creada (por ejemplo la de la Etapa 3)
--   y NO la querés volver a crear desde cero con lib/bd/script.sql.
--
--   Si la base está vacía o la estás haciendo de cero: usá lib/bd/script.sql
--   y NO este archivo.
--
-- Cómo usarlo (en la terminal, desde la raíz del proyecto):
--
--   mysql -u USUARIO -p NOMBRE_BASE < lib/bd/actualizar_base.sql
--
-- Ejemplo:
--
--   mysql -u root -p contactos3 < lib/bd/actualizar_base.sql
--
-- En phpMyAdmin: menú "Importar" -> elegí este archivo.
-- =====================================================================

USE contactos3;

-- ---------------------------------------------------------------------
-- 1. Columnas de los archivos adjuntos (foto y currículum)
-- ---------------------------------------------------------------------
-- En la base NO se guarda el archivo, sino la RUTA donde quedó guardado:
--   avatar_path -> files/avatars/avatar_1712345678_1234.jpg
--   cv_path     -> files/cv/cv_1712345678_1234.pdf
-- Se pueden dejar vacías (NULL) si la persona todavía no subió nada.
--
-- OJO: si tu base todavía tiene las columnas viejas de texto
-- (ciudad, provincia, codigo_postal), mirá la Etapa 3: en este proyecto
-- esos datos se sacaron y ahora se obtienen con JOIN a la tabla ciudades.
-- ---------------------------------------------------------------------

ALTER TABLE personas
    ADD COLUMN avatar_path VARCHAR(255) NULL AFTER observaciones,
    ADD COLUMN cv_path     VARCHAR(255) NULL AFTER avatar_path;

-- ---------------------------------------------------------------------
-- 2. Verificación
-- ---------------------------------------------------------------------
-- Para comprobar que quedaron agregadas:
--
--   DESCRIBE personas;
--
-- ---------------------------------------------------------------------
