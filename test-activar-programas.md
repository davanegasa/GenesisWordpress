# QA: Activar/Desactivar Programas

## ✅ Checklist de Pruebas

### 1. Migración Base de Datos
- [x] Campo `activo` agregado a `programas_asignaciones`
- [x] Índices creados correctamente
- [x] Valores por defecto `true` aplicados

### 2. Backend - Repository & Service
- [x] `ProgramasRepository::toggleAsignacion()` creado
- [x] `ProgramasService::toggleAsignacion()` creado
- [x] Queries actualizadas para filtrar por `activo = true`:
  - [x] `ContactosRepository::getContactPrograms()`
  - [x] `EstudiantesRepository::getStudentPrograms()`
  - [x] `DiplomasRepository::getProgramasConProximos()`
  - [x] `DiplomasRepository::getProximosACompletar()`

### 3. Backend - Controller & Endpoint
- [x] Endpoint `PUT /programas-asignaciones/{id}/toggle` creado
- [x] Permission callback con `plg_office_manager` y `plg_super_admin`
- [x] Validación de payload `activo` (boolean)

### 4. Frontend - Contacto Detail
- [x] Botón toggle en cada programa
- [x] Estilo gris para programas inactivos
- [x] Badge "INACTIVO" visible
- [x] Confirmación antes de desactivar
- [x] No colapsar al hacer clic en botón

## 🧪 Pruebas Manuales

### Preparación
1. Acceder a http://localhost:8080/genesis/dashboard-v2/
2. Navegar a Contactos
3. Abrir detalle de un contacto con programas (ej: contacto_id=24)

### Caso 1: Ver programas activos
**Esperado:**
- Todos los programas deben verse normales (sin estilo gris)
- Botón "⛔ Desactivar" visible
- NO debe aparecer badge "INACTIVO"

### Caso 2: Desactivar un programa
**Pasos:**
1. Clic en "⛔ Desactivar"
2. Debe aparecer confirmación
3. Aceptar confirmación

**Esperado:**
- Programa se recarga
- Programa ahora tiene estilo gris (opacity 0.5, grayscale 70%)
- Badge "INACTIVO" en rojo aparece
- Botón cambia a "✅ Activar"

**Verificar en BD:**
```sql
SELECT id, programa_id, contacto_id, activo 
FROM programas_asignaciones 
WHERE id = [ID_ASIGNACION];
```
Debe mostrar `activo = f`

### Caso 3: Programa inactivo no aparece en otros lugares
**Verificar:**
- Tab "Diplomas" → No debe mostrar elegibles para el programa inactivo
- Tab "Por Completar" → No debe incluir estudiantes del programa inactivo
- API `/contactos/{code}/academic-history` → Programa inactivo debe estar en la lista (con campo `activo: false`)

### Caso 4: Activar un programa
**Pasos:**
1. Clic en "✅ Activar" de un programa inactivo
2. NO debe pedir confirmación (activar es seguro)
3. Programa se recarga

**Esperado:**
- Programa vuelve a estilo normal
- Badge "INACTIVO" desaparece
- Botón cambia a "⛔ Desactivar"

### Caso 5: Permisos
**Probar con usuario sin permisos:**
- Endpoint debe retornar 403 Forbidden

**Probar con Office Manager:**
- Debe poder activar/desactivar

**Probar con Super Admin:**
- Debe poder activar/desactivar

### Caso 6: Herencia a Estudiantes
**Verificar:**
1. Desactivar programa del contacto
2. Abrir detalle de un estudiante heredado
3. Tab "Programas" NO debe mostrar el programa inactivo

## 📊 Resultados

### Base de Datos
```
✅ Total asignaciones: 4
✅ Activas: 4
✅ Inactivas: 0
```

### Endpoint (pendiente de probar)
- [ ] GET /contactos/{code}/academic-history → campo `activo` presente
- [ ] PUT /programas-asignaciones/{id}/toggle → desactiva correctamente
- [ ] PUT /programas-asignaciones/{id}/toggle → activa correctamente
- [ ] PUT sin permisos → 403

### Frontend (pendiente de probar)
- [ ] Botón toggle visible
- [ ] Confirmación funciona
- [ ] Estilo gris aplicado
- [ ] Badge INACTIVO visible
- [ ] Reload después de toggle

## 🐛 Issues Encontrados
_(Ninguno hasta ahora)_

## ✅ Listo para Push
- [ ] Todas las pruebas pasaron
- [ ] No hay regresiones
- [ ] Documentación actualizada (este archivo)

