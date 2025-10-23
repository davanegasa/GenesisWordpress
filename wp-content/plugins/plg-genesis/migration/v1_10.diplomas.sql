-- Migración v1.10: Sistema de diplomas para programas
-- Idempotente y compatible con PostgreSQL 9.2
-- Enfoque simplificado: solo registro de diplomas emitidos/entregados

-- =============================================
-- Tabla: diplomas_entregados
-- Registro histórico de diplomas emitidos y entregados
-- Los diplomas se calculan dinámicamente según el progreso
-- =============================================
CREATE TABLE IF NOT EXISTS diplomas_entregados (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'programa_completo' o 'nivel'
    programa_id INTEGER NOT NULL REFERENCES programas(id) ON DELETE CASCADE,
    nivel_id INTEGER REFERENCES niveles_programas(id) ON DELETE SET NULL,
    version_programa INTEGER NOT NULL DEFAULT 1,
    estudiante_id INTEGER REFERENCES estudiantes(id) ON DELETE CASCADE,
    contacto_id INTEGER REFERENCES contactos(id) ON DELETE CASCADE,
    fecha_emision DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_entrega DATE,
    entregado_por INTEGER, -- ID del usuario WordPress que registró la entrega
    notas TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    -- Validaciones
    CONSTRAINT chk_tipo_diploma CHECK (tipo IN ('programa_completo', 'nivel')),
    -- Si es diploma de nivel, nivel_id debe estar presente
    CONSTRAINT chk_nivel_requerido CHECK (
        (tipo = 'nivel' AND nivel_id IS NOT NULL) OR 
        (tipo = 'programa_completo' AND nivel_id IS NULL)
    ),
    -- Debe tener estudiante_id O contacto_id, no ambos
    CONSTRAINT chk_diploma_destinatario CHECK (
        (estudiante_id IS NOT NULL AND contacto_id IS NULL) OR 
        (contacto_id IS NOT NULL AND estudiante_id IS NULL)
    )
);

-- Índices para diplomas_entregados
CREATE INDEX IF NOT EXISTS idx_diplomas_tipo ON diplomas_entregados(tipo);
CREATE INDEX IF NOT EXISTS idx_diplomas_programa ON diplomas_entregados(programa_id);
CREATE INDEX IF NOT EXISTS idx_diplomas_nivel ON diplomas_entregados(nivel_id);
CREATE INDEX IF NOT EXISTS idx_diplomas_estudiante ON diplomas_entregados(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_diplomas_contacto ON diplomas_entregados(contacto_id);
CREATE INDEX IF NOT EXISTS idx_diplomas_emision ON diplomas_entregados(fecha_emision);
CREATE INDEX IF NOT EXISTS idx_diplomas_entrega ON diplomas_entregados(fecha_entrega);
CREATE INDEX IF NOT EXISTS idx_diplomas_pendientes ON diplomas_entregados(fecha_entrega) WHERE fecha_entrega IS NULL;

-- Índice único para evitar duplicados: mismo tipo de diploma para el mismo destinatario
CREATE UNIQUE INDEX IF NOT EXISTS uq_diploma_programa_estudiante 
ON diplomas_entregados(tipo, programa_id, COALESCE(nivel_id, 0), estudiante_id) 
WHERE estudiante_id IS NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS uq_diploma_programa_contacto 
ON diplomas_entregados(tipo, programa_id, COALESCE(nivel_id, 0), contacto_id) 
WHERE contacto_id IS NOT NULL;

-- =============================================
-- Comentarios descriptivos
-- =============================================
COMMENT ON TABLE diplomas_entregados IS 'Registro histórico de diplomas emitidos y entregados a estudiantes/contactos';
COMMENT ON COLUMN diplomas_entregados.tipo IS 'Tipo de diploma: programa_completo (completó todo el programa) o nivel (completó un nivel específico)';
COMMENT ON COLUMN diplomas_entregados.programa_id IS 'Programa al que pertenece el diploma';
COMMENT ON COLUMN diplomas_entregados.nivel_id IS 'Nivel específico (solo si tipo=nivel)';
COMMENT ON COLUMN diplomas_entregados.version_programa IS 'Versión del programa bajo la cual se completó (importante para versionamiento)';
COMMENT ON COLUMN diplomas_entregados.fecha_emision IS 'Fecha en que se emitió el diploma (se registró el logro)';
COMMENT ON COLUMN diplomas_entregados.fecha_entrega IS 'Fecha en que se entregó físicamente el diploma (NULL = pendiente de entrega)';
COMMENT ON COLUMN diplomas_entregados.entregado_por IS 'ID del usuario WordPress que registró la entrega física';
COMMENT ON COLUMN diplomas_entregados.notas IS 'Observaciones sobre la entrega o el logro';

