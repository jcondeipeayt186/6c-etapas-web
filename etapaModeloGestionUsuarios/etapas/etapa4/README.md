# Etapa 4 — Diseño de la interfaz (HTML + CSS + Bootstrap)

> Corresponde a `etapasdetrabajo.md` Etapa 4. **En esta carpeta la ETAPA 4 es 100% estática: solo HTML + Bootstrap 5, sin PHP ni MySQL.**

## Objetivo
Pasar de bocetos a diseño concreto de la aplicación con **maquetas navegables** que simulan la ETAPA 5 final.

## Actividades
- Organización de carpetas.
- Estructura de páginas, menú de navegación, formularios, tablas, botones, mensajes, diseño responsive.
- Uso de componentes Bootstrap 5 (cards, navbar, grid, tables, badges, alerts, forms).

No se exige funcionalidad PHP completa aún — por eso todo es HTML fijo con datos de ejemplo (como si vinieran de MySQL).

## Entregable
`etapa4.zip` con la carpeta del proyecto (esta carpeta `sitio/` ya es el contenido del zip para esta etapa).

## ¿Qué hay en `sitio/`?

Sitio estático que **replica todas las páginas de ETAPA 5** pero sin lógica de servidor. Cada vez que en ETAPA 5 PHP genera contenido distinto según datos/sesión, aquí hay **variantes `index-v1`, `index-v2`, etc.** para mostrar los estados posibles.

```
sitio/
├── index.html                  # Login portada (image.png + cartel.png + form usuario/clave con ojito, Ingresar, Olvidé clave)
├── auth/
│   └── recuperar.html          # Olvidé mi clave (informativo + form simulado de envío por mail)
├── home/
│   ├── index.html              # v1: Administrador (tipo 1) — 4 cards: Personas 52, Ciudades 20, Usuarios 3, Roles 3 (todos habilitados)
│   ├── index-v2.html           # v2: Directivo (tipo 2) — Personas/Ciudades habilitados, Usuarios/Roles deshabilitados (opacity)
│   ├── index-v3.html           # v3: Operador (tipo 3) — "Sin módulos disponibles" (sin tipo 1/2)
│   └── index-v4.html           # v4: BD vacía — Personas 0 / Ciudades 0 + alerta "No hay ciudades, cree una primero"
├── usuarios/
│   ├── viewUsuario.html        # Listado con 3 usuarios ficticios (admin→tipo1, directivo→tipo2, operador→tipo3), buscador, badges, tabla
│   ├── viewUsuario-v2.html     # v2: sin resultados (búsqueda "zzz" y tabla vacía) — simula empty() / LIKE sin coincidencias
│   ├── editUsuario.html        # Alta: username + clave con ojito + checkboxes N:M Roles (vacío)
│   └── editUsuario-v2.html     # v2: Edición precargada (directivo) + error "Username ya existe" + clave opcional
├── roles/
│   ├── viewRol.html            # Listado 3 roles (Admin 1, Directivo 2, Operador 3) con conteo usuarios, buscador
│   ├── viewRol-v2.html         # v2: sin resultados (zzz)
│   ├── editRol.html            # Alta: nombre, descripción, select tipo 1-4
│   └── editRol-v2.html         # v2: Edición precargada + error "Ya existe un rol con ese nombre"
├── persona/
│   ├── viewPersona.html        # Listado 5 personas con JOIN ciudad simulado (Río Cuarto, Córdoba...), buscador, badge count
│   ├── viewPersona-v2.html     # v2: sin resultados (zzz) + tabla vacía inicial — simula dos estados PHP distintos
│   ├── editPersona.html        # Alta: 12 campos + select ciudad (5 opciones) — simula foreach ciudades
│   └── editPersona-v2.html     # v2: Edición precargada id=2 (María Pérez, Córdoba) con select selected
└── ciudad/
    ├── viewCiudad.html         # Listado 5 ciudades con lat/long, código postal, fundación
    ├── viewCiudad-v2.html      # v2: sin resultados + alerta error FK "No se puede eliminar, hay personas"
    ├── editCiudad.html         # Alta: nombre, provincia, lat/long, código postal, fecha fundación, descripción
    └── editCiudad-v2.html      # v2: Edición precargada id=1 (Río Cuarto) + error validación latitud 999
├── img/
│   ├── image.png, cartel.png, mysql.png, LogoCasta.png (copiados de ../../img)
```

### ¿Cómo se simulan los datos?
Cada tabla/form tiene **datos fijos** que imitan un `SELECT` real:
- Home: `52` personas, `20` ciudades (como `script_inserts.sql` de ETAPA 5)
- Personas: 5 filas con `ciudad_nombre` por JOIN (Río Cuarto, Córdoba...)
- Ciudades: latitud `-33.123456`, etc.
- Usuarios/Roles: badges por `tipo` con colores (danger=Admin, primary=Directivo, secondary=Otros)

### Variantes v1/v2/v3...
Cuando en ETAPA 5 PHP decide qué mostrar (`if ($tieneModulosPersonas)`, `if (empty($personas))`, `if (hayPersonasEnCiudad())`, `?error=`, `$_GET['id']` para precargar), aquí hay HTML separados:
- `home/index.html` vs `index-v3.html` = `tipo IN (1,2)` true/false
- `viewPersona.html` vs `viewPersona-v2.html` = `count>0` vs `empty` / `LIKE sin match`
- `editPersona.html` (vacío) vs `editPersona-v2.html` (value="María") = `?id=` presente/ausente
- `editCiudad-v2.html` con `latitud=999` + alert rojo = validación `lat < -90 || lat > 90`

### Cómo probar (sin PHP ni MySQL)
```bash
# Opción 1: abrir directo en navegador
xdg-open etapas/etapa4/sitio/index.html

# Opción 2: servidor estático
cd etapas/etapa4/sitio
python3 -m http.server 8001
# abrir http://localhost:8001
# navegar: login → home/index.html → probar variantes v2/v3/v4 vía links o escribiendo URL
```

Todo usa **Bootstrap 5.3.3 + Bootstrap Icons 1.11.3 desde CDN**, sin CSS propio extra (solo `max-height` para imágenes). Copiá `sitio/` a `etapa4.zip` para el entregable.

### Relación con ETAPA 5
| Aspecto | ETAPA 4 (esta carpeta) | ETAPA 5 (raíz `etapaModeloGestionUsuarios/`) |
|---------|------------------------|---------------------------------------------|
| Login | `sitio/index.html` form → `home/index.html` sin validación | `index.php` → `src/auth/login.php` con `password_verify` + sesión |
| Home | 4 HTML fijos por rol (admin/directivo/operador/vacío) | `src/home/index.php` decide con `tieneTipo([1,2])` y `COUNT(*)` |
| Listados | Tabla con 5 filas fijas | `obtenerTodasLasPersonas($conexion,$busqueda)` con `LIKE` y `JOIN` |
| Formularios | `onsubmit="alert(...)"` simulado | `POST` a `gestion*.php` → `INSERT/UPDATE` con `?` |
| Recuperar | Form estático `alert` | `lib/utils/mail.php::enviarMailRecuperacion()` con SMTP/PHPMailer |
| Navbar | Fijo por variante (admin ve todo, operador solo Inicio) | `lib/html/funcionesHTML.php::navbar()` dinámico según `$_SESSION['tipos']` |

En ETAPA 4 validás el **diseño**; en ETAPA 5 reemplazás cada `*.html` por su `*.php` con la misma estructura Bootstrap pero datos reales de MySQL.
