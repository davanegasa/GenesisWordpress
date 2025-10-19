# Arquitectura Multi-Base de Datos

## 📊 Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│                         WordPress (Container)                    │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐    │
│  │              Plugin: plg-genesis                        │    │
│  │                                                         │    │
│  │  ┌──────────────────────────────────────────┐         │    │
│  │  │  API Controller                          │         │    │
│  │  │  /wp-json/plg-genesis/v1/estudiantes     │         │    │
│  │  └──────────┬───────────────────────────────┘         │    │
│  │             │                                          │    │
│  │             ▼                                          │    │
│  │  ┌──────────────────────────────────────────┐         │    │
│  │  │  ConnectionProvider                      │         │    │
│  │  │  - get_connection($office_code)          │         │    │
│  │  │  - get_current_user_office()             │         │    │
│  │  └──────────┬───────────────────────────────┘         │    │
│  │             │                                          │    │
│  │             │ Resuelve según metadato                  │    │
│  │             │ office del usuario                       │    │
│  │             │                                          │    │
│  └─────────────┼──────────────────────────────────────────┘    │
│                │                                               │
│                │ Lee variables de entorno                      │
│                │ BOG_DB_*, SOL_DB_*, etc.                      │
│                │                                               │
└────────────────┼───────────────────────────────────────────────┘
                 │
                 ▼
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
┌─────────────┐         ┌─────────────┐
│  PostgreSQL │         │  PostgreSQL │
│   Database  │         │   Database  │
│             │         │             │
│    BOG      │         │    SOL      │
│ emmaus_est  │         │ sourceofli  │
│ estudiantes │         │ght_estudi   │
│             │         │ antes       │
└─────────────┘         └─────────────┘

Usuario BOG → Ve solo datos de emmaus_estudiantes
Usuario SOL → Ve solo datos de sourceoflight_estudiantes
Super Admin → Puede cambiar entre oficinas
```

## 🔄 Flujo de Resolución de Conexión

1. **Usuario hace login** → WordPress autentica
2. **Usuario accede a API** → `GET /wp-json/plg-genesis/v1/estudiantes`
3. **Controller llama a ConnectionProvider** → `get_connection()`
4. **ConnectionProvider lee metadato** → `get_user_meta($user_id, 'office', true)` → "SOL"
5. **Resuelve credenciales** → Lee `SOL_DB_HOST`, `SOL_DB_NAME`, etc.
6. **Crea conexión** → `pg_connect("host=postgres dbname=sourceoflight_estudiantes ...")`
7. **Repository ejecuta query** → `pg_query_params($conn, "SELECT ...", [])`
8. **Retorna datos** → Solo estudiantes de Source of Light

## 🎯 Aislamiento de Datos

### Seguridad por Oficina

| Usuario | Oficina | Base de Datos | Acceso |
|---------|---------|---------------|--------|
| admin@bog.com | BOG | `emmaus_estudiantes` | ✅ BOG ❌ SOL |
| staff@sol.com | SOL | `sourceoflight_estudiantes` | ❌ BOG ✅ SOL |
| superadmin@emmaus.com | * | Todas | ✅ BOG ✅ SOL |

### Metadato de Usuario

```sql
-- Tabla: wp_usermeta
+---------+----------+--------+-------+
| umeta_id| user_id  | meta_key | meta_value |
+---------+----------+--------+-------+
| 123     | 5        | office | BOG    |
| 124     | 7        | office | SOL    |
+---------+----------+--------+-------+
```

## 🔧 Variables de Entorno

```yaml
# docker-compose.yml
environment:
  # Bogotá
  BOG_DB_HOST: postgres
  BOG_DB_NAME: emmaus_estudiantes
  BOG_DB_USER: emmaus_admin
  BOG_DB_PASSWORD: emmaus1234+
  
  # Source of Light
  SOL_DB_HOST: postgres
  SOL_DB_NAME: sourceoflight_estudiantes
  SOL_DB_USER: emmaus_admin
  SOL_DB_PASSWORD: emmaus1234+
```

## 📁 Estructura de Archivos

```
migration/
├── init-postgres-multidb.sh          # Crea bases de datos
├── load-bog-data.sh                  # Carga datos BOG
├── load-sol-data.sh                  # Carga datos SOL
├── MULTIDB_SETUP.md                  # Guía completa
└── ARCHITECTURE.md                   # Este archivo

wp-content/plugins/plg-genesis/
├── backend/
│   ├── infrastructure/
│   │   └── ConnectionProvider.php    # Resuelve conexiones
│   └── repositories/
│       └── EstudiantesRepository.php  # Usa ConnectionProvider
└── migration/
    ├── init.sql                       # Esquema base
    ├── dump20250805.sql               # Datos BOG
    └── examples/
        └── fuentedeLuz.sql            # Datos SOL
```

## 🚀 Inicialización del Contenedor

Orden de ejecución en `/docker-entrypoint-initdb.d/`:

```
01-init-schema.sql              # Crea tablas en emmaus_estudiantes (default)
    ↓
02-create-databases.sh          # CREATE DATABASE sourceoflight_estudiantes
    ↓
03-load-bog-data.sh            # Carga dump20250805.sql → emmaus_estudiantes
    ↓
04-load-sol-data.sh            # Carga fuentedeLuz.sql → sourceoflight_estudiantes
```

## 🔐 Consideraciones de Seguridad

1. **Nunca hardcodear credenciales** - Usar siempre variables de entorno
2. **Validar oficina del usuario** - Verificar que tenga acceso
3. **SQL parametrizado** - Usar `pg_query_params` siempre
4. **Logging con contexto** - Registrar usuario, oficina y acción
5. **Auditoría** - Tracking de cambios por usuario/oficina

## 📈 Escalabilidad

### Agregar Nueva Oficina (Ejemplo: MED)

1. Crear dump: `migration/medellin.sql`
2. Agregar variables al `docker-compose.yml`:
   ```yaml
   MED_DB_HOST: postgres
   MED_DB_NAME: medellin_estudiantes
   MED_DB_USER: emmaus_admin
   MED_DB_PASSWORD: emmaus1234+
   ```
3. Crear script: `migration/load-med-data.sh`
4. Actualizar `init-postgres-multidb.sh`
5. Reiniciar: `docker-compose down -v && docker-compose up -d`

### Producción

En producción, cada oficina puede tener:
- Servidor PostgreSQL dedicado
- Credenciales únicas por oficina
- Backups independientes
- Replicación geográfica

```yaml
# Ejemplo producción
BOG_DB_HOST: postgres-bog.aws.region1.rds.amazonaws.com
BOG_DB_NAME: emmaus_bog_prod
BOG_DB_USER: bg_user
BOG_DB_PASSWORD: ${BOG_SECRET}  # Desde secrets manager

SOL_DB_HOST: postgres-sol.aws.region2.rds.amazonaws.com
SOL_DB_NAME: sourceoflight_prod
SOL_DB_USER: sol_user
SOL_DB_PASSWORD: ${SOL_SECRET}  # Desde secrets manager
```

## 🧪 Testing

```php
// Unit test - ConnectionProvider
public function test_resolve_connection_by_office() {
    // Arrange
    $user_id = $this->create_user_with_office('SOL');
    wp_set_current_user($user_id);
    
    // Act
    $conn = ConnectionProvider::get_connection();
    
    // Assert
    $result = pg_query($conn, "SELECT current_database()");
    $row = pg_fetch_assoc($result);
    $this->assertEquals('sourceoflight_estudiantes', $row['current_database']);
}
```

## 📚 Referencias

- [MULTIDB_SETUP.md](MULTIDB_SETUP.md) - Guía completa de configuración
- [../README.md](../README.md) - Documentación principal
- [PostgreSQL Multi-Database Best Practices](https://www.postgresql.org/docs/current/managing-databases.html)

