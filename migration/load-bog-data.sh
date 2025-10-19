#!/bin/bash
set -e

# Cargar datos de Bogotá en emmaus_estudiantes
echo "📥 Cargando datos de Bogotá (BOG) en emmaus_estudiantes..."

if [ -f /docker-entrypoint-initdb.d/data/dump20250805.sql ]; then
    psql -v ON_ERROR_STOP=0 --username "$POSTGRES_USER" --dbname=emmaus_estudiantes < /docker-entrypoint-initdb.d/data/dump20250805.sql
    echo "✅ Datos de BOG cargados exitosamente"
else
    echo "⚠️  Advertencia: No se encontró dump20250805.sql"
fi

