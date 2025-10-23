-- Migración v1.10.1: Simplificar diplomas_entregados
-- Los diplomas siempre son por estudiante individual
-- No necesitamos contacto_id porque se obtiene via estudiantes.id_contacto

-- Eliminar el constraint antiguo
ALTER TABLE diplomas_entregados DROP CONSTRAINT IF EXISTS chk_diploma_destinatario;

-- Nuevo constraint: DEBE tener estudiante_id siempre
ALTER TABLE diplomas_entregados ADD CONSTRAINT chk_diploma_estudiante_required 
CHECK (estudiante_id IS NOT NULL);

-- Hacer contacto_id opcional (puede ser NULL, se obtiene por JOIN)
-- No hacemos DROP COLUMN por si hay datos legacy, pero dejamos de usarlo

