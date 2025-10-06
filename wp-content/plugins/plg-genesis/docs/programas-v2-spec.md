Programas y Cursos – API v2 (propuesta bajo `plg-genesis/v1`)

Objetivo: exponer Programas/Cursos vía WordPress REST API con DTOs homogéneos, validación y permisos, siguiendo las reglas del plugin API-first.

Convenciones
- Namespace: `plg-genesis/v1`
- Respuestas:
  - Éxito: `{ success: true, data }`
  - Error: `{ success: false, error: { code, message, details } }`
- Auth: sesión WP; mutaciones requieren `edit_users`.

DTOs

- Curso
  {
    "id": 128,
    "nombre": "Romanos",
    "nivel": "Nivel 3",
    "descripcion": "..."
  }

- Nivel de Programa
  {
    "id": 21,
    "nombre": "Nivel 1",
    "cursos": [ { "id": 128, "consecutivo": 1 } ]
  }

- Programa
  {
    "id": 12,
    "nombre": "Programa Emmaus",
    "descripcion": "...",
    "niveles": [ /* NivelDePrograma */ ],
    "cursosSinNivel": [ { "id": 200, "consecutivo": 1 } ],
    "prerequisitos": [ { "id": 3, "nombre": "Intro" } ]
  }

Endpoints

- Cursos
  - GET `/cursos` → Query: `q?`, `nivel?` → `{ items: Curso[] }`
  - POST `/cursos` → `{ nombre, nivelId, descripcion }` → `{ id }`
  - PUT `/cursos/{id}` → `{ nombre?, nivelId?, descripcion? }`

- Programas
  - GET `/programas` → Query: `q?`, `include?=all` → `{ items: Programa[] }`
  - GET `/programas/{id}` → `Programa`
  - POST `/programas` → `{ nombre, descripcion, niveles[], cursosSinNivel[], prerequisitos[] }` → `{ id }`
  - PUT `/programas/{id}` → body parcial; sincroniza niveles/asignaciones
  - DELETE `/programas/{id}` → soft delete (`deleted_at`); `?hard=true` para eliminación total (solo admin)

- Asignaciones
  - POST `/programas/{id}/asignar` → `{ estudianteId? , contactoId? }` (uno)
  - DELETE `/programas/{id}/asignar` → `{ estudianteId? , contactoId? }` (uno)

Validación y seguridad
- Sanitizar cadenas; enteros con `intval`.
- SQL parametrizado (`pg_query_params`).
- `permission_callback` por ruta; nonce `X-WP-Nonce` en mutaciones.

Frontend v2 (vistas)
- `#/programas` listado + búsqueda + paginación
- `#/programa/:id` ver/editar (modo toggle)
- `#/programas/nuevo` creación
- Modal de asignación desde Estudiante/Contacto


