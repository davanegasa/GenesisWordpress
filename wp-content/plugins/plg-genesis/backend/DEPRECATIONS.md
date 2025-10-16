# Deprecations y plan de migración (plg-genesis)

Este documento rastrea qué entrypoints legacy siguen activos y cuál es su reemplazo en la nueva arquitectura API-first bajo `wp-json/plg-genesis/v1/...`.

Notas generales
- No mover ni romper legacy hasta completar la migración de cada módulo en el frontend v2.
- Al finalizar cada módulo, anunciar deprecación en CHANGELOG y retirar entrypoints tras un periodo acordado.
- Regla: repositorios usan `pg_query_params`; prohibido SQL con interpolación.

## Estado actual (implementado)
- Salud: `GET /plg-genesis/v1/health`
- Nonce (cookies + REST): `GET /plg-genesis/v1/auth/nonce`
- Estudiantes (por contacto): `GET /plg-genesis/v1/estudiantes?contactoId={id}`
- Congresos (read-only): `GET /plg-genesis/v1/congresos`
- Estadísticas (dashboard): `GET /plg-genesis/v1/estadisticas?month=MM&year=YYYY`
- Contactos (búsqueda): `GET /plg-genesis/v1/contactos?q=texto&limit=20&offset=0`

## Mapeo legacy → REST (módulos)

### Estudiantes
Legacy actual:
- `backend/estudiantes/obtener_estudiantes.php`
- `backend/estudiantes/crear_estudiante.php`
- `backend/estudiantes/editar_estudiante.php`
- `backend/estudiantes/actualizar_estudiante.php`
- `backend/estudiantes/get_estudiantes.php`

REST (nuevo):
- [OK] `GET /v1/estudiantes?contactoId={id}`
- [TODO] `POST /v1/estudiantes` (crear)
- [TODO] `PUT /v1/estudiantes/{id}` (actualizar)
- [TODO] `GET /v1/estudiantes/{id}` (detalle)

Acciones para retirar legacy: cuando el front v2 consuma los endpoints POST/PUT/GET detalle y no existan referencias a los scripts legacy.

### Contactos
Legacy actual:
- `backend/contactos/obtener_contactos.php`
- `backend/contactos/detalle_contacto.php`
- `backend/contactos/crear_contacto.php`
- `backend/contactos/actualizar_contacto.php`

REST (nuevo):
- [OK] `GET /v1/contactos?q=&limit=&offset=`
- [TODO] `GET /v1/contactos/{id}`
- [TODO] `POST /v1/contactos`
- [TODO] `PUT /v1/contactos/{id}`

### Congresos
Legacy actual (extracto):
- `backend/congresos/obtener_congresos.php`
- `backend/congresos/obtener_inscritos.php`
- `backend/congresos/registrar_asistencia.php`
- `backend/congresos/validar_boleta.php`, `actualizar_estado.php`, `crear_congreso.php`, etc.

REST (nuevo):
- [OK] `GET /v1/congresos`
- [TODO] `GET /v1/congresos/{id}`
- [TODO] `POST /v1/congresos` (crear)
- [TODO] `POST /v1/congresos/{id}/registrar-asistencia`
- [TODO] `GET /v1/congresos/{id}/inscritos`

### Estadísticas
Legacy actual:
- `backend/estadisticas/obtener_estadisticas.php`

REST (nuevo):
- [OK] `GET /v1/estadisticas`

Retiro: cuando el dashboard v2 quede 100% en uso.

### Informes de Oficina
Legacy actual (Dashboard v1):
- `backend/informes/estudiantes_activos_cursos_corregidos.php` [ACTIVO - mantener para compatibilidad v1]
- `frontend/informes/oficina/ADC.php` [ACTIVO - mantener para compatibilidad v1]

REST (nuevo - Dashboard v2):
- [OK] `GET /v1/estadisticas/informe-anual?year=YYYY`

Frontend v2:
- [OK] `frontendv2/pages/informes/informe-anual.js`

Nota: Los archivos legacy se mantienen para el Dashboard v1. El Dashboard v2 usa exclusivamente la nueva API.

### Infra y seguridad
- Reemplazar `backend/db_public.php` (contiene credenciales hardcodeadas) →
  - [PLAN] Endpoints públicos solo de lectura con rate limiting, sin secretos, o eliminar si no es imprescindible.
- Centralizar conexiones: usar `backend/infrastructure/ConnectionProvider.php` y `OfficeResolver.php`.

## Criterios de Done por módulo
1) Endpoints REST implementados (lecturas y mutaciones) con `permission_callback` y nonces.
2) Frontend v2 consume exclusivamente los endpoints del módulo.
3) Logs sin errores, timeouts controlados en `client.js` y manejo de errores estandarizado.
4) Documentación actualizada (README del plugin y este archivo).
5) Anuncio de deprecación en CHANGELOG y retiro de entrypoints legacy.

## Próximos pasos sugeridos
- Estudiantes: `POST/PUT/GET detalle` con validación fuerte y nonces.
- Congresos: endpoints de detalle y asistencia.
- Contactos: detalle/creación/actualización.