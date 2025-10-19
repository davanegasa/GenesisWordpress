#!/bin/bash
set -e

# Cargar datos de Fuente de Luz en fuentedeluz_estudiantes
echo "📥 Cargando datos de Fuente de Luz (FDL) en fuentedeluz_estudiantes..."

if [ -f /docker-entrypoint-initdb.d/data/fuentedeLuz.sql ]; then
    psql -v ON_ERROR_STOP=0 --username "$POSTGRES_USER" --dbname=fuentedeluz_estudiantes < /docker-entrypoint-initdb.d/data/fuentedeLuz.sql
    echo "✅ Datos de FDL cargados exitosamente"
else
    echo "⚠️  Advertencia: No se encontró fuentedeLuz.sql"
fi

