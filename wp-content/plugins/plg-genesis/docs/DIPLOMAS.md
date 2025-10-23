# Sistema de Diplomas

Sistema simplificado para gestionar emisión y entrega de diplomas a estudiantes y contactos que completan programas o niveles.

## Características

- ✅ **Cálculo automático de elegibilidad** basado en progreso real (≥70% en todos los cursos)
- 🎓 **Dos tipos de diplomas**:
  - **Programa Completo**: Por completar todos los cursos de un programa
  - **Nivel**: Por completar todos los cursos de un nivel específico
- 📋 **Registro de emisión y entrega** separados (permite seguimiento de diplomas pendientes)
- 📊 **Acta de Cierre**: Vista consolidada de todos los diplomas de un contacto
- 🔄 **Versionamiento**: Los diplomas registran bajo qué versión del programa se completaron
- 🚫 **Sin duplicados**: No se puede emitir el mismo diploma dos veces

## Modelo de Datos

### Tabla: `diplomas_entregados`

```sql
diplomas_entregados (
  id                    SERIAL PRIMARY KEY,
  tipo                  VARCHAR(50),          -- 'programa_completo' o 'nivel'
  programa_id           INTEGER,
  nivel_id              INTEGER,              -- NULL si tipo = 'programa_completo'
  version_programa      INTEGER,
  estudiante_id         INTEGER,              -- NULL si es contacto
  contacto_id           INTEGER,              -- NULL si es estudiante
  fecha_emision         DATE,
  fecha_entrega         DATE,                 -- NULL = pendiente de entrega
  entregado_por         INTEGER,              -- Usuario WordPress que registró la entrega
  notas                 TEXT,
  created_at            TIMESTAMP,
  updated_at            TIMESTAMP
)
```

## API Endpoints

### 1. Obtener Diplomas Elegibles

```http
GET /wp-json/plg-genesis/v1/diplomas/elegibles?contactoId={id}
GET /wp-json/plg-genesis/v1/diplomas/elegibles?estudianteId={id}
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "tipo": "programa_completo",
      "programa_id": 1,
      "nivel_id": null,
      "version": 2
    },
    {
      "tipo": "nivel",
      "programa_id": 1,
      "nivel_id": 3,
      "version": 2
    }
  ]
}
```

### 2. Listar Diplomas Emitidos

```http
GET /wp-json/plg-genesis/v1/diplomas?contactoId={id}
GET /wp-json/plg-genesis/v1/diplomas?contactoId={id}&pendientes=true
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tipo": "programa_completo",
      "programa_id": 1,
      "programa_nombre": "Fuente de Luz",
      "nivel_id": null,
      "nivel_nombre": null,
      "version_programa": 2,
      "fecha_emision": "2025-01-15",
      "fecha_entrega": null,
      "entregado": false,
      "notas": null
    }
  ]
}
```

### 3. Emitir un Diploma

```http
POST /wp-json/plg-genesis/v1/diplomas/emitir
Content-Type: application/json

{
  "tipo": "programa_completo",
  "programaId": 1,
  "version": 2,
  "contactoId": 123,
  "notas": "Diploma generado en ceremonia 2025"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "diplomaId": 45,
    "message": "Diploma emitido exitosamente"
  }
}
```

### 4. Emitir Todos los Elegibles

```http
POST /wp-json/plg-genesis/v1/diplomas/emitir-todos
Content-Type: application/json

{
  "contactoId": 123
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "emitidos": [1, 2, 3],
    "total_emitidos": 3,
    "errores": [],
    "total_errores": 0
  }
}
```

### 5. Registrar Entrega

```http
PUT /wp-json/plg-genesis/v1/diplomas/{id}/entrega
Content-Type: application/json

{
  "fechaEntrega": "2025-01-20",
  "notas": "Entregado en ceremonia presencial"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "message": "Entrega registrada exitosamente"
  }
}
```

### 6. Acta de Cierre

```http
GET /wp-json/plg-genesis/v1/diplomas/acta-cierre?contactoId={id}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "elegibles": [...],          // Diplomas que puede obtener
    "emitidos": [...],            // Todos los diplomas emitidos
    "pendientes_entrega": [...]   // Diplomas emitidos pero no entregados
  }
}
```

## Uso desde el Frontend

### Integrar en Detalle de Estudiante/Contacto

```html
<!-- En el HTML -->
<link rel="stylesheet" href="../../components/diplomas.css">
<div id="diplomas-section"></div>
<script src="../../components/diplomas.js"></script>
```

```javascript
// En el JS
await renderDiplomasSection(null, contactoId, 'diplomas-section');
// o
await renderDiplomasSection(estudianteId, null, 'diplomas-section');
```

### Vista de Acta de Cierre

URL: `/wp-content/plugins/plg-genesis/frontendv2/pages/contactos/acta-cierre.html?id={contactoId}`

Características:
- Resumen ejecutivo consolidado
- Secciones separadas por estado (elegibles, pendientes, entregados)
- Acciones rápidas desde la misma vista
- Optimizada para impresión (Ctrl+P o botón "Imprimir")

## Lógica de Elegibilidad

Un estudiante/contacto es elegible para un diploma cuando:

### Diploma de Programa Completo
1. Tiene el programa asignado
2. Ha completado **todos** los cursos del programa con porcentaje ≥ 70%
3. No tiene ya un diploma emitido de tipo `programa_completo` para ese programa

### Diploma de Nivel
1. Tiene el programa asignado
2. Ha completado **todos** los cursos del nivel con porcentaje ≥ 70%
3. No tiene ya un diploma emitido de tipo `nivel` para ese nivel

**Nota**: Si un estudiante repitió un curso, se toma el porcentaje **máximo** obtenido.

## Flujo de Trabajo Típico

### Escenario 1: Emisión Individual

1. Usuario navega a detalle de estudiante/contacto
2. Ve sección "Diplomas" con elegibles resaltados en verde
3. Hace clic en "Emitir" para un diploma específico
4. Sistema valida elegibilidad y crea registro con `fecha_emision` = hoy
5. Diploma pasa a "Pendientes de Entrega" (amarillo)

### Escenario 2: Emisión Masiva

1. Usuario navega a detalle de contacto
2. Hace clic en "Emitir Todos los Elegibles"
3. Sistema emite todos los diplomas elegibles en una transacción
4. Muestra resumen de emisiones exitosas y errores (si hubo)

### Escenario 3: Entrega Física

1. Usuario ve diploma en "Pendientes de Entrega"
2. Hace clic en "Registrar Entrega"
3. Opcionalmente ingresa fecha y notas
4. Sistema registra `fecha_entrega` y `entregado_por`
5. Diploma pasa a "Entregados" (gris)

### Escenario 4: Acta de Cierre

1. Usuario navega a `/acta-cierre.html?id={contactoId}`
2. Ve resumen consolidado en formato de acta formal
3. Puede emitir diplomas elegibles directamente desde la vista
4. Puede imprimir el acta para archivo físico

## Instalación

### 1. Ejecutar Migración

```bash
# Desde Docker
docker exec -it genesiswordpress-postgres-1 \
  psql -U emmaus_admin -d emmaus_estudiantes \
  -f /docker-entrypoint-initdb.d/plg-genesis/migration/v1_10.diplomas.sql

# o desde el host si tienes psql
psql -U emmaus_admin -h localhost -d emmaus_estudiantes \
  -f wp-content/plugins/plg-genesis/migration/v1_10.diplomas.sql
```

### 2. Verificar Instalación

```sql
-- Debe existir la tabla
SELECT * FROM diplomas_entregados LIMIT 1;

-- Verificar constraints
SELECT conname, contype 
FROM pg_constraint 
WHERE conrelid = 'diplomas_entregados'::regclass;
```

### 3. Rutas ya Registradas

Las rutas se registran automáticamente en `backend/bootstrap.php`. No se requiere configuración adicional.

## Consideraciones

### Versionamiento de Programas

Los diplomas registran `version_programa` para mantener histórico preciso:
- Si un programa cambia sus cursos (nueva versión), los diplomas antiguos siguen siendo válidos
- Los nuevos diplomas se emiten bajo la nueva versión
- Permite auditoría: "¿Bajo qué estructura del programa se obtuvo este diploma?"

### Prevención de Duplicados

El sistema previene duplicados mediante:
- Índices únicos en DB: `(tipo, programa_id, nivel_id, destinatario)`
- Validación en backend antes de emitir
- Query de elegibilidad excluye diplomas ya emitidos

### Rendimiento

Para instalaciones con muchos estudiantes/diplomas:
- Los índices garantizan consultas rápidas
- La query de elegibilidad es eficiente (usa MAX(porcentaje) por curso)
- Considera agregar índice en `(contacto_id, fecha_emision)` si haces consultas por rango de fechas

## Próximas Mejoras

- [ ] Plantillas de diplomas en PDF generadas dinámicamente
- [ ] Envío de diploma por email al emitir
- [ ] Dashboard de diplomas emitidos por periodo
- [ ] Notificaciones cuando un estudiante se vuelve elegible
- [ ] Campos personalizables (número de diploma, notas adicionales)
- [ ] Firma digital de diplomas

## Troubleshooting

### "No es elegible para este diploma"

Verificar:
1. ¿Tiene el programa asignado? → Query `programas_asignaciones`
2. ¿Completó todos los cursos con ≥70%? → Query `estudiantes_cursos`
3. ¿Ya tiene ese diploma emitido? → Query `diplomas_entregados`

```sql
-- Ejemplo de debugging para contacto_id=123, programa_id=1
-- 1. Verificar asignación
SELECT * FROM programas_asignaciones 
WHERE contacto_id = 123 AND programa_id = 1;

-- 2. Ver cursos del programa
SELECT pc.curso_id, c.nombre 
FROM programas_cursos pc 
JOIN cursos c ON pc.curso_id = c.id
WHERE pc.programa_id = 1 AND pc.version = 2;

-- 3. Ver progreso del contacto en esos cursos
SELECT ec.curso_id, MAX(ec.porcentaje) as max_porcentaje
FROM estudiantes_cursos ec
WHERE ec.contacto_id = 123
AND ec.curso_id IN (SELECT curso_id FROM programas_cursos WHERE programa_id = 1 AND version = 2)
GROUP BY ec.curso_id;

-- 4. Ver diplomas ya emitidos
SELECT * FROM diplomas_entregados 
WHERE contacto_id = 123 AND programa_id = 1;
```

### Error al emitir: "duplicate key value violates unique constraint"

El diploma ya fue emitido anteriormente. Verificar:
```sql
SELECT * FROM diplomas_entregados 
WHERE tipo = 'programa_completo' 
AND programa_id = 1 
AND contacto_id = 123;
```

## Soporte

Para dudas o reportar issues:
- Documentación del proyecto: `README.md`
- Changelog: `CHANGELOG.md`
- Issues: Crear en el repositorio del proyecto

