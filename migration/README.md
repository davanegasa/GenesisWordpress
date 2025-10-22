# Migraciones y Configuración Multi-Base de Datos

Este directorio contiene scripts de migración para MariaDB y PostgreSQL, además de la configuración completa del sistema multi-base de datos que replica el comportamiento de producción.

## 📁 Estructura de Archivos

```
migration/
├── README.md                          # Este archivo
├── MULTIDB_SETUP.md                   # 📘 Guía completa de configuración
├── ARCHITECTURE.md                    # 🏗️ Diagramas y arquitectura del sistema
│
├── emmaus_wpgenesis (2).sql           # Dump inicial de WordPress (MariaDB)
├── update_urls.sql                    # Actualización de URLs de WordPress
│
├── init-postgres-multidb.sh           # 🔧 Crea múltiples bases de datos PostgreSQL
├── load-bog-data.sh                   # 📥 Carga datos de Bogotá
├── load-sol-data.sh                   # 📥 Carga datos de Source of Light
└── verify-multidb.sh                  # ✅ Verifica la configuración
```

## 🚀 Inicio Rápido

### 1. Primera vez - Iniciar el sistema

```bash
# Levantar todos los servicios
docker-compose up -d

# Esperar a que los contenedores estén listos (30-60 segundos)
docker-compose logs -f postgres

# Verificar que todo esté correcto
./migration/verify-multidb.sh
```

### 2. Reiniciar desde cero (elimina todos los datos)

```bash
# Detener y eliminar volúmenes
docker-compose down -v

# Levantar nuevamente
docker-compose up -d

# Verificar
./migration/verify-multidb.sh
```

### 3. Ver logs de inicialización

```bash
# Ver logs de PostgreSQL
docker-compose logs postgres

# Ver logs en tiempo real
docker-compose logs -f postgres
```

## 🗄️ Bases de Datos Configuradas

| Oficina | Código | Base de Datos | Dump SQL | Variables |
|---------|--------|---------------|----------|-----------|
| Bogotá | BOG | `emmaus_estudiantes` | `dump20250805.sql` | `BOG_DB_*` |
| Source of Light | SOL | `sourceoflight_estudiantes` | `fuentedeLuz.sql` | `SOL_DB_*` |

## 📚 Documentación

### Para desarrolladores del plugin:

1. **[MULTIDB_SETUP.md](MULTIDB_SETUP.md)** - LÉELO PRIMERO
   - Configuración completa del sistema
   - Cómo usar `ConnectionProvider`
   - Ejemplos de código para API endpoints
   - Cómo agregar nuevas oficinas

2. **[ARCHITECTURE.md](ARCHITECTURE.md)** - Referencia técnica
   - Diagramas de flujo del sistema
   - Arquitectura de resolución de conexiones
   - Consideraciones de seguridad
   - Estrategias de escalabilidad

### Para usuarios:

- **[../README.md](../README.md)** - Documentación general del proyecto
- **[verify-multidb.sh](verify-multidb.sh)** - Script de verificación

## 🔧 Scripts de Utilidad

### Verificar configuración

```bash
./migration/verify-multidb.sh
```

Este script verifica:
- ✅ Contenedor PostgreSQL corriendo
- ✅ Bases de datos creadas
- ✅ Tablas disponibles
- ✅ Datos cargados

### Conectar a una base de datos específica

```bash
# Conectar a BOG (Bogotá)
docker exec -it postgres psql -U emmaus_admin -d emmaus_estudiantes

# Conectar a SOL (Source of Light)
docker exec -it postgres psql -U emmaus_admin -d sourceoflight_estudiantes

# Comandos útiles dentro de psql:
# \l              - Listar todas las bases de datos
# \dt             - Listar tablas de la base de datos actual
# \d nombre_tabla - Describir estructura de una tabla
# \q              - Salir
```

### Ver todas las bases de datos

```bash
docker exec -it postgres psql -U emmaus_admin -d postgres -c "\l"
```

## 🔄 Flujo de Inicialización

Cuando ejecutas `docker-compose up -d`, PostgreSQL ejecuta automáticamente estos scripts en orden:

```
1. 01-init-schema.sql          ← Crea esquema base en emmaus_estudiantes
2. 02-create-databases.sh      ← Crea sourceoflight_estudiantes y otras
3. 03-load-bog-data.sh         ← Carga dump20250805.sql → emmaus_estudiantes
4. 04-load-sol-data.sh         ← Carga fuentedeLuz.sql → sourceoflight_estudiantes
```

Los archivos se procesan **solo en el primer arranque**. Si los volúmenes ya existen, no se vuelven a ejecutar.

## 🐛 Resolución de Problemas

### Error: "Base de datos no existe"

```bash
# Verifica que el contenedor esté corriendo
docker ps | grep postgres

# Verifica los logs de inicialización
docker-compose logs postgres | grep -i "error"

# Solución: Recrear desde cero
docker-compose down -v
docker-compose up -d
```

### Error: "No se puede conectar a la base de datos"

```bash
# Verifica que el puerto esté expuesto
docker ps | grep postgres

# Debería mostrar: 0.0.0.0:5432->5432/tcp

# Prueba la conexión desde el host
psql -h localhost -p 5432 -U emmaus_admin -d emmaus_estudiantes
```

### Ver qué bases de datos se crearon

```bash
docker exec -it postgres psql -U emmaus_admin -d postgres -c "\l" | grep -E "emmaus|sourceoflight"
```

### Verificar datos cargados

```bash
# Contar estudiantes en BOG
docker exec -it postgres psql -U emmaus_admin -d emmaus_estudiantes -c "SELECT COUNT(*) FROM estudiantes;"

# Contar estudiantes en SOL
docker exec -it postgres psql -U emmaus_admin -d sourceoflight_estudiantes -c "SELECT COUNT(*) FROM estudiantes;"
```

## 🔐 Credenciales

### PostgreSQL (todas las bases de datos)

- **Usuario**: `emmaus_admin`
- **Contraseña**: `emmaus1234+`
- **Host**: `postgres` (dentro de Docker) / `localhost` (desde el host)
- **Puerto**: `5432`

### MariaDB (WordPress)

- **Base de datos**: `emmaus_wpgenesis`
- **Usuario**: `emmaus_wpgenesis`
- **Contraseña**: `emmaus_wpgenesis`
- **Puerto**: `3306`

⚠️ **Importante**: Estas son credenciales de desarrollo. En producción usa secretos gestionados.

## 📖 Recursos Adicionales

- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

## 💡 Consejos

1. **Desarrollo iterativo**: Los cambios en `wp-content/` se reflejan inmediatamente
2. **Backup regular**: Exporta dumps de tus bases de datos de desarrollo frecuentemente
3. **Logs son tu amigo**: Usa `docker-compose logs -f` para debugging
4. **Verifica antes de commitear**: Ejecuta `verify-multidb.sh` antes de hacer commits

---

**¿Necesitas ayuda?** Revisa la documentación completa en:
- [`MULTIDB_SETUP.md`](MULTIDB_SETUP.md) - Configuración y uso
- [`ARCHITECTURE.md`](ARCHITECTURE.md) - Arquitectura y diagramas

