# Changelog

## [Unreleased]
- Pending: profile settings documentation in Ajustes.

## [v2.1.0] - 2025-01-10
### Added
- **Sistema completo de roles y permisos:**
  - 4 roles personalizados: Super Admin, Office Manager, Office Staff, Office Viewer
  - 32 capabilities granulares (view/create/edit/delete por módulo)
  - Aislamiento por oficina: cada usuario solo accede a datos de su oficina (PostgreSQL por oficina)
  - Selector de oficina para Super Admin
- **UI de gestión de usuarios:**
  - Listar, crear, editar usuarios
  - Asignar roles y oficinas
  - Acceso vía Ajustes → Usuarios (requiere `plg_view_users`)
- **Menú dinámico:** El sidebar se filtra automáticamente según capabilities del usuario
- **Toast notifications:** Notificaciones visuales para errores de permisos (403)
- **Swagger UI mejorado:**
  - Documentación completa de la API REST
  - Autenticación vía WordPress cookies + `X-WP-Nonce`
  - Acceso en `/dashboard-v2/docs/swagger.html`
- **Migración de roles:**
  - Script SQL para migración rápida: `migration/initial_roles_migration.sql`
  - Documentación: `migration/README_MIGRATION.md`

### Changed
- **Permission callbacks:** Todos los endpoints REST ahora usan `plg_genesis_can($capability)` para validar permisos
- **Cookie validation:** Helper `plg_genesis_validate_user_from_cookie()` para autenticación en REST API
- **User cache management:** Limpieza automática de caché de WordPress para refrescar roles y capabilities
- **Logout mejorado:** Redirección correcta a `/wp-login.php?loggedout=true&wp_lang=en_US`
- **Rutas renombradas:** `/users` → `/user-management` para evitar bloqueos de WAF

### Fixed
- Formato correcto de capabilities en roles (uso de `array_fill_keys`)
- PHP serialización de roles en base de datos (`s:15` para `plg_super_admin`)
- Caché de WordPress forzando reload de roles por request
- Toast notifications con z-index alto y posición top-right
- Selector de oficina con hard reload para aplicar cambios

## [v2.0.0] - 2025-10-09
### Added
- Dashboard v2 (vanilla JS):
  - Estudiantes: unified management (search, quick view, inline edit doc/cell/email, course assignment with 409 repeat, observations list/create).
  - Ajustes ⚙️ menu: Theme presets and Logout (WP).
  - Sticky sidebar with section accordions.
- Backend Estudiantes:
  - Endpoints: list, get, put (partial), quickview, observations (GET/POST), assign courses (POST with 409 and force), all require auth.
  - Observations: usuario_nombre resolved from WordPress.

### Changed
- All main controllers now require authenticated users (is_user_logged_in).
- Router: #/estudiantes points to unified management (formerly list), separate assign route removed.
- UI: Accessible, compact modals; icon-only actions on small screens.

### Deprecated
- Dashboard v1 PHP views deprecated; kept temporarily for reference.

### Removed
- Separate “Listar Estudiantes” and “Asignar Curso” menus replaced by “Gestionar Estudiantes”.

### Fixed
- Multiple SQL and error handling fixes in Congresos/Programas/Cursos.
