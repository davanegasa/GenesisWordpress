# Changelog

## [Unreleased]
- Pending: granular permission review per role/capability.
- Pending: profile settings documentation in Ajustes.

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
