-- Migración v1.10.2: Sistema de Actas de Diplomas
-- Crea registro formal de actas que agrupan diplomas emitidos

-- Tabla de actas
CREATE TABLE IF NOT EXISTS actas_diplomas (
    id SERIAL PRIMARY KEY,
    numero_acta VARCHAR(50) UNIQUE NOT NULL, -- Ej: "2025-001", "2025-BOG-042"
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

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_actas_fecha ON actas_diplomas(fecha_acta DESC);
CREATE INDEX IF NOT EXISTS idx_actas_contacto ON actas_diplomas(contacto_id);
CREATE INDEX IF NOT EXISTS idx_actas_numero ON actas_diplomas(numero_acta);

-- Agregar columna acta_id a diplomas_entregados
ALTER TABLE diplomas_entregados 
ADD COLUMN IF NOT EXISTS acta_id INTEGER REFERENCES actas_diplomas(id) ON DELETE SET NULL;

-- Índice para joins rápidos
CREATE INDEX IF NOT EXISTS idx_diplomas_acta ON diplomas_entregados(acta_id);

-- Función para generar número de acta correlativo por año
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

-- Trigger para actualizar updated_at
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

-- Comentarios
COMMENT ON TABLE actas_diplomas IS 'Registro formal de actas que agrupan diplomas emitidos';
COMMENT ON COLUMN actas_diplomas.numero_acta IS 'Número único de acta, formato YYYY-NNN';
COMMENT ON COLUMN actas_diplomas.tipo_acta IS 'Tipo de acta: cierre (acta de cierre), graduacion, regular';
COMMENT ON COLUMN actas_diplomas.estado IS 'Estado del acta: activa o anulada';

