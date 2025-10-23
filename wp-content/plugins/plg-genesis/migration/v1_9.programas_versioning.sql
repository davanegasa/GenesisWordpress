-- Programas versioning – migración idempotente compatible con PostgreSQL 9.2

-- 1) Columnas base con comprobación

-- Agregar columnas de timestamp si no existen
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='programas' AND column_name='created_at'
    ) THEN
        ALTER TABLE programas ADD COLUMN created_at TIMESTAMP DEFAULT NOW();
        UPDATE programas SET created_at = NOW() WHERE created_at IS NULL;
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='programas' AND column_name='updated_at'
    ) THEN
        ALTER TABLE programas ADD COLUMN updated_at TIMESTAMP DEFAULT NOW();
        UPDATE programas SET updated_at = NOW() WHERE updated_at IS NULL;
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='programas' AND column_name='current_version'
    ) THEN
        ALTER TABLE programas ADD COLUMN current_version INTEGER;
        UPDATE programas SET current_version = 1 WHERE current_version IS NULL;
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='niveles_programas' AND column_name='version'
    ) THEN
        ALTER TABLE niveles_programas ADD COLUMN version INTEGER;
        UPDATE niveles_programas SET version = 1 WHERE version IS NULL;
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='programas_cursos' AND column_name='version'
    ) THEN
        ALTER TABLE programas_cursos ADD COLUMN version INTEGER;
        UPDATE programas_cursos SET version = 1 WHERE version IS NULL;
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema='public' AND table_name='programas_asignaciones' AND column_name='version'
    ) THEN
        ALTER TABLE programas_asignaciones ADD COLUMN version INTEGER;
    END IF;
END$$;

-- Backfill de asignaciones
UPDATE programas_asignaciones pa
SET version = p.current_version
FROM programas p
WHERE pa.programa_id = p.id AND pa.version IS NULL;

-- Eliminar constraint antiguo de programas_cursos si existe (sin version)
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'programas_cursos_programa_id_consecutivo_key'
    ) THEN
        ALTER TABLE programas_cursos DROP CONSTRAINT programas_cursos_programa_id_consecutivo_key;
    END IF;
END$$;

-- 2) Índices/Unicidad por versión
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE n.nspname='public' AND c.relname='programas_cursos_prog_ver_cons_uniq'
    ) THEN
        CREATE UNIQUE INDEX programas_cursos_prog_ver_cons_uniq
        ON programas_cursos (programa_id, version, consecutivo);
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE n.nspname='public' AND c.relname='niveles_programas_programa_version_idx'
    ) THEN
        CREATE INDEX niveles_programas_programa_version_idx
        ON niveles_programas (programa_id, version);
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE n.nspname='public' AND c.relname='programas_cursos_programa_version_idx'
    ) THEN
        CREATE INDEX programas_cursos_programa_version_idx
        ON programas_cursos (programa_id, version);
    END IF;
END$$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
        WHERE n.nspname='public' AND c.relname='programas_asignaciones_programa_version_idx'
    ) THEN
        CREATE INDEX programas_asignaciones_programa_version_idx
        ON programas_asignaciones (programa_id, version);
    END IF;
END$$;


