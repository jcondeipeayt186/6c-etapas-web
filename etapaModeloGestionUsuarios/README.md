# Gestión de Personas y Ciudades con Usuarios y Roles — Etapa 5

> **Proyecto:** `etapaModeloGestionUsuarios` (Etapa 5 — Aplicación final PHP + MySQL + Bootstrap 5)  
> **Base:** evolución de `etapa3-dosTablas` con **gestión de usuarios/roles N:M** y control de acceso.  
> **Curso:** IPEAyT 186 — Bases de Datos / Aplicaciones Web 6° C

---

## ¿Qué hay en el proyecto?

```
etapaModeloGestionUsuarios/
├── index.php                 # Portada pública con image.png + cartel.png + login (usuario, clave con ojito, Ingresar, Olvidé mi clave)
├── .env / .env.example       # Credenciales MySQL + correo (DB_*, MAIL_*)
├── img/
│   ├── image.png             # Imagen portada (de etapa3-dosTablas/img)
│   ├── cartel.png            # Cartel/banner superior
│   ├── mysql.png             # Logo MySQL
│   └── LogoCasta.png
├── lib/
│   ├── bd/
│   │   ├── conexion.php      # SOLO conexión (lee .env, PDO)
│   │   ├── gestionBaseDatos.php # Compat: incluye todos los módulos
│   │   ├── usuarios.php      # CRUD usuarios
│   │   ├── roles.php         # CRUD roles
│   │   ├── usuario_roles.php # N:M usuario_rol
│   │   ├── personas.php      # CRUD personas (JOIN ciudades)
│   │   └── ciudades.php      # CRUD ciudades
│   ├── html/funcionesHTML.php# piePagina(), navbar(), mostrarAlerta()
│   ├── auth/auth.php         # Helpers sesión/roles
│   └── utils/                # Utilidades transversales
│       ├── mail.php          # Librería envío correo (PHPMailer/SMTP puro/mail)
│       ├── ejemplo.php       # Prueba rápida lib/utils
│       ├── mail.log          # Log (ignorado en git, DRY_RUN)
│       └── README.md
├── src/
│   ├── auth/
│   │   ├── login.php         # POST login → verifica, fechaUltimoAcceso, sesión
│   │   ├── logout.php
│   │   └── recuperar.php     # Olvidé mi clave (usa lib/utils/mail.php si MAIL_ configurado)
│   ├── home/index.php        # Dashboard (ex-index etapa3) con control tipo 1/2
│   ├── usuarios/             # viewUsuario, editUsuario (clave con ojito), gestionUsuario
│   ├── roles/                # viewRol, editRol, gestionRol
│   ├── persona/              # viewPersona, editPersona, gestionPersona (auth tipo 1/2)
│   └── ciudad/               # viewCiudad, editCiudad, gestionCiudad
├── sql/
│   ├── script.sql            # DDL completo (contactos5)
│   ├── script_inserts.sql    # Datos demo (20 ciudades, 5 personas, 3 roles, 3 usuarios con hash correcto)
│   └── seed.php              # Seed con password_hash real (corrige hashes viejos)
└── etapas/                   # Traza etapas 1-4 según etapasdetrabajo.md
    ├── etapa1/README.md
    ├── etapa2/README.md
    ├── etapa3/README.md
    ├── etapa4/README.md
    └── etapasdetrabajo.md (copia)
```

---

## Etapas de trabajo (según `etapasdetrabajo.md`)

### Etapa 1 — Análisis de requerimientos y funcionalidades
Definir **qué hace el sistema**: entrevistas, funcionalidades, **bocetos HTML**, navegación y datos a almacenar.  
Entregable `etapa1.zip` (doc + bocetos). Ver `etapas/etapa1/README.md` y `etapa1/` en la raíz del repo (formulario sin BD, solo `index.php` + `resultado.php` con Bootstrap y `$_POST`).

### Etapa 2 — Diseño de la base de datos
DER notación Chen + MER tabulado (entidades, atributos, relaciones, cardinalidades, PK/FK).  
En el proyecto base: `etapa2-BD/` (una tabla `personas`), luego normalización.  
**Etapa 5 tabla final** ver `etapas/etapa2/README.md`:

- `ciudades` y `personas` (FK `ciudad_id`)
- `usuarios(id, username UNIQUE, password hash, fechaUltimoAcceso, fechaCreacion)`
- `roles(id, nombre UNIQUE, descripcion, tipo TINYINT)`
- `usuario_rol(id, usuario_id FK, rol_id FK, fechaCreacion, UNIQUE usuario_id+rol_id)` → N:M

### Etapa 3 — Implementación de la BD (MySQL)
Crear BD/tablas, PK/FK, restricciones, inserts de prueba, consultas.  
`etapa3-BD-tablas/` y `etapa3-dosTablas/`; en Etapa 5 `sql/script.sql` crea `contactos5`. Ejecutar con phpMyAdmin o `mysql -u root -p < sql/script.sql`.

### Etapa 4 — Diseño de la interfaz (HTML/CSS Bootstrap)
Organización de carpetas, estructura de páginas, menú, formularios, tablas, botones, mensajes, responsive.  
Bootstrap 5 desde CDN en toda la app: cards, navbar, grid, badges, alerts. Ver `etapas/etapa4/README.md`.

### Etapa 5 — Desarrollo de la Aplicación Web (PHP) — *esta carpeta*

#### 5.1 Estructura
La descrita arriba. `lib/bd/` separado por módulo (conexión sola en `conexion.php`, cada tabla en su archivo). Credenciales en `.env`.

#### 5.2 Conexión PHP–MySQL
- `lib/bd/conexion.php::obtenerConexion()` lee `.env` (fallback `localhost/root/ contactos5`), PDO `charset utf8mb4`, `ERRMODE_EXCEPTION`, prepared statements `?` + `execute([])`.
- `lib/bd/*.php` encapsula todo SQL (DAO por módulo).

#### 5.3 Módulos

**a) Autenticación — `index.php` (portada)**
- Imagen `image.png` (portada) + `cartel.png` (banner) con Bootstrap.
- Formulario `POST` a `src/auth/login.php`: `username`, `password`, botón **Ingresar**, link **Olvidé mi clave** → `src/auth/recuperar.php` (indica contactar al Administrador, quien resetea desde Usuarios→Editar).
- Si ya logueado, redirige a `src/home/index.php`. Usa Bootstrap, validación y mensajes `?error=`.

**b) Sesión y autorización — `lib/auth/auth.php`**
- `session_start()`, `$_SESSION[usuario_id, username, roles, tipos]`.
- Login: `verificarCredenciales()` (`password_verify`), `actualizarUltimoAcceso(NOW())`, `obtenerRolesDeUsuario()` → guarda tipos.
- Guardas: `requerirLogin`, `tieneTipo([1,2])`, `esAdmin()`.

**c) Home/Dashboard — `src/home/index.php`**
- Era el `index.php` de etapa3 (estadísticas `COUNT(*)`, accesos rápidos).
- **Ahora con control de roles**: `tieneTipo([1,2])` muestra cards Personas/Ciudades y botones crear; si no, alerta *"No tiene módulos disponibles"* (tipo 1 Administrador, 2 Directivo). Usuarios/Roles solo visibles si tipo 1. Muestra `navbar()` con usuario y roles.

**d) Gestión de Usuarios — `src/usuarios/`**
- `viewUsuario.php` (solo Admin): tabla con buscador `LIKE` username, badges de roles por `usuario_rol`, fechas, Modificar/Eliminar (no auto-eliminarse), conteo.
- `editUsuario.php` (alta/edición dual): username único + password `password_hash` (opcional en edición) + checkboxes múltiples de roles (N:M, `sincronizarRolesUsuario()` borra/inserta).
- `gestionUsuario.php` (POST sin HTML): ramas `eliminar/actualizar/crear` con validación (username único, clave ≥4, etc.) y `header(Location)`.

**e) Gestión de Roles — `src/roles/`**
- `viewRol.php`, `editRol.php`, `gestionRol.php` con mismo patrón. Campos: `nombre`, `descripcion`, `tipo TINYINT` (1=Administrador, 2=Directivo, 3=Operador...). Validación y bloqueo de borrado si `hayUsuariosEnRol()`.

**f) Personas y Ciudades — `src/persona/` y `src/ciudad/`**
- Migrados de etapa3, ahora con `session_start()` y guarda `tipo IN (1,2)` + `navbar()` y `piePagina()`. Mantienen buscador GET, JOIN `personas LEFT JOIN ciudades`, select FK, validación lat/long y verificación FK antes de DELETE.

**Flujo Etapa 5**
```
index.php (login) --POST--> src/auth/login.php --SELECT usuarios + password_verify + UPDATE fechaUltimoAcceso--> MySQL
         ^                          |--carga roles N:M--> $_SESSION[tipos]
         |                          └--> header Location: src/home/index.php
src/home/index.php --COUNT(*)--> MySQL --si tipo 1/2--> muestra Personas/Ciudades + usuarios/roles (solo 1)
         |--else--> alerta "sin módulos"
viewUsuario/viewRol/viewPersona/viewCiudad --GET ?busqueda --> lib/bd/*::obtener... LIKE --> tabla
editX.php?id=X --GET--> obtenerPorId + precarga --POST--> gestionX.php --INSERT/UPDATE/DELETE + usuario_rol--> MySQL --header--> viewX.php
logout --> src/auth/logout.php --> destroy session --> index.php
```

---

## Instalación y ejecución

1. **Clonar y entrar**
   ```bash
   cd etapaModeloGestionUsuarios
   ```

2. **Configurar `.env`** (copiar de ejemplo)
   ```bash
   cp .env.example .env
   # editar DB_USER, DB_PASS, DB_NAME si cambias de XAMPP/etapa3
   ```

3. **Crear BD y datos**
   ```bash
   mysql -u root -p < sql/script.sql
   mysql -u root -p < sql/script_inserts.sql
   # o con seed (hashes reales):
   php sql/seed.php
   ```

4. **Usuarios demo** (tras inserts/seed): password = `hash` de `password123` (`password` en tabla = bcrypt)
   | username | clave | rol (tipo) | acceso |
   |----------|-------|----------|--------|
   | `admin` | `admin123` | Administrador (1) | todo (personas/ciudades + usuarios/roles) |
   | `directivo` | `direc123` | Directivo (2) | personas/ciudades |
   | `operador` | `oper123` | Operador (3) | **sin módulos** (ve mensaje) |

   > Todos los hashes en `script_inserts.sql` son `password_hash('xxx', PASSWORD_DEFAULT)` genéricos (`$2y$10$92IXUN...`). Seed genera los correctos por usuario.

5. **Servir**
   ```bash
   php -S localhost:8000
   # abrir http://localhost:8000 (index.php = login)
   ```

---

## Decisiones técnicas

- **`.env` para credenciales y correo**: `lib/bd/conexion.php::cargarEnv()` y `lib/utils/mail.php::cargarConfigMail()` parsean `clave=valor` sin librerías externas. `MAIL_DRY_RUN=true` permite desarrollar sin SMTP real.
- **DAO separado**: cada módulo en su `lib/bd/*.php`; `gestionBaseDatos.php` es alias de compatibilidad que incluye todos.
- **Seguridad**: `password_hash/verify` (nunca texto plano), `htmlspecialchars()` al mostrar, `?` + `execute([])` al guardar (inyección SQL/XSS), `(int)` cast en IDs, `session` en todas las vistas con guardas. Clave con botón ojito (ver/ocultar) en login y edición de usuarios (Bootstrap Icons).
- **N:M**: `usuario_rol` con `UNIQUE(usuario_id,rol_id)` y `ON DELETE CASCADE`; sincronización borra todo y reinserta (simple para didáctica).
- **Correo desacoplado**: `lib/utils/mail.php` funciona sin Composer (fallback `mail()` / SMTP por sockets con `STARTTLS`/`AUTH LOGIN`); si existe `PHPMailer` lo usa automáticamente. Helpers `enviarMailRecuperacion`, `enviarMailBienvenida`.
- **Bootstrap 5.3 CDN + Icons**: sin instalación, 100% responsive, navbar y footer reutilizables.

---

## Carpeta `etapas/`

Conserva el histórico de las 4 primeras etapas según `etapasdetrabajo.md` (copia del documento en `etapas/etapasdetrabajo.md` y `etapas/comprendiendoAplicacionWeb.md`). Ver cada `etapas/etapaX/README.md` para detalle de entregables.

---

*Etapa 5 cierra el ciclo: de bocetos (1) y DER (2) a MySQL (3) y Bootstrap (4), la app final integra todo con login, roles N:M y control de acceso por tipo.*
