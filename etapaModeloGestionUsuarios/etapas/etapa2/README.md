# Etapa 2 — Diseño de la base de datos

> Corresponde a `etapasdetrabajo.md` Etapa 2.

## Objetivo
Modelar los datos que el sistema debe persistir.

## Actividades
- Identificación de entidades, atributos, relaciones y cardinalidades.
- Diagrama Entidad-Relación (DER) notación Chen.
- Modelo Entidad-Relación tabulado (tablas, PK, FK).

### DER (resumen)
```
Ciudades (1) ──< Personas (N)   // una ciudad, muchas personas (ciudad_id FK)
Usuarios (N) ──< Usuario_Rol >── Usuarios-Roles (N) ──> Roles (N)  // N:M
```

## Entregable
`etapa2.zip` con DER + tabulación.

## En este proyecto
- Etapa 2 en la raíz: `etapa2-BD/`, `etapa2-BD-Filtro/`, `etapa2-BD-FiltroyUpdate/` muestran la evolución a una tabla `personas`.
- En Etapa 5 la tabulación final es:

| Tabla | Columnas | PK | FK |
|-------|----------|----|----|
| ciudades | id, nombre, provincia, latitud, longitud, codigo_postal, descripcion, fecha_fundacion | id | — |
| personas | id, nombre, apellido, dni, cuit, fecha_nacimiento, email, telefono, direccion, ciudad_id, observaciones | id | ciudad_id → ciudades(id) |
| usuarios | id, username (UNIQUE), password (hash), fechaUltimoAcceso, fechaCreacion | id | — |
| roles | id, nombre (UNIQUE), descripcion, tipo (TINYINT) | id | — |
| usuario_rol | id, usuario_id, rol_id, fechaCreacion | id | usuario_id→usuarios, rol_id→roles + UNIQUE(usuario_id,rol_id) |

Ver `sql/script.sql` para el DDL y `comprendiendoAplicacionWeb.md` §3.1 para normalización.
