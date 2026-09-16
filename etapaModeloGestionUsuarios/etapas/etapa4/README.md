# Etapa 4 — Diseño de la interfaz (HTML + CSS + Bootstrap)

> Corresponde a `etapasdetrabajo.md` Etapa 4.

## Objetivo
Pasar de bocetos a diseño concreto de la aplicación.

## Actividades
- Organización de carpetas.
- Estructura de páginas, menú de navegación, formularios, tablas, botones, mensajes, diseño responsive.
- Uso de componentes Bootstrap 5.

No se exige funcionalidad PHP completa aún.

## Entregable
`etapa4.zip` con la carpeta del proyecto (módulo asignado).

## En este proyecto
- Referencia: `etapa3-dosTablas/src/` ya aplica Bootstrap 5 (cards, shadow, grid, tables, badges, navbar).
- En Etapa 5 se mantiene y extiende:
  - Portada login con `image.png` + `cartel.png` + card dividida (imagen / formulario) responsive.
  - Dashboard `src/home/index.php` con 4 cards de estadísticas (personas/ciudades/usuarios/roles).
  - Navbar reutilizable `lib/html/funcionesHTML.php::navbar()` con menú según rol.
  - Tablas `table-striped table-hover` + buscador GET + badges de tipos.
  - Formularios con `select` FK (ciudad), checkboxes N:M (roles), validación y `confirm()` para eliminar.
  - Footer común `piePagina()` y alertas `mostrarAlerta()`.

Todo 100% Bootstrap desde CDN, sin CSS propio extra (solo estilos mínimos para imágenes).
