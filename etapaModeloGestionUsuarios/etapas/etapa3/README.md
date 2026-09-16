# Etapa 3 — Implementación de la base de datos (MySQL)

> Corresponde a `etapasdetrabajo.md` Etapa 3.

## Objetivo
Llevar el modelo al motor MySQL y verificarlo.

## Actividades (phpMyAdmin)
- Crear BD y tablas, definir PK/FK, restricciones.
- Insertar datos de prueba.
- Ejecutar consultas de verificación.

## Entregable
`etapa3.zip` con `baseDeDatos.sql` (DDL) y `script-crud.sql` (INSERT/UPDATE/DELETE/SELECT).

## En este proyecto
- Implementación base: `etapa3-BD-tablas/` y `etapa3-dosTablas/` con `sql/script.sql`.
- En Etapa 5 el script está en `sql/script.sql` (BD `contactos5`) y datos en `sql/script_inserts.sql` + `sql/seed.php`.
- FK clave: `personas.ciudad_id → ciudades.id` (normalización), y `usuario_rol` con `ON DELETE CASCADE`.

### Para ejecutar
```sql
-- en phpMyAdmin pestaña SQL o:
mysql -u root -p < sql/script.sql
mysql -u root -p < sql/script_inserts.sql
-- o:
php sql/seed.php
```
Crea 4 BD de ejemplo: ciudades (20), personas (5+), roles (3) y usuarios demo (admin/directivo/operador).
