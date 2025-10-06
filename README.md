# Sistema de Gestión de Estudiantes con WordPress, MariaDB y PostgreSQL

Este proyecto implementa un sistema de gestión de estudiantes utilizando WordPress como CMS, MariaDB para la base de datos de WordPress y PostgreSQL para el plugin de gestión (Genesis).

## Características

- Gestión de estudiantes
- Sistema de congresos con estados (PLANEACION, REGISTRO, EN_CURSO, FINALIZADO, CANCELADO)
- Registro de asistencias
- Generación de boletas
- Exportación de datos a Excel

## Requisitos

- Docker
- Docker Compose

## Instalación rápida (Docker Compose)

1. Clonar el repositorio:

```bash
git clone <URL_DE_TU_REPO>
cd GenesisWordpress
```

2. Servicios y credenciales por defecto (ver `docker-compose.yml`):

   - WordPress (servicio `wordpress`)
     - Puerto host: 8080 → contenedor 80
     - URL: http://localhost:8080

   - MariaDB (servicio `mariadb`)
     - Base de datos: `emmaus_wpgenesis`
     - Usuario: `emmaus_wpgenesis`
     - Contraseña: `emmaus_wpgenesis`
     - Puerto: 3306

   - PostgreSQL (servicio `postgres`)
     - Base de datos: `emmaus_estudiantes`
     - Usuario: `emmaus_admin`
     - Contraseña: `emmaus1234+`
     - Puerto: 5432

3. Iniciar los contenedores:

```bash
docker-compose up -d
```

4. Acceder a WordPress:

- URL: http://localhost:8080
- Usuario administrador de WordPress: se configura durante la instalación inicial

5. Instalar/activar el plugin Genesis:

- El código del plugin vive en `wp-content/plugins/plg-genesis`
- Actívalo desde el panel de administración de WordPress

## Estructura del proyecto

```
GenesisWordpress/
├── docker-compose.yml                 # Orquestación de servicios
├── Dockerfile                         # Imagen de WordPress con extensiones pgsql
├── migration/                         # Migraciones iniciales para MariaDB (WordPress)
│   ├── emmaus_wpgenesis (2).sql       # Dump/base inicial
│   └── update_urls.sql                # Ajustes de URLs de WP (tabla edgen_options)
├── wp-content/
│   ├── plugins/
│   │   └── plg-genesis/               # Plugin Genesis (usa PostgreSQL)
│   │       └── migration/             # Migraciones/semillas para PostgreSQL
│   └── themes/                        # Temas (incluye `MiTema/`)
├── wp-admin/, wp-includes/, ...       # Núcleo de WordPress
└── README.md                          # Este archivo
```

Servicios expuestos:

- wordpress: http://localhost:8080
- mariadb: 3306
- postgres: 5432

## Desarrollo

Para desarrollo local:

1. Los cambios en `wp-content/` se reflejan automáticamente gracias al volumen montado.
2. Los datos de MariaDB y PostgreSQL persisten entre reinicios vía volúmenes.
3. Para reiniciar los servicios:

```bash
docker-compose restart
```

4. Para acceder a las bases de datos:
   - MariaDB: `mysql -h localhost -P 3306 -u emmaus_wpgenesis -p emmaus_wpgenesis`
   - PostgreSQL: `psql -h localhost -p 5432 -U emmaus_admin -d emmaus_estudiantes`

Comandos útiles:

```bash
docker-compose up -d         # Levantar
docker-compose down          # Apagar
docker-compose restart       # Reiniciar
docker-compose logs -f       # Ver logs en vivo
```

## Frontend v2 (dashboard) – Guía rápida de estilos y componentes

Para mantener homogeneidad en el dashboard v2 (`wp-content/plugins/plg-genesis/frontendv2/`):

- Espaciados (utilidades): `.u-mt-8`, `.u-mt-16`, `.u-mb-8`, `.u-mb-12`.
- Títulos/Secciones: usar `.card-title`, `.section`, `.section-title`.
- Estados de inputs: añadir `.invalid` y `aria-invalid="true"` cuando corresponda.
- Toasts: usar el helper `showToast(text, isError)` provisto en páginas.
- Modal accesible: `role="dialog"`, `aria-modal="true"`, foco inicial, cerrar con `Escape` y trap de `Tab`.

Más detalles y ejemplos en `frontendv2/FRONTEND_GUIDE.md`.

## Documentación v1 (referencia)

- Programas y Cursos v1: ver `wp-content/plugins/plg-genesis/docs/programas-v1.md`
- API v2 (propuesta): `wp-content/plugins/plg-genesis/docs/programas-v2-spec.md`

## Configuración de DB_HOST en `wp-config.php`

Dependiendo del entorno donde ejecutes WordPress, debes ajustar el valor de `DB_HOST` en el archivo `wp-config.php`:

- **Desarrollo local con Docker Compose:**

  ```php
  define( 'DB_HOST', 'mariadb' );
  ```

  El contenedor de WordPress se comunica con MariaDB usando el nombre del servicio definido en `docker-compose.yml`.

- **Producción (o instalación tradicional):**
  ```php
  define( 'DB_HOST', 'localhost' );
  ```
  Usa `localhost` si la base de datos MariaDB/MySQL está en el mismo servidor que WordPress.

**Importante:**
El contenedor de WordPress ya recibe variables de entorno equivalentes vía `docker-compose.yml` (p. ej. `WORDPRESS_DB_HOST=mariadb`). Ajusta según tu entorno para evitar errores de conexión.

## Variables de entorno relevantes (WordPress)

Definidas en el servicio `wordpress` de `docker-compose.yml`:

- `WORDPRESS_DB_HOST=mariadb`
- `WORDPRESS_DB_USER=emmaus_wpgenesis`
- `WORDPRESS_DB_PASSWORD=emmaus_wpgenesis`
- `WORDPRESS_DB_NAME=emmaus_wpgenesis`
- `WORDPRESS_TABLE_PREFIX=edgen_`

Variables para el plugin (PostgreSQL):

- `BOG_DB_HOST=postgres`
- `BOG_DB_NAME=emmaus_estudiantes`
- `BOG_DB_USER=emmaus_admin`
- `BOG_DB_PASSWORD=emmaus1234+`

Asegúrate de mantener estas credenciales fuera de commits públicos si cambian a valores sensibles en producción.

## Migraciones de base de datos

### MariaDB (WordPress)

Al primer arranque, el contenedor `mariadb` ejecuta automáticamente los archivos montados en `docker-entrypoint-initdb.d/`:

- `migration/emmaus_wpgenesis (2).sql` → crea/esquema/datos iniciales
- `migration/update_urls.sql` → ajusta `siteurl` y `home` a `http://localhost:8080` en la tabla `edgen_options` y limpia `rewrite_rules`

Para re-aplicar desde cero, elimina el volumen `mysql_data` y vuelve a levantar los servicios.

### PostgreSQL (Plugin Genesis)

El contenedor `postgres` ejecuta migraciones/semillas montadas desde el plugin:

- `wp-content/plugins/plg-genesis/migration/init.sql`
- `wp-content/plugins/plg-genesis/migration/dump20250805.sql`

Adecua/ordena estas migraciones según el flujo del plugin (p. ej. versiones fechadas en subcarpeta `migrations/`).

## Convenciones de Git

- Ramas: `feature/<descripcion-kebab>`, `fix/<descripcion-kebab>`, `chore/<descripcion>`
- Commits (Conventional Commits): `feat: ...`, `fix: ...`, `chore: ...`, `docs: ...`, `refactor: ...`.
- PRs: título claro, descripción con contexto, lista de cambios, pasos de prueba, y notas de migración si aplica.

## Licencia

Este proyecto está bajo la licencia MIT.
