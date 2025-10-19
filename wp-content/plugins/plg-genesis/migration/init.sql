-- Crear el usuario emmaus si no existe
DO
$do$
BEGIN
   IF NOT EXISTS (
      SELECT FROM pg_catalog.pg_roles WHERE rolname = 'emmaus'
   ) THEN
      CREATE USER emmaus WITH PASSWORD 'emmaus';
   END IF;
END
$do$;

-- Dar permisos necesarios al usuario emmaus
GRANT ALL PRIVILEGES ON DATABASE emmaus_estudiantes TO emmaus;
ALTER USER emmaus WITH SUPERUSER;

-- NOTA: Los datos se cargan mediante scripts separados:
-- - load-bog-data.sh carga dump20250805.sql en emmaus_estudiantes
-- - load-fdl-data.sh carga fuentedeLuz.sql en fuentedeluz_estudiantes 