# Configuración Multi-Base de Datos PostgreSQL

## 📋 Resumen

Este entorno de desarrollo replica el comportamiento de producción con **múltiples bases de datos PostgreSQL**, una por oficina. El selector de base de datos se basa en el metadato de oficina del usuario.

## 🗄️ Bases de Datos Disponibles

| Oficina | Código | Base de Datos | Variables de Entorno |
|---------|--------|--------------|---------------------|
| Bogotá | BOG | `emmaus_estudiantes` | `BOG_DB_*` |
| Source of Light | SOL | `sourceoflight_estudiantes` | `SOL_DB_*` |

## 🔧 Variables de Entorno

Las credenciales están configuradas en `docker-compose.yml`:

```yaml
# Bogotá (BOG)
BOG_DB_HOST: postgres
BOG_DB_NAME: emmaus_estudiantes
BOG_DB_USER: emmaus_admin
BOG_DB_PASSWORD: emmaus1234+

# Source of Light (SOL)
SOL_DB_HOST: postgres
SOL_DB_NAME: sourceoflight_estudiantes
SOL_DB_USER: emmaus_admin
SOL_DB_PASSWORD: emmaus1234+
```

## 📦 Estructura de Inicialización

El contenedor PostgreSQL ejecuta scripts en orden alfabético desde `/docker-entrypoint-initdb.d/`:

1. **01-init-schema.sql** - Crea esquema base (tablas, índices, etc.)
2. **02-create-databases.sh** - Crea las bases de datos adicionales
3. **03-load-bog-data.sh** - Carga datos de Bogotá (`dump20250805.sql`)
4. **04-load-sol-data.sh** - Carga datos de Source of Light (`fuentedeLuz.sql`)

## 🚀 Uso desde el Plugin

### ConnectionProvider - Resolver Base de Datos por Oficina

El `ConnectionProvider` debe usar el metadato de oficina del usuario para seleccionar las credenciales correctas:

```php
<?php
// wp-content/plugins/plg-genesis/backend/infrastructure/ConnectionProvider.php

class ConnectionProvider {
    
    /**
     * Obtiene conexión PostgreSQL según la oficina del usuario
     * 
     * @param string $office_code Código de oficina (BOG, SOL, etc.)
     * @return resource|false Conexión PostgreSQL
     */
    public static function get_connection($office_code = null) {
        // Si no se pasa oficina, obtenerla del usuario actual
        if ($office_code === null) {
            $office_code = self::get_current_user_office();
        }
        
        // Resolver credenciales desde variables de entorno
        $prefix = strtoupper($office_code);
        $host = getenv("{$prefix}_DB_HOST") ?: 'postgres';
        $dbname = getenv("{$prefix}_DB_NAME");
        $user = getenv("{$prefix}_DB_USER");
        $password = getenv("{$prefix}_DB_PASSWORD");
        
        // Validar que existan las credenciales
        if (empty($dbname) || empty($user)) {
            error_log("ConnectionProvider: No hay credenciales para oficina {$office_code}");
            return false;
        }
        
        // Crear conexión
        $conn_string = sprintf(
            "host=%s dbname=%s user=%s password=%s",
            $host,
            $dbname,
            $user,
            $password
        );
        
        $conn = pg_connect($conn_string);
        
        if (!$conn) {
            error_log("ConnectionProvider: Error conectando a DB de oficina {$office_code}");
            return false;
        }
        
        return $conn;
    }
    
    /**
     * Obtiene el código de oficina del usuario actual desde meta
     * 
     * @return string Código de oficina (BOG por defecto)
     */
    private static function get_current_user_office() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return 'BOG'; // Default
        }
        
        $office = get_user_meta($user_id, 'office', true);
        return !empty($office) ? strtoupper($office) : 'BOG';
    }
}
```

### OfficeResolver - Ejemplo de uso en API

```php
<?php
// Uso en un endpoint REST API

add_action('rest_api_init', function() {
    register_rest_route('plg-genesis/v1', '/estudiantes', [
        'methods' => 'GET',
        'callback' => function($request) {
            // La oficina se resuelve automáticamente del metadato del usuario
            $conn = ConnectionProvider::get_connection();
            
            if (!$conn) {
                return new WP_Error('db_error', 'Error de conexión', ['status' => 500]);
            }
            
            // Ejecutar consulta con pg_query_params (seguro)
            $result = pg_query_params($conn, 
                'SELECT * FROM estudiantes WHERE deleted_at IS NULL LIMIT $1', 
                [50]
            );
            
            // ... procesar resultados
            
            pg_close($conn);
            
            return rest_ensure_response([
                'success' => true,
                'data' => $estudiantes
            ]);
        },
        'permission_callback' => function() {
            return current_user_can('read');
        }
    ]);
});
```

## 🔄 Agregar Nueva Oficina

Para agregar una nueva oficina (ejemplo: MED - Medellín):

1. **Crear dump SQL**: `migration/medellin.sql`

2. **Actualizar docker-compose.yml**:
```yaml
# Agregar variables de entorno
MED_DB_HOST: postgres
MED_DB_NAME: medellin_estudiantes
MED_DB_USER: emmaus_admin
MED_DB_PASSWORD: emmaus1234+

# Agregar volúmenes
- ./migration/medellin.sql:/docker-entrypoint-initdb.d/data/medellin.sql
- ./migration/load-med-data.sh:/docker-entrypoint-initdb.d/05-load-med-data.sh
```

3. **Crear script de carga**: `migration/load-med-data.sh`
```bash
#!/bin/bash
set -e
echo "📥 Cargando datos de Medellín (MED)..."
psql -v ON_ERROR_STOP=0 --username "$POSTGRES_USER" --dbname=medellin_estudiantes < /docker-entrypoint-initdb.d/data/medellin.sql
echo "✅ Datos de MED cargados"
```

4. **Actualizar init-postgres-multidb.sh**:
```bash
echo "📦 Creando base de datos: medellin_estudiantes"
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
    CREATE DATABASE medellin_estudiantes;
    GRANT ALL PRIVILEGES ON DATABASE medellin_estudiantes TO $POSTGRES_USER;
EOSQL
```

5. **Reiniciar contenedor**:
```bash
docker-compose down -v
docker-compose up -d
```

## 🧪 Verificar Configuración

```bash
# Ver bases de datos creadas
docker exec -it postgres psql -U emmaus_admin -d emmaus_estudiantes -c "\l"

# Conectar a base de datos específica
docker exec -it postgres psql -U emmaus_admin -d sourceoflight_estudiantes

# Ver tablas
\dt

# Verificar datos
SELECT COUNT(*) FROM estudiantes;
```

## 🔐 Seguridad

- ⚠️ **NUNCA** hardcodear credenciales en el código
- ✅ Usar siempre variables de entorno
- ✅ En producción: usar secretos gestionados (AWS Secrets, Vault, etc.)
- ✅ Usar siempre `pg_query_params` para prevenir SQL injection

## 📝 Metadato de Usuario

El metadato `office` debe almacenarse en la tabla `wp_usermeta`:

```sql
-- Ver oficina de un usuario
SELECT meta_value FROM wp_usermeta 
WHERE user_id = 1 AND meta_key = 'office';

-- Actualizar oficina de un usuario
UPDATE wp_usermeta 
SET meta_value = 'SOL' 
WHERE user_id = 1 AND meta_key = 'office';
```

O desde PHP:
```php
// Obtener
$office = get_user_meta($user_id, 'office', true);

// Actualizar
update_user_meta($user_id, 'office', 'SOL');
```

