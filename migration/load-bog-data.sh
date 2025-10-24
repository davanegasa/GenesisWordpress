#!/bin/bash
set -e

# Cargar datos de Bogotá en emmaus_estudiantes
echo "📥 Cargando datos de Bogotá (BOG) en emmaus_estudiantes..."

if [ -f /docker-entrypoint-initdb.d/data/emmausBog.sql ]; then
    psql -v ON_ERROR_STOP=0 --username "$POSTGRES_USER" --dbname=emmaus_estudiantes < /docker-entrypoint-initdb.d/data/emmausBog.sql
    echo "✅ Datos de BOG cargados exitosamente"
else
    echo "⚠️  Advertencia: No se encontró emmausBog.sql"
fi

