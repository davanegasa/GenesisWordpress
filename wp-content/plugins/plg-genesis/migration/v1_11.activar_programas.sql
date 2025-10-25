-- Migración v1.11: Campo activo en programas_asignaciones
-- Permite activar/desactivar programas asignados a contactos (borrado lógico)
-- Idempotente y compatible con PostgreSQL 9.2+

-- =============================================
-- Agregar campo activo a programas_asignaciones
-- =============================================

DO $$ 
BEGIN
    -- Verificar si la columna ya existe
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'programas_asignaciones' 
        AND column_name = 'activo'
    ) THEN
        -- Agregar columna activo
        ALTER TABLE programas_asignaciones 
        ADD COLUMN activo BOOLEAN NOT NULL DEFAULT true;
        
        RAISE NOTICE 'Columna activo agregada a programas_asignaciones';
    ELSE
        RAISE NOTICE 'Columna activo ya existe en programas_asignaciones';
    END IF;
END $$;

-- =============================================
-- Crear índice para consultas de programas activos
-- =============================================

DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_indexes 
        WHERE indexname = 'idx_programas_asignaciones_activo'
    ) THEN
        CREATE INDEX idx_programas_asignaciones_activo 
        ON programas_asignaciones(activo) 
        WHERE activo = true;
        
        RAISE NOTICE 'Índice idx_programas_asignaciones_activo creado';
    ELSE
        RAISE NOTICE 'Índice idx_programas_asignaciones_activo ya existe';
    END IF;
END $$;

-- =============================================
-- Índice compuesto para búsquedas comunes
-- =============================================

DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_indexes 
        WHERE indexname = 'idx_programas_asig_contacto_activo'
    ) THEN
        CREATE INDEX idx_programas_asig_contacto_activo 
        ON programas_asignaciones(contacto_id, activo);
        
        RAISE NOTICE 'Índice idx_programas_asig_contacto_activo creado';
    ELSE
        RAISE NOTICE 'Índice idx_programas_asig_contacto_activo ya existe';
    END IF;
END $$;

-- =============================================
-- Comentario descriptivo
-- =============================================

COMMENT ON COLUMN programas_asignaciones.activo IS 'Indica si la asignación está activa. Los programas inactivos se ocultan pero mantienen el historial de progreso.';

-- =============================================
-- Verificación
-- =============================================

-- Contar programas activos e inactivos
DO $$ 
DECLARE
    total_activos INTEGER;
    total_inactivos INTEGER;
BEGIN
    SELECT COUNT(*) INTO total_activos 
    FROM programas_asignaciones 
    WHERE activo = true;
    
    SELECT COUNT(*) INTO total_inactivos 
    FROM programas_asignaciones 
    WHERE activo = false;
    
    RAISE NOTICE 'Migración v1.11 completada. Programas activos: %, inactivos: %', total_activos, total_inactivos;
END $$;

