#!/bin/bash
set -e

# Script de inicialización para crear múltiples bases de datos PostgreSQL
# Replica el comportamiento de producción con una base de datos por oficina

echo "🚀 Inicializando bases de datos para múltiples oficinas..."

# Base de datos principal ya se crea automáticamente por POSTGRES_DB
# emmaus_estudiantes (BOG - Bogotá)

# Crear base de datos para Fuente de Luz (FDL)
echo "📦 Creando base de datos: fuentedeluz_estudiantes"
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname="postgres" <<-EOSQL
    CREATE DATABASE fuentedeluz_estudiantes;
    GRANT ALL PRIVILEGES ON DATABASE fuentedeluz_estudiantes TO $POSTGRES_USER;
EOSQL

echo "✅ Bases de datos creadas exitosamente:"
echo "   - emmaus_estudiantes (BOG)"
echo "   - fuentedeluz_estudiantes (FDL)"
echo ""
echo "⏳ Los dumps SQL se cargarán automáticamente a continuación..."

