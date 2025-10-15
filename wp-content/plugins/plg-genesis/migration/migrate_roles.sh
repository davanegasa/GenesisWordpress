#!/bin/bash
# =====================================================
# Script de migración rápida de roles
# =====================================================

set -e

echo "🔄 Migración de Roles y Permisos - Genesis Plugin"
echo "=================================================="
echo ""

# Detectar si estamos en Docker o local
if docker ps | grep -q "mariadb"; then
    echo "✓ Docker detectado"
    DOCKER_CMD="docker exec -i genesiswordpress-mariadb-1 mysql -u emmaus_wpgenesis -pemmaus_wpgenesis emmaus_wpgenesis"
else
    echo "⚠️  Docker no detectado. Usando MySQL local..."
    read -p "Usuario MySQL: " MYSQL_USER
    read -sp "Password MySQL: " MYSQL_PASS
    echo ""
    read -p "Base de datos: " MYSQL_DB
    DOCKER_CMD="mysql -u $MYSQL_USER -p$MYSQL_PASS $MYSQL_DB"
fi

echo ""
read -p "Nombre de usuario de WordPress a migrar: " WP_USER
echo ""
read -p "Oficina (BOG/MED/CAL): " OFFICE
echo ""

echo "📝 Configuración:"
echo "   Usuario: $WP_USER"
echo "   Oficina: $OFFICE"
echo "   Rol: Super Admin"
echo ""
read -p "¿Continuar? (s/n): " CONFIRM

if [ "$CONFIRM" != "s" ]; then
    echo "❌ Cancelado"
    exit 1
fi

echo ""
echo "🚀 Ejecutando migración..."

# Crear script SQL temporal
SQL=$(cat <<EOF
-- Actualizar usuario a Super Admin
UPDATE edgen_usermeta 
SET meta_value = 'a:1:{s:16:"plg_super_admin";b:1;}'
WHERE user_id = (SELECT ID FROM edgen_users WHERE user_login = '$WP_USER')
  AND meta_key = 'edgen_capabilities';

-- Asignar oficina
DELETE FROM edgen_usermeta 
WHERE user_id = (SELECT ID FROM edgen_users WHERE user_login = '$WP_USER')
  AND meta_key = 'oficina';

INSERT INTO edgen_usermeta (user_id, meta_key, meta_value)
SELECT ID, 'oficina', '$OFFICE'
FROM edgen_users
WHERE user_login = '$WP_USER';

-- Verificar resultado
SELECT 
  u.user_login as 'Usuario',
  (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'edgen_capabilities') as 'Rol',
  (SELECT meta_value FROM edgen_usermeta WHERE user_id = u.ID AND meta_key = 'oficina') as 'Oficina'
FROM edgen_users u
WHERE u.user_login = '$WP_USER';
EOF
)

# Ejecutar SQL
echo "$SQL" | eval $DOCKER_CMD

echo ""
echo "✅ Migración completada!"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Recarga el dashboard: http://localhost:8080/dashboard-v2/"
echo "   2. Cierra sesión y vuelve a entrar si es necesario"
echo "   3. Verifica que veas el selector de oficina"
echo ""

