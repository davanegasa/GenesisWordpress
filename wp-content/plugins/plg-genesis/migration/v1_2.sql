-- Crear tabla de niveles de programas
CREATE TABLE IF NOT EXISTS niveles_programas (
    id SERIAL PRIMARY KEY,
    programa_id INTEGER NOT NULL REFERENCES programas(id) ON DELETE CASCADE,
    nombre VARCHAR(255) NOT NULL
);

-- Actualizar tabla boletas_congresos con campos faltantes
ALTER TABLE boletas_congresos 
ADD COLUMN IF NOT EXISTS datos_registro JSON DEFAULT '{}'::json,
ADD COLUMN IF NOT EXISTS fecha_registro TIMESTAMP DEFAULT now();

-- Crear índices faltantes
CREATE INDEX IF NOT EXISTS idx_contacto_id ON programas_asignaciones(contacto_id);
CREATE INDEX IF NOT EXISTS idx_estudiante_id ON programas_asignaciones(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_programa_id ON programas_asignaciones(programa_id);

-- Crear índices únicos
CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_contacto_programa 
ON programas_asignaciones(programa_id, contacto_id) 
WHERE contacto_id IS NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_estudiante_programa 
ON programas_asignaciones(programa_id, estudiante_id) 
WHERE estudiante_id IS NOT NULL;

-- Actualizar permisos
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM PUBLIC;
REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM PUBLIC;

-- Otorgar permisos a emmaus_admin
GRANT ALL ON ALL TABLES IN SCHEMA public TO emmaus_admin;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO emmaus_admin;

-- Aplicar permisos automáticamente a futuras tablas y secuencias
ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT ALL ON TABLES TO emmaus_admin;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
GRANT ALL ON SEQUENCES TO emmaus_admin; 