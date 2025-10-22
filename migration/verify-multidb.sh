#!/bin/bash

# Script de verificación para el sistema multi-base de datos
# Verifica que todas las bases de datos estén creadas y accesibles

set -e

echo "🔍 Verificando configuración multi-base de datos..."
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para verificar una base de datos
check_database() {
    local db_name=$1
    local office_code=$2
    
    echo -n "  Verificando base de datos '$db_name' ($office_code)... "
    
    if docker exec -it postgres psql -U emmaus_admin -d "$db_name" -c "SELECT 1;" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC}"
        
        # Contar tablas
        table_count=$(docker exec -it postgres psql -U emmaus_admin -d "$db_name" -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" | tr -d '[:space:]')
        echo "    → Tablas encontradas: $table_count"
        
        # Contar estudiantes
        if docker exec -it postgres psql -U emmaus_admin -d "$db_name" -t -c "SELECT COUNT(*) FROM estudiantes;" > /dev/null 2>&1; then
            estudiantes_count=$(docker exec -it postgres psql -U emmaus_admin -d "$db_name" -t -c "SELECT COUNT(*) FROM estudiantes;" | tr -d '[:space:]')
            echo "    → Estudiantes registrados: $estudiantes_count"
        fi
        
        return 0
    else
        echo -e "${RED}✗${NC}"
        echo "    → Error: No se pudo conectar a la base de datos"
        return 1
    fi
}

# Función para verificar variables de entorno
check_env_vars() {
    local prefix=$1
    local office_name=$2
    
    echo -n "  Verificando variables de entorno ${prefix}_DB_*... "
    
    if docker exec -it postgres env | grep -q "${prefix}_DB_NAME"; then
        echo -e "${GREEN}✓${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠${NC}"
        echo "    → Advertencia: Variables no encontradas en el contenedor"
        echo "    → (Esto es normal, las variables están en el contenedor de WordPress)"
        return 0
    fi
}

# Verificar que el contenedor esté corriendo
echo "📦 Verificando contenedores Docker..."
if ! docker ps | grep -q postgres; then
    echo -e "${RED}✗${NC} El contenedor 'postgres' no está corriendo"
    echo "  Ejecuta: docker-compose up -d"
    exit 1
fi
echo -e "  ${GREEN}✓${NC} Contenedor PostgreSQL corriendo"
echo ""

# Listar todas las bases de datos
echo "📋 Bases de datos disponibles:"
docker exec -it postgres psql -U emmaus_admin -d postgres -c "\l" | grep -E "emmaus_estudiantes|sourceoflight"
echo ""

# Verificar cada base de datos
echo "🔎 Verificando bases de datos individuales:"
echo ""

errors=0

echo "1️⃣  BOG (Bogotá)"
if ! check_database "emmaus_estudiantes" "BOG"; then
    ((errors++))
fi
echo ""

echo "2️⃣  SOL (Source of Light)"
if ! check_database "sourceoflight_estudiantes" "SOL"; then
    ((errors++))
fi
echo ""

# Resumen final
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ $errors -eq 0 ]; then
    echo -e "${GREEN}✅ Todas las verificaciones pasaron exitosamente${NC}"
    echo ""
    echo "📚 Próximos pasos:"
    echo "  1. Accede a WordPress: http://localhost:8080"
    echo "  2. Activa el plugin 'plg-genesis'"
    echo "  3. Configura el metadato 'office' para los usuarios:"
    echo "     - 'BOG' para usuarios de Bogotá"
    echo "     - 'SOL' para usuarios de Source of Light"
    echo ""
    echo "📖 Documentación:"
    echo "  - migration/MULTIDB_SETUP.md"
    echo "  - migration/ARCHITECTURE.md"
else
    echo -e "${RED}❌ Se encontraron $errors error(es)${NC}"
    echo ""
    echo "💡 Solución:"
    echo "  Recrea los contenedores con:"
    echo "  docker-compose down -v"
    echo "  docker-compose up -d"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

