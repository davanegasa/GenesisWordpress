# 🚀 Inicio Rápido - Sistema Multi-Base de Datos

## ✅ ¿Qué se ha configurado?

Tu entorno de desarrollo ahora replica el comportamiento de **producción con múltiples bases de datos PostgreSQL**, una por oficina:

| Oficina | Código | Base de Datos | Archivo de Datos |
|---------|--------|---------------|------------------|
| **Bogotá** | `BOG` | `emmaus_estudiantes` | `dump20250805.sql` |
| **Source of Light** | `SOL` | `sourceoflight_estudiantes` | `fuentedeLuz.sql` |

## 📋 Archivos Creados/Modificados

### Nuevos Archivos

```
✅ migration/init-postgres-multidb.sh          # Crea las bases de datos adicionales
✅ migration/load-bog-data.sh                  # Carga datos de Bogotá
✅ migration/load-sol-data.sh                  # Carga datos de Source of Light
✅ migration/verify-multidb.sh                 # Verifica la configuración
✅ migration/MULTIDB_SETUP.md                  # Guía completa (¡LÉELA!)
✅ migration/ARCHITECTURE.md                   # Diagramas y arquitectura
✅ migration/README.md                         # Índice de documentación
✅ QUICKSTART_MULTIDB.md                       # Este archivo
```

### Archivos Modificados

```
📝 docker-compose.yml                          # Variables BOG_DB_* y SOL_DB_* agregadas
📝 README.md                                   # Documentación actualizada
📝 wp-content/plugins/plg-genesis/CHANGELOG.md # Cambio documentado
```

## 🏃 Cómo Iniciar

### 1️⃣ Reiniciar contenedores (elimina datos existentes)

```bash
cd /Users/daniel/Downloads/GenesisWordpress

# Detener y eliminar volúmenes
docker-compose down -v

# Levantar nuevamente (tardará 30-60 segundos en inicializar)
docker-compose up -d
```

### 2️⃣ Verificar que todo funcione

```bash
# Ejecutar script de verificación
./migration/verify-multidb.sh
```

Deberías ver algo como:

```
✅ Todas las verificaciones pasaron exitosamente

1️⃣  BOG (Bogotá)
  Verificando base de datos 'emmaus_estudiantes' (BOG)... ✓
    → Tablas encontradas: 15
    → Estudiantes registrados: 150

2️⃣  SOL (Source of Light)
  Verificando base de datos 'sourceoflight_estudiantes' (SOL)... ✓
    → Tablas encontradas: 15
    → Estudiantes registrados: 89
```

### 3️⃣ Acceder a las bases de datos

```bash
# Ver todas las bases de datos
docker exec -it postgres psql -U emmaus_admin -d postgres -c "\l"

# Conectar a BOG (Bogotá)
docker exec -it postgres psql -U emmaus_admin -d emmaus_estudiantes

# Conectar a SOL (Source of Light)
docker exec -it postgres psql -U emmaus_admin -d sourceoflight_estudiantes
```

Dentro de `psql`, prueba:
```sql
-- Ver tablas
\dt

-- Contar estudiantes
SELECT COUNT(*) FROM estudiantes;

-- Ver algunos estudiantes
SELECT id, nombre, apellido FROM estudiantes LIMIT 5;

-- Salir
\q
```

## 🔧 Configurar el Plugin

El plugin debe usar el `ConnectionProvider` para resolver automáticamente la base de datos según la oficina del usuario.

### Ejemplo de Implementación

```php
<?php
// wp-content/plugins/plg-genesis/backend/infrastructure/ConnectionProvider.php

class ConnectionProvider {
    
    public static function get_connection($office_code = null) {
        // Obtener oficina del usuario actual si no se especifica
        if ($office_code === null) {
            $user_id = get_current_user_id();
            $office_code = get_user_meta($user_id, 'office', true) ?: 'BOG';
        }
        
        // Resolver credenciales desde variables de entorno
        $prefix = strtoupper($office_code);
        $host = getenv("{$prefix}_DB_HOST");
        $dbname = getenv("{$prefix}_DB_NAME");
        $user = getenv("{$prefix}_DB_USER");
        $password = getenv("{$prefix}_DB_PASSWORD");
        
        // Validar
        if (empty($dbname)) {
            error_log("ConnectionProvider: No hay credenciales para {$office_code}");
            return false;
        }
        
        // Conectar
        $conn_string = "host={$host} dbname={$dbname} user={$user} password={$password}";
        return pg_connect($conn_string);
    }
}
```

### Configurar Metadato de Usuario

Los usuarios deben tener el metadato `office` configurado:

```php
// Asignar oficina a un usuario
update_user_meta($user_id, 'office', 'SOL');

// Leer oficina de un usuario
$office = get_user_meta($user_id, 'office', true);
```

O directamente en la base de datos:

```sql
-- Conectar a MariaDB (WordPress)
docker exec -it <mariadb_container> mysql -u emmaus_wpgenesis -p emmaus_wpgenesis

-- Ver oficinas de usuarios
SELECT u.ID, u.user_login, m.meta_value as office
FROM edgen_users u
LEFT JOIN edgen_usermeta m ON u.ID = m.user_id AND m.meta_key = 'office';

-- Asignar oficina a un usuario
INSERT INTO edgen_usermeta (user_id, meta_key, meta_value) 
VALUES (1, 'office', 'SOL')
ON DUPLICATE KEY UPDATE meta_value = 'SOL';
```

## 🧪 Probar el Sistema

### Test 1: Conexión Manual

```bash
# Test BOG
docker exec -it postgres psql -U emmaus_admin -d emmaus_estudiantes -c "SELECT COUNT(*) as estudiantes_bog FROM estudiantes;"

# Test SOL
docker exec -it postgres psql -U emmaus_admin -d sourceoflight_estudiantes -c "SELECT COUNT(*) as estudiantes_sol FROM estudiantes;"
```

### Test 2: Variables de Entorno

```bash
# Ver variables en el contenedor de WordPress
docker exec -it <wordpress_container_id> env | grep DB_

# Deberías ver:
# BOG_DB_HOST=postgres
# BOG_DB_NAME=emmaus_estudiantes
# BOG_DB_USER=emmaus_admin
# BOG_DB_PASSWORD=emmaus1234+
# SOL_DB_HOST=postgres
# SOL_DB_NAME=sourceoflight_estudiantes
# SOL_DB_USER=emmaus_admin
# SOL_DB_PASSWORD=emmaus1234+
```

## 🆘 Resolución de Problemas

### Problema: "Base de datos no existe"

**Solución**: Recrear desde cero

```bash
docker-compose down -v
docker-compose up -d
./migration/verify-multidb.sh
```

### Problema: "Error al cargar datos"

**Solución**: Verificar logs

```bash
docker-compose logs postgres | grep -i error
docker-compose logs postgres | grep -i "load"
```

### Problema: Scripts no se ejecutan

**Causa**: Los volúmenes ya existen. PostgreSQL solo ejecuta scripts en el **primer arranque**.

**Solución**: Eliminar volúmenes y recrear

```bash
docker-compose down -v
docker-compose up -d
```

## 📚 Documentación Completa

| Documento | Descripción |
|-----------|-------------|
| **[migration/MULTIDB_SETUP.md](migration/MULTIDB_SETUP.md)** | 📘 Guía completa de configuración y uso |
| **[migration/ARCHITECTURE.md](migration/ARCHITECTURE.md)** | 🏗️ Diagramas y arquitectura del sistema |
| **[migration/README.md](migration/README.md)** | 📑 Índice de archivos y comandos útiles |
| **[README.md](README.md)** | 📖 Documentación general del proyecto |

## ✨ Próximos Pasos

1. ✅ **Verificar la configuración**: `./migration/verify-multidb.sh`
2. 📖 **Leer la guía completa**: `migration/MULTIDB_SETUP.md`
3. 🔧 **Implementar ConnectionProvider** en el plugin
4. 👤 **Configurar metadatos de oficina** para usuarios de prueba
5. 🧪 **Probar con usuarios de diferentes oficinas**

## 📊 Resumen Visual

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress + Plugin                    │
│                                                          │
│  Usuario BOG → ConnectionProvider → BOG_DB_*            │
│                         ↓                                │
│                   pg_connect()                           │
│                         ↓                                │
├─────────────────────────┼────────────────────────────────┤
│                         │                                │
│         ┌───────────────┴───────────────┐                │
│         ▼                               ▼                │
│  ┌─────────────┐                 ┌─────────────┐        │
│  │   BOG DB    │                 │   SOL DB    │        │
│  │             │                 │             │        │
│  │ emmaus_     │                 │ sourceofli  │        │
│  │ estudiantes │                 │ ght_estudi  │        │
│  │             │                 │ antes       │        │
│  │ 150 records │                 │ 89 records  │        │
│  └─────────────┘                 └─────────────┘        │
│                                                          │
│  Aislamiento total: Cada oficina ve solo sus datos      │
└─────────────────────────────────────────────────────────┘
```

## 🎉 ¡Listo!

Tu entorno ahora está configurado con múltiples bases de datos PostgreSQL. Cada oficina tendrá su propia base de datos aislada, replicando exactamente el comportamiento de producción.

**¿Dudas?** Consulta [`migration/MULTIDB_SETUP.md`](migration/MULTIDB_SETUP.md) para detalles completos.

