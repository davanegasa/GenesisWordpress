-- =============================================
-- Migración v1.10: Sistema completo de diplomas y actas
-- Idempotente y compatible con PostgreSQL 9.5+
-- =============================================
-- 
-- Este sistema permite:
-- - Emitir diplomas por completar niveles o programas completos
-- - Agrupar diplomas en actas formales con numeración automática
-- - Registrar entrega física de diplomas
-- - Consultar historial completo por estudiante/contacto
--
-- =============================================

-- =============================================
-- Tabla: diplomas_entregados
-- Registro histórico de diplomas emitidos y entregados
-- =============================================
CREATE TABLE IF NOT EXISTS diplomas_entregados (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- 'programa_completo' o 'nivel'
    programa_id INTEGER NOT NULL REFERENCES programas(id) ON DELETE CASCADE,
    nivel_id INTEGER REFERENCES niveles_programas(id) ON DELETE SET NULL,
    version_programa INTEGER NOT NULL DEFAULT 1,
    estudiante_id INTEGER REFERENCES estudiantes(id) ON DELETE CASCADE,
    contacto_id INTEGER REFERENCES contactos(id) ON DELETE CASCADE, -- Redundante pero útil para consultas
    acta_id INTEGER, -- FK a actas_diplomas (se crea después)
    fecha_emision DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_entrega DATE,
    entregado_por INTEGER, -- ID del usuario WordPress que registró la entrega
    notas TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Validaciones
    CONSTRAINT chk_tipo_diploma CHECK (tipo IN ('programa_completo', 'nivel')),
    CONSTRAINT chk_nivel_requerido CHECK (
        (tipo = 'nivel' AND nivel_id IS NOT NULL) OR 
        (tipo = 'programa_completo' AND nivel_id IS NULL)
    ),
    CONSTRAINT chk_diploma_estudiante_required CHECK (estudiante_id IS NOT NULL)
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

-- Índices únicos para evitar duplicados
CREATE UNIQUE INDEX IF NOT EXISTS uq_diploma_programa_estudiante 
ON diplomas_entregados(tipo, programa_id, COALESCE(nivel_id, 0), estudiante_id) 
WHERE estudiante_id IS NOT NULL;

-- Comentarios descriptivos para diplomas_entregados
COMMENT ON TABLE diplomas_entregados IS 'Registro histórico de diplomas emitidos y entregados a estudiantes';
COMMENT ON COLUMN diplomas_entregados.tipo IS 'Tipo de diploma: programa_completo o nivel';
COMMENT ON COLUMN diplomas_entregados.programa_id IS 'Programa al que pertenece el diploma';
COMMENT ON COLUMN diplomas_entregados.nivel_id IS 'Nivel específico (solo si tipo=nivel)';
COMMENT ON COLUMN diplomas_entregados.version_programa IS 'Versión del programa bajo la cual se completó';
COMMENT ON COLUMN diplomas_entregados.fecha_emision IS 'Fecha en que se emitió el diploma';
COMMENT ON COLUMN diplomas_entregados.fecha_entrega IS 'Fecha de entrega física (NULL = pendiente)';
COMMENT ON COLUMN diplomas_entregados.entregado_por IS 'ID del usuario WordPress que registró la entrega';

-- =============================================
-- Tabla: actas_diplomas
-- Registro formal de actas que agrupan diplomas
-- =============================================
CREATE TABLE IF NOT EXISTS actas_diplomas (
    id SERIAL PRIMARY KEY,
    numero_acta VARCHAR(50) UNIQUE NOT NULL, -- Formato: YYYY-NNN (ej: 2025-001)
    fecha_acta DATE NOT NULL DEFAULT CURRENT_DATE,
    contacto_id INTEGER REFERENCES contactos(id) ON DELETE SET NULL,
    tipo_acta VARCHAR(50) NOT NULL DEFAULT 'cierre', -- 'cierre', 'graduacion', 'regular'
    total_diplomas INTEGER NOT NULL DEFAULT 0,
    observaciones TEXT,
    estado VARCHAR(20) NOT NULL DEFAULT 'activa', -- 'activa', 'anulada'
    created_by INTEGER, -- ID del usuario WordPress que creó el acta
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    CONSTRAINT chk_tipo_acta CHECK (tipo_acta IN ('cierre', 'graduacion', 'regular')),
    CONSTRAINT chk_estado_acta CHECK (estado IN ('activa', 'anulada'))
);

-- Índices para actas_diplomas
CREATE INDEX IF NOT EXISTS idx_actas_fecha ON actas_diplomas(fecha_acta DESC);
CREATE INDEX IF NOT EXISTS idx_actas_contacto ON actas_diplomas(contacto_id);
CREATE INDEX IF NOT EXISTS idx_actas_numero ON actas_diplomas(numero_acta);

-- Comentarios descriptivos para actas_diplomas
COMMENT ON TABLE actas_diplomas IS 'Registro formal de actas que agrupan diplomas emitidos';
COMMENT ON COLUMN actas_diplomas.numero_acta IS 'Número único de acta, formato YYYY-NNN';
COMMENT ON COLUMN actas_diplomas.tipo_acta IS 'Tipo de acta: cierre, graduacion, regular';
COMMENT ON COLUMN actas_diplomas.estado IS 'Estado del acta: activa o anulada';

-- =============================================
-- Agregar FK de diplomas_entregados a actas_diplomas
-- =============================================
ALTER TABLE diplomas_entregados 
ADD COLUMN IF NOT EXISTS acta_id INTEGER;

-- Agregar constraint solo si no existe
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint 
        WHERE conname = 'fk_diplomas_acta'
    ) THEN
        ALTER TABLE diplomas_entregados 
        ADD CONSTRAINT fk_diplomas_acta 
        FOREIGN KEY (acta_id) REFERENCES actas_diplomas(id) ON DELETE SET NULL;
    END IF;
END $$;

-- Índice para joins rápidos
CREATE INDEX IF NOT EXISTS idx_diplomas_acta ON diplomas_entregados(acta_id);

-- =============================================
-- Función: generar_numero_acta
-- Genera número correlativo de acta por año
-- =============================================
CREATE OR REPLACE FUNCTION generar_numero_acta() 
RETURNS TEXT AS $$
DECLARE
    anio TEXT;
    consecutivo INTEGER;
    nuevo_numero TEXT;
BEGIN
    anio := EXTRACT(YEAR FROM CURRENT_DATE)::TEXT;
    
    -- Obtener el último consecutivo del año actual
    SELECT COALESCE(MAX(
        CAST(SUBSTRING(numero_acta FROM '\d+$') AS INTEGER)
    ), 0) INTO consecutivo
    FROM actas_diplomas
    WHERE numero_acta LIKE anio || '-%';
    
    -- Incrementar
    consecutivo := consecutivo + 1;
    
    -- Formato: YYYY-NNN (ej: 2025-001)
    nuevo_numero := anio || '-' || LPAD(consecutivo::TEXT, 3, '0');
    
    RETURN nuevo_numero;
END;
$$ LANGUAGE plpgsql;

-- =============================================
-- Trigger: actualizar updated_at en actas
-- =============================================
CREATE OR REPLACE FUNCTION update_actas_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_actas_updated_at ON actas_diplomas;
CREATE TRIGGER trg_actas_updated_at
    BEFORE UPDATE ON actas_diplomas
    FOR EACH ROW
    EXECUTE FUNCTION update_actas_updated_at();
