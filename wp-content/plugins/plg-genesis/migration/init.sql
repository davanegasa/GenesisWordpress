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

-- Ejecutar el archivo con el esquema y datos de producción
\i /docker-entrypoint-initdb.d/migrations/datos270420251117.sql; 